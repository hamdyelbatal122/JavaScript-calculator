<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Tests\Feature;

use Hamzi\CoreWatch\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class DashboardTest extends TestCase
{
    /** @test */
    public function test_dashboard_uri_returns_successful_response(): void
    {
        $response = $this->get('/corewatch');

        $response->assertStatus(200);
        $response->assertViewIs('corewatch::dashboard');
        $response->assertSee('COREWATCH');
    }

    /** @test */
    public function test_api_metrics_returns_valid_telemetry_structure(): void
    {
        $response = $this->get('/corewatch/api/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'cpu' => ['cores', 'load_1', 'load_5', 'load_15', 'usage_percentage'],
                'ram' => ['total', 'total_formatted', 'used', 'used_formatted', 'free', 'available', 'usage_percentage'],
                'disk' => ['total', 'used', 'free', 'usage_percentage', 'path'],
                'uptime',
                'system_info',
                'services',
                'processes',
                'database' => ['driver', 'tables_count', 'size_formatted', 'connection', 'active'],
                'app_checks' => ['cache', 'queue', 'security', 'environment'],
            ],
        ]);
        $response->assertJsonPath('success', true);
    }

    /** @test */
    public function test_unregistered_service_key_returns_bad_request(): void
    {
        $response = $this->postJson('/corewatch/api/services/control', [
            'service_key' => 'unregistered_key_123',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $response->assertJsonFragment(['error' => 'Unauthorized or unregistered command trigger.']);
    }

    /** @test */
    public function test_logs_fails_if_no_file_key(): void
    {
        $response = $this->getJson('/corewatch/api/logs');

        $response->assertStatus(422); // Laravel validation failed
    }

    /** @test */
    public function test_check_health_command_works(): void
    {
        $exitCode = Artisan::call('corewatch:check-health');

        $this->assertEquals(0, $exitCode);
    }
}
