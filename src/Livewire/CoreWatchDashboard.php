<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CoreWatchDashboard extends Component
{
    /**
     * Mount the component and initialize settings.
     */
    public function mount(): void
    {
        $this->checkAuthorization();
    }

    /**
     * Render the CoreWatch dashboard view.
     *
     * @return View
     */
    public function render()
    {
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

        return view('corewatch::dashboard', compact('config'))
            ->layout('layouts.app'); // Default layout fallback if rendered as full-page component
    }

    /**
     * Perform environment and custom authorization checks.
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

        $gate = config('corewatch.gate');
        if ($gate !== null && is_callable($gate)) {
            if (! $gate(request())) {
                abort(403, 'CoreWatch Access Denied.');
            }
        }
    }
}
