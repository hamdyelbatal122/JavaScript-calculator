<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Http\Controllers;

use Exception;
use Hamzi\CoreWatch\Services\LogParser;
use Hamzi\CoreWatch\Services\SystemMonitor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class DashboardController extends Controller
{
    protected SystemMonitor $monitor;

    protected LogParser $logParser;

    public function __construct(SystemMonitor $monitor, LogParser $logParser)
    {
        $this->monitor = $monitor;
        $this->logParser = $logParser;
    }

    /**
     * Display the CoreWatch Server Health & DevOps Dashboard.
     *
     * @return View|Response
     */
    public function index()
    {
        $this->checkAuthorization();

        $config = [
            'refresh_interval' => config('corewatch.refresh_interval', 5000),
            'widgets' => config('corewatch.widgets', []),
            'services' => array_map(fn ($key, $s) => [
                'key' => $key,
                'name' => $s['name'],
            ], array_keys(config('corewatch.services', [])), config('corewatch.services', [])),
            'logs' => array_map(fn ($key, $l) => [
                'key' => $key,
                'name' => $l['name'],
            ], array_keys(config('corewatch.logs.files', [])), config('corewatch.logs.files', [])),
        ];

        return view('corewatch::dashboard', compact('config'));
    }

    /**
     * Fetch the real-time server health metrics.
     */
    public function metrics(): JsonResponse
    {
        $this->checkAuthorization();

        try {
            $metrics = $this->monitor->getMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Streaming and filtered parsing of server and application logs.
     */
    public function logs(Request $request): JsonResponse
    {
        $this->checkAuthorization();

        $request->validate([
            'file' => 'required|string',
            'page' => 'integer|min:1',
            'level' => 'nullable|string',
            'ip' => 'nullable|string',
            'status' => 'nullable|integer',
            'search' => 'nullable|string',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ]);

        $fileKey = $request->input('file');
        $filesConfig = config('corewatch.logs.files', []);

        if (! array_key_exists($fileKey, $filesConfig)) {
            return response()->json([
                'success' => false,
                'error' => 'Configured log file key not found.',
            ], 404);
        }

        $logFile = $filesConfig[$fileKey];
        $limit = config('corewatch.logs.max_lines_per_page', 100);
        $page = (int) $request->input('page', 1);

        $filters = [
            'level' => $request->input('level'),
            'ip' => $request->input('ip'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
        ];

        try {
            $parsedData = $this->logParser->parse(
                $logFile['path'],
                $logFile['type'],
                $limit,
                $page,
                $filters
            );

            return response()->json([
                'success' => true,
                'logs' => $parsedData['logs'],
                'has_more' => $parsedData['has_more'],
                'total_scanned' => $parsedData['total_scanned'],
                'file_name' => $logFile['name'],
                'file_path' => $logFile['path'],
                'error' => $parsedData['error'] ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asynchronous secure server/artisan command executor.
     */
    public function controlService(Request $request): JsonResponse
    {
        $this->checkAuthorization();

        $request->validate([
            'service_key' => 'required|string',
        ]);

        $serviceKey = $request->input('service_key');
        $servicesConfig = config('corewatch.services', []);

        if (! array_key_exists($serviceKey, $servicesConfig)) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized or unregistered command trigger.',
            ], 400);
        }

        $service = $servicesConfig[$serviceKey];
        $cmdText = $service['command'];
        $cmdType = $service['type'];

        try {
            if ($cmdType === 'artisan') {
                // Execute secure local artisan task
                $status = Artisan::call($cmdText);
                $output = Artisan::output();
                $success = ($status === 0);
            } else {
                // Execute command using shell processes
                if ($this->monitor->isShellDisabled()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Shell execution is disabled in PHP configuration.',
                    ], 500);
                }

                $processResult = Process::run($cmdText);
                $success = $processResult->successful();
                $output = $processResult->output() ?: $processResult->errorOutput();
            }

            return response()->json([
                'success' => $success,
                'service' => $service['name'],
                'output' => trim($output) ?: 'Command completed successfully (no output).',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enforce environments, feature toggles and custom Gate authorizers.
     */
    protected function checkAuthorization(): void
    {
        if (! config('corewatch.enabled', true)) {
            abort(404, 'CoreWatch dashboard is disabled.');
        }

        $allowedEnvs = config('corewatch.environments', ['local']);
        if (! app()->environment($allowedEnvs)) {
            abort(403, 'CoreWatch dashboard is not permitted in this environment.');
        }

        // Custom callable or gate check
        $gate = config('corewatch.gate');
        if ($gate !== null) {
            if (is_callable($gate)) {
                if (! $gate(request())) {
                    abort(403, 'CoreWatch Access Denied.');
                }
            }
        }
    }
}
