<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class SystemMonitor
{
    /**
     * Get all hardware and service metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        return [
            'cpu' => $this->getCpuMetrics(),
            'ram' => $this->getRamMetrics(),
            'disk' => $this->getDiskMetrics(),
            'uptime' => $this->getUptime(),
            'system_info' => $this->getSystemInfo(),
            'services' => $this->getServicesStatus(),
            'processes' => $this->getTopProcesses(),
            'database' => $this->getDatabaseStats(),
            'app_checks' => $this->getApplicationChecks(),
        ];
    }

    /**
     * Gather CPU load averages and calculate utilization.
     *
     * @return array<string, mixed>
     */
    public function getCpuMetrics(): array
    {
        $cores = $this->getCpuCoresCount();
        $loadAvg = [0.0, 0.0, 0.0];

        // Try reading /proc/loadavg directly (Linux standard)
        if (is_readable('/proc/loadavg')) {
            $loadStr = file_get_contents('/proc/loadavg');
            if ($loadStr !== false) {
                $parts = explode(' ', trim($loadStr));
                if (count($parts) >= 3) {
                    $loadAvg = [
                        (float) $parts[0],
                        (float) $parts[1],
                        (float) $parts[2],
                    ];
                }
            }
        } elseif (function_exists('sys_getloadavg')) {
            $sysLoad = sys_getloadavg();
            if (is_array($sysLoad) && count($sysLoad) >= 3) {
                $loadAvg = [
                    (float) $sysLoad[0],
                    (float) $sysLoad[1],
                    (float) $sysLoad[2],
                ];
            }
        } else {
            // Shell fallback
            $result = $this->runCommand('uptime');
            if ($result['success'] && preg_match('/load average[s]?:/i', $result['output'])) {
                $parts = preg_split('/load average[s]?:/i', $result['output']);
                if (isset($parts[1])) {
                    $loads = explode(',', $parts[1]);
                    $loadAvg = [
                        (float) trim($loads[0] ?? '0'),
                        (float) trim($loads[1] ?? '0'),
                        (float) trim($loads[2] ?? '0'),
                    ];
                }
            }
        }

        // Calculate CPU usage percentage relative to cores
        // Load average 1.0 on a 1-core machine means 100% load
        $loadPercentage = $cores > 0 ? ($loadAvg[0] / $cores) * 100 : 0.0;
        $loadPercentage = min(100.0, round($loadPercentage, 2));

        return [
            'cores' => $cores,
            'load_1' => $loadAvg[0],
            'load_5' => $loadAvg[1],
            'load_15' => $loadAvg[2],
            'usage_percentage' => $loadPercentage,
        ];
    }

    /**
     * Gather Memory (RAM) details.
     *
     * @return array<string, mixed>
     */
    public function getRamMetrics(): array
    {
        $total = 0;
        $free = 0;
        $available = 0;

        // Try reading /proc/meminfo (Linux direct, avoids sub-process)
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            if ($meminfo !== false) {
                preg_match('/^MemTotal:\s+(\d+)\s+kB/m', $meminfo, $totalMatches);
                preg_match('/^MemFree:\s+(\d+)\s+kB/m', $meminfo, $freeMatches);
                preg_match('/^MemAvailable:\s+(\d+)\s+kB/m', $meminfo, $availMatches);

                $total = isset($totalMatches[1]) ? (int) $totalMatches[1] * 1024 : 0;
                $free = isset($freeMatches[1]) ? (int) $freeMatches[1] * 1024 : 0;

                if (isset($availMatches[1])) {
                    $available = (int) $availMatches[1] * 1024;
                } else {
                    // Fallback calculation for older kernels
                    preg_match('/^Buffers:\s+(\d+)\s+kB/m', $meminfo, $bufferMatches);
                    preg_match('/^Cached:\s+(\d+)\s+kB/m', $meminfo, $cacheMatches);
                    $buffers = isset($bufferMatches[1]) ? (int) $bufferMatches[1] * 1024 : 0;
                    $cached = isset($cacheMatches[1]) ? (int) $cacheMatches[1] * 1024 : 0;
                    $available = $free + $buffers + $cached;
                }
            }
        }

        // Fallback: use 'free' command
        if ($total === 0) {
            $result = $this->runCommand('free -b');
            if ($result['success']) {
                $lines = explode("\n", trim($result['output']));
                foreach ($lines as $line) {
                    if (str_starts_with(strtolower($line), 'mem:')) {
                        $parts = preg_split('/\s+/', $line);
                        $total = (int) ($parts[1] ?? 0);
                        $free = (int) ($parts[3] ?? 0);
                        $available = (int) ($parts[6] ?? ($free + ($parts[4] ?? 0) + ($parts[5] ?? 0))); // available key is standard in modern free
                        break;
                    }
                }
            }
        }

        // Final safety check/mock fallback to prevent division by zero
        if ($total === 0) {
            $total = 1;
            $available = 1;
        }

        $used = $total - $available;
        $usagePercentage = round(($used / $total) * 100, 2);

        return [
            'total' => $total,
            'total_formatted' => $this->formatBytes($total),
            'used' => $used,
            'used_formatted' => $this->formatBytes($used),
            'free' => $free,
            'free_formatted' => $this->formatBytes($free),
            'available' => $available,
            'available_formatted' => $this->formatBytes($available),
            'usage_percentage' => min(100.0, max(0.0, $usagePercentage)),
        ];
    }

    /**
     * Gather Disk storage metrics.
     *
     * @return array<string, mixed>
     */
    public function getDiskMetrics(): array
    {
        $path = base_path();

        try {
            $total = (float) @disk_total_space($path);
            $free = (float) @disk_free_space($path);

            // Safeguard against disk function restrictions
            if ($total <= 0) {
                throw new Exception('Native disk metrics returned zero or are restricted.');
            }
        } catch (Exception) {
            // Shell fallback via df
            $total = 1.0;
            $free = 1.0;
            $result = $this->runCommand('df -P '.escapeshellarg($path));
            if ($result['success']) {
                $lines = explode("\n", trim($result['output']));
                if (count($lines) >= 2) {
                    $parts = preg_split('/\s+/', $lines[1]);
                    if (count($parts) >= 6) {
                        $total = (float) $parts[1] * 1024; // df outputs in 1K blocks
                        $free = (float) $parts[3] * 1024;
                    }
                }
            }
        }

        $used = $total - $free;
        $usagePercentage = round(($used / $total) * 100, 2);

        return [
            'total' => $total,
            'total_formatted' => $this->formatBytes((int) $total),
            'used' => $used,
            'used_formatted' => $this->formatBytes((int) $used),
            'free' => $free,
            'free_formatted' => $this->formatBytes((int) $free),
            'usage_percentage' => min(100.0, max(0.0, $usagePercentage)),
            'path' => $path,
        ];
    }

    /**
     * Fetch server uptime.
     */
    public function getUptime(): string
    {
        if (is_readable('/proc/uptime')) {
            $uptimeStr = file_get_contents('/proc/uptime');
            if ($uptimeStr !== false) {
                $seconds = (int) explode(' ', trim($uptimeStr))[0];

                return $this->formatUptimeSeconds($seconds);
            }
        }

        $result = $this->runCommand('uptime -p');
        if ($result['success']) {
            return trim(str_replace('up ', '', $result['output']));
        }

        return 'Unknown';
    }

    /**
     * Fetch General System Information.
     *
     * @return array<string, string>
     */
    public function getSystemInfo(): array
    {
        $os = PHP_OS;
        $kernel = php_uname('r');
        $hostname = php_uname('n');

        if (is_readable('/etc/os-release')) {
            $osInfo = file_get_contents('/etc/os-release');
            if ($osInfo !== false && preg_match('/PRETTY_NAME="([^"]+)"/', $osInfo, $matches)) {
                $os = $matches[1];
            }
        }

        return [
            'os' => $os,
            'kernel' => $kernel,
            'hostname' => $hostname,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Command Line Interface',
        ];
    }

    /**
     * Checks the running status of typical server services.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getServicesStatus(): array
    {
        $services = [
            'nginx' => ['name' => 'Nginx', 'port' => 80, 'process' => 'nginx'],
            'apache' => ['name' => 'Apache', 'port' => 8080, 'process' => 'apache2'],
            'mysql' => ['name' => 'MySQL', 'port' => 3306, 'process' => 'mysqld'],
            'redis' => ['name' => 'Redis', 'port' => 6379, 'process' => 'redis-server'],
            'supervisor' => ['name' => 'Supervisor', 'port' => null, 'process' => 'supervisord'],
            'memcached' => ['name' => 'Memcached', 'port' => 11211, 'process' => 'memcached'],
        ];

        $statusList = [];

        foreach ($services as $key => $meta) {
            $isActive = false;

            // Method 1: Check running process via shell (fast if available)
            if ($meta['process'] !== null) {
                $check = $this->runCommand('pgrep -x '.escapeshellarg($meta['process']));
                if ($check['success'] && ! empty(trim($check['output']))) {
                    $isActive = true;
                }
            }

            // Method 2: Connection fallback if process check failed but port exists
            if (! $isActive && $meta['port'] !== null) {
                $connection = @fsockopen('127.0.0.1', $meta['port'], $errno, $errstr, 0.2);
                if (is_resource($connection)) {
                    $isActive = true;
                    fclose($connection);
                }
            }

            // Custom specific checks for services (e.g. supervisorctl status)
            if ($key === 'supervisor' && ! $isActive) {
                $checkSup = $this->runCommand('supervisorctl status');
                if ($checkSup['success'] && ! str_contains(strtolower($checkSup['output']), 'error')) {
                    $isActive = true;
                }
            }

            $statusList[$key] = [
                'name' => $meta['name'],
                'active' => $isActive,
                'port' => $meta['port'],
            ];
        }

        return $statusList;
    }

    /**
     * Get CPU cores count.
     */
    protected function getCpuCoresCount(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== false) {
                preg_match_all('/^processor/m', $cpuinfo, $matches);
                $count = count($matches[0]);
                if ($count > 0) {
                    return $count;
                }
            }
        }

        // Fallback to lscpu or nproc
        $result = $this->runCommand('nproc');
        if ($result['success']) {
            return (int) trim($result['output']);
        }

        $result = $this->runCommand('lscpu');
        if ($result['success'] && preg_match('/^CPU\(s\):\s+(\d+)/m', $result['output'], $matches)) {
            return (int) $matches[1];
        }

        return 1; // absolute fallback
    }

    /**
     * Run administrative shell commands via Laravel 10+ Process facade safely.
     *
     * @return array{success: bool, output: string}
     */
    protected function runCommand(string $command): array
    {
        // Check if shell commands execution is disabled in PHP
        if ($this->isShellDisabled()) {
            return ['success' => false, 'output' => 'Shell execution is disabled in php.ini'];
        }

        try {
            $processResult = Process::run($command);

            return [
                'success' => $processResult->successful(),
                'output' => $processResult->output().$processResult->errorOutput(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine if shell execution functions are disabled.
     */
    public function isShellDisabled(): bool
    {
        $disabledFunctions = explode(',', ini_get('disable_functions'));
        $disabledFunctions = array_map('trim', $disabledFunctions);
        $disabledFunctions = array_map('strtolower', $disabledFunctions);

        return in_array('exec', $disabledFunctions, true) ||
               in_array('shell_exec', $disabledFunctions, true) ||
               in_array('system', $disabledFunctions, true) ||
               in_array('proc_open', $disabledFunctions, true);
    }

    /**
     * Format bytes to readable size string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = (int) floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }

    /**
     * Format uptime seconds into a human-readable duration.
     */
    protected function formatUptimeSeconds(int $seconds): string
    {
        $days = (int) floor($seconds / 86400);
        $seconds %= 86400;
        $hours = (int) floor($seconds / 3600);
        $seconds %= 3600;
        $minutes = (int) floor($seconds / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }

        return count($parts) > 0 ? implode(' ', $parts) : '0m';
    }

    /**
     * Get top 5 CPU-consuming processes.
     *
     * @return array<int, array<string, string>>
     */
    public function getTopProcesses(): array
    {
        if ($this->isShellDisabled()) {
            return [];
        }

        $result = $this->runCommand('ps -eo pcpu,pmem,pid,user,comm --sort=-pcpu | head -n 6');
        if (! $result['success']) {
            return [];
        }

        $lines = explode("\n", trim($result['output']));
        $processes = [];

        // Skip headers line 0
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            $parts = preg_split('/\s+/', $line, 5);
            if (count($parts) >= 5) {
                $processes[] = [
                    'cpu' => $parts[0].'%',
                    'mem' => $parts[1].'%',
                    'pid' => $parts[2],
                    'user' => $parts[3],
                    'command' => $parts[4],
                ];
            }
        }

        return $processes;
    }

    /**
     * Fetch host application database telemetry.
     *
     * @return array<string, mixed>
     */
    public function getDatabaseStats(): array
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $tablesCount = 0;
            $sizeBytes = 0;

            if ($driver === 'mysql') {
                $tablesResult = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()');
                $tablesCount = (int) ($tablesResult[0]->count ?? 0);

                $sizeResult = DB::select('SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = DATABASE()');
                $sizeBytes = (int) ($sizeResult[0]->size ?? 0);
            } elseif ($driver === 'sqlite') {
                $tablesResult = DB::select("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'");
                $tablesCount = (int) ($tablesResult[0]->count ?? 0);

                $sizeResult = DB::select('SELECT page_count * page_size AS size FROM pragma_page_count(), pragma_page_size()');
                $sizeBytes = (int) ($sizeResult[0]->size ?? 0);
            } elseif ($driver === 'pgsql') {
                $tablesResult = DB::select("SELECT COUNT(*) as count FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
                $tablesCount = (int) ($tablesResult[0]->count ?? 0);

                $sizeResult = DB::select('SELECT pg_database_size(current_database()) AS size');
                $sizeBytes = (int) ($sizeResult[0]->size ?? 0);
            }

            return [
                'driver' => strtoupper($driver),
                'tables_count' => $tablesCount,
                'size_formatted' => $this->formatBytes($sizeBytes),
                'connection' => 'Connected ✅',
                'active' => true,
            ];
        } catch (Exception) {
            return [
                'driver' => 'Unknown',
                'tables_count' => 0,
                'size_formatted' => '0 B',
                'connection' => 'Disconnected ❌',
                'active' => false,
            ];
        }
    }

    /**
     * Conduct active connection/system health checklists.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getApplicationChecks(): array
    {
        $checks = [];

        // 1. Cache Diagnostic
        try {
            $cacheKey = 'corewatch_health_ping';
            Cache::put($cacheKey, true, 5);
            $cacheActive = Cache::get($cacheKey) === true;
            $checks['cache'] = [
                'name' => 'Cache System Driver',
                'status' => $cacheActive ? 'Operational ✅' : 'Failed ❌',
                'active' => $cacheActive,
                'detail' => 'Store driver: '.config('cache.default', 'unknown'),
            ];
        } catch (Exception $e) {
            $checks['cache'] = [
                'name' => 'Cache System Driver',
                'status' => 'Broken ❌',
                'active' => false,
                'detail' => $e->getMessage(),
            ];
        }

        // 2. Queue Diagnostic
        try {
            $queueConnection = config('queue.default', 'sync');
            $checks['queue'] = [
                'name' => 'Artisan Queue Driver',
                'status' => 'Configured ✅',
                'active' => true,
                'detail' => 'Driver connection: '.$queueConnection,
            ];
        } catch (Exception $e) {
            $checks['queue'] = [
                'name' => 'Artisan Queue Driver',
                'status' => 'Unconfigured ⚠️',
                'active' => false,
                'detail' => $e->getMessage(),
            ];
        }

        // 3. Security (Debug mode check)
        $debugMode = (bool) config('app.debug', false);
        $checks['security'] = [
            'name' => 'Debug Diagnostics Mode',
            'status' => $debugMode ? 'Exposed ⚠️' : 'Secured ✅',
            'active' => ! $debugMode,
            'detail' => $debugMode ? 'Disable APP_DEBUG in production env.' : 'Direct public access exposures are closed.',
        ];

        // 4. Environment Check
        $checks['environment'] = [
            'name' => 'Active Environment',
            'status' => app()->environment() === 'production' ? 'Production Mode 🚀' : 'Development / Staging 🛠️',
            'active' => true,
            'detail' => 'Current env: '.app()->environment(),
        ];

        return $checks;
    }
}
