<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Console\Commands;

use Hamzi\CoreWatch\Services\SystemMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corewatch:check-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect server resource usage thresholds and trigger Slack/Telegram alerts';

    protected SystemMonitor $monitor;

    public function __construct(SystemMonitor $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking server resources...');

        $cpu = $this->monitor->getCpuMetrics();
        $ram = $this->monitor->getRamMetrics();
        $disk = $this->monitor->getDiskMetrics();
        $systemInfo = $this->monitor->getSystemInfo();

        $cpuThreshold = (float) config('corewatch.thresholds.cpu', 85.0);
        $ramThreshold = (float) config('corewatch.thresholds.ram', 90.0);
        $diskThreshold = (float) config('corewatch.thresholds.disk', 90.0);

        $alerts = [];

        // Check CPU Usage
        if ($cpu['usage_percentage'] >= $cpuThreshold) {
            $alerts['cpu'] = [
                'name' => 'CPU Load Utilization',
                'current' => $cpu['usage_percentage'].'%',
                'threshold' => $cpuThreshold.'%',
                'severity' => 'critical',
                'details' => "1-Min Load: {$cpu['load_1']}, Cores: {$cpu['cores']}",
            ];
        }

        // Check RAM Usage
        if ($ram['usage_percentage'] >= $ramThreshold) {
            $alerts['ram'] = [
                'name' => 'RAM Allocation Limit',
                'current' => $ram['usage_percentage'].'%',
                'threshold' => $ramThreshold.'%',
                'severity' => 'critical',
                'details' => "Used: {$ram['used_formatted']} / Total: {$ram['total_formatted']}",
            ];
        }

        // Check Disk Space
        if ($disk['usage_percentage'] >= $diskThreshold) {
            $alerts['disk'] = [
                'name' => 'Disk Space Saturation',
                'current' => $disk['usage_percentage'].'%',
                'threshold' => $diskThreshold.'%',
                'severity' => 'warning',
                'details' => "Used: {$disk['used_formatted']} / Total: {$disk['total_formatted']} (Path: {$disk['path']})",
            ];
        }

        if (count($alerts) > 0) {
            $this->error(sprintf('Threshold breached! %d resource alerts found.', count($alerts)));
            $this->displayAlertsTable($alerts);
            $this->sendNotifications($alerts, $systemInfo);
        } else {
            $this->info('Server health check completed successfully. All metrics are within safe boundaries.');
            $this->table(
                ['Resource', 'Current Usage', 'Alert Threshold', 'Status'],
                [
                    ['CPU', $cpu['usage_percentage'].'%', $cpuThreshold.'%', 'OK ✅'],
                    ['RAM', $ram['usage_percentage'].'%', $ramThreshold.'%', 'OK ✅'],
                    ['Disk', $disk['usage_percentage'].'%', $diskThreshold.'%', 'OK ✅'],
                ]
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Format and display breaches as console tables.
     *
     * @param  array<string, array<string, string>>  $alerts
     */
    protected function displayAlertsTable(array $alerts): void
    {
        $rows = [];
        foreach ($alerts as $key => $alert) {
            $rows[] = [
                $alert['name'],
                $alert['current'],
                $alert['threshold'],
                strtoupper($alert['severity']).' ⚠️',
                $alert['details'],
            ];
        }

        $this->table(
            ['Metric Alert', 'Current', 'Limit', 'Severity', 'Context Details'],
            $rows
        );
    }

    /**
     * Dispatches notification payloads to configured channels.
     *
     * @param  array<string, array<string, string>>  $alerts
     * @param  array<string, string>  $systemInfo
     */
    protected function sendNotifications(array $alerts, array $systemInfo): void
    {
        $channels = config('corewatch.notifications.channels', []);

        if (in_array('slack', $channels, true)) {
            $this->sendSlackAlert($alerts, $systemInfo);
        }

        if (in_array('telegram', $channels, true)) {
            $this->sendTelegramAlert($alerts, $systemInfo);
        }
    }

    /**
     * Push alert blocks to Slack Webhooks.
     *
     * @param  array<string, array<string, string>>  $alerts
     * @param  array<string, string>  $systemInfo
     */
    protected function sendSlackAlert(array $alerts, array $systemInfo): void
    {
        $webhookUrl = config('corewatch.notifications.slack.webhook_url');
        if (empty($webhookUrl)) {
            $this->warn('Slack webhook URL is empty. Alert skipped.');

            return;
        }

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => '🚨 CoreWatch Server Alert Breach 🚨',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    ['type' => 'mrkdwn', 'text' => "*Host:* `{$systemInfo['hostname']}`"],
                    ['type' => 'mrkdwn', 'text' => '*Environment:* `'.app()->environment().'`'],
                    ['type' => 'mrkdwn', 'text' => "*OS:* `{$systemInfo['os']}`"],
                    ['type' => 'mrkdwn', 'text' => "*PHP:* `{$systemInfo['php_version']}`"],
                ],
            ],
            ['type' => 'divider'],
        ];

        foreach ($alerts as $alert) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => sprintf(
                        "⚠️ *%s* has breached limits!\n*Current Usage:* `%s` (Threshold: `%s`)\n*Details:* _%s_",
                        $alert['name'],
                        $alert['current'],
                        $alert['threshold'],
                        $alert['details']
                    ),
                ],
            ];
        }

        $blocks[] = ['type' => 'divider'];
        $blocks[] = [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => 'CoreWatch DevOps Sentinel • '.now()->toCookieString(),
                ],
            ],
        ];

        try {
            Http::post($webhookUrl, [
                'text' => 'CoreWatch Alert Breach: Server resource thresholds exceeded!',
                'blocks' => $blocks,
            ]);
            $this->info('Slack notification dispatched successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to send Slack alert: '.$e->getMessage());
        }
    }

    /**
     * Push HTML alerts to Telegram Channels.
     *
     * @param  array<string, array<string, string>>  $alerts
     * @param  array<string, string>  $systemInfo
     */
    protected function sendTelegramAlert(array $alerts, array $systemInfo): void
    {
        $botToken = config('corewatch.notifications.telegram.bot_token');
        $chatId = config('corewatch.notifications.telegram.chat_id');

        if (empty($botToken) || empty($chatId)) {
            $this->warn('Telegram credentials not configured. Alert skipped.');

            return;
        }

        $message = "<b>⚠️ CoreWatch Alert Breach ⚠️</b>\n\n";
        $message .= "<b>Host:</b> <code>{$systemInfo['hostname']}</code>\n";
        $message .= '<b>Env:</b> <code>'.app()->environment()."</code>\n";
        $message .= "<b>OS:</b> <code>{$systemInfo['os']}</code>\n\n";
        $message .= "<b>Resource Violations:</b>\n";

        foreach ($alerts as $alert) {
            $message .= "• <b>{$alert['name']}</b>\n";
            $message .= "  Current: <code>{$alert['current']}</code> (Limit: {$alert['threshold']})\n";
            $message .= "  Info: <i>{$alert['details']}</i>\n\n";
        }

        $message .= '📅 <i>'.now()->toCookieString().'</i>';

        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
            $this->info('Telegram notification dispatched successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to send Telegram alert: '.$e->getMessage());
        }
    }
}
