<div align="center">
    <h1>Laravel CoreWatch 🛡️</h1>
    <p><strong>A world-class, production-ready DevOps & Server Health Dashboard for Laravel 11, 12 & 13</strong></p>
    
    <p>
        <a href="https://packagist.org/packages/hamzi/corewatch"><img src="https://img.shields.io/badge/packagist-v1.0.0-5F57C9?style=flat-square" alt="Latest Stable Version"></a>
        <a href="https://github.com/hamdyelbatal122/JavaScript-calculator/actions"><img src="https://img.shields.io/badge/tests-passing-10B981?style=flat-square" alt="Build Status"></a>
        <a href="https://github.com/hamdyelbatal122/JavaScript-calculator/actions"><img src="https://img.shields.io/badge/pint-passing-10B981?style=flat-square" alt="Pint Status"></a>
        <a href="https://packagist.org/packages/hamzi/corewatch"><img src="https://img.shields.io/badge/downloads-0-EF4444?style=flat-square" alt="Total Downloads"></a>
        <a href="https://php.net"><img src="https://img.shields.io/badge/php-^8.2|^-8.5-007EC6?style=flat-square" alt="PHP Version"></a>
        <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-74C812?style=flat-square" alt="License"></a>
    </p>
</div>

---

`Hamzi/CoreWatch` is a comprehensive, production-ready, and stealthy **DevOps Server Health & Service Control Dashboard** designed specifically for **Laravel 13.x** applications. It embeds an elegant, real-time monitoring suite directly inside your application without external heavy agents or runtime pipelines.

---

## ⚡ Key Highlights
1. **Stealthy & Dynamic UI:** Self-contained Blade views styled with a premium Cyberpunk DevOps dark theme. Uses lightweight Tailwind CSS & AlpineJS for dynamic reactivity without bundler dependencies.
2. **Zero-overhead Log Viewer:** An advanced, memory-efficient backward-seeking chunked file parser that streams Laravel/Nginx/Apache logs without memory exhaustion even on multi-gigabyte files.
3. **Advanced System Diagnostics:** Native `/proc` filesystem parsing coupled with fast system command fallbacks to deliver instant CPU, RAM, Disk, and system uptime metrics.
4. **Pre-Whitelisted Services Controller:** Safe administrative triggers (e.g. queue restart, redis flush, cache clearing) mapping to strict command keys preventing arbitrary RCE vulnerabilities.
5. **Top Active CPU Processes:** Live sorted process statistics terminal displaying CPU load, RAM allocation, PID, user, and running commands on Linux hosts.
6. **Database Telemetry Widget:** Direct schema capacity details, connection indicators, and tables count monitoring for MySQL, PostgreSQL, and SQLite database connection engines.
7. **App Integrity Checks:** Automated operational verification for Cache drivers, Artisan Queue connections, Environment status, and Security debug mode states.
8. **Livewire Embed Support:** Built-in dynamic Livewire component (`livewire:corewatch-dashboard`) for drag-and-drop embedding inside administrative panels like **Filament** and **Laravel Nova**.
9. **Continuous Sentinel Daemon:** Scheduled console monitor (`corewatch:check-health`) that alerts your DevOps channels (Slack & Telegram) when resource thresholds are breached.

---

## 📦 Package Directory Structure

The complete filesystem structure of the package is organized as follows:
```text
hamzi/corewatch/
├── .github/
│   └── workflows/
│       └── ci.yml              # GitHub Actions CI matrix workflow for PHP & Laravel
├── config/
│   └── corewatch.php           # Feature switches, thresholds, paths and whitelists
├── resources/
│   └── views/
│       └── dashboard.blade.php # Fully interactive responsive Cyberpunk frontend
├── src/
│   ├── CoreWatchServiceProvider.php           # Service provider & route bootstrapping
│   ├── Console/
│   │   └── Commands/
│   │       └── CheckHealthCommand.php         # Threshold check & notifier cron
│   ├── Http/
│   │   └── Controllers/
│   │       └── DashboardController.php        # API Metrics, log stream & action router
│   ├── Livewire/
│   │   └── CoreWatchDashboard.php             # Drag-and-drop Livewire component
│   └── Services/
│       ├── LogParser.php                      # Backward chunked file scanner
│       └── SystemMonitor.php                  # System metrics and status analyzer
├── tests/
│   ├── Feature/
│   │   └── DashboardTest.php                  # Automated test suite for endpoints & commands
│   └── TestCase.php                           # Orchestra Testbench base sandbox
├── .gitignore                  # GitHub ignore files pattern
├── CONTRIBUTING.md             # Developer workflow contribution guide
├── LICENSE                     # MIT License
├── phpunit.xml                 # Test environment configs
└── README.md                   # Setup documentation
```

---

## 🛠️ Installation & Setup

### 1. Register Local Repository (Local/Development)
During local development, configure your main Laravel application's `composer.json` to link the package folder:

```json
"repositories": [
    {
        "type": "path",
        "url": "../JavaScript-calculator"
    }
],
```

Then pull the package via composer:
```bash
composer require hamzi/corewatch:dev-main
```

### 2. Publish Configuration & Assets
CoreWatch registers its routes and views automatically. Run the publish commands to customize default rules:

```bash
# Publish CoreWatch configuration (config/corewatch.php)
php artisan vendor:publish --tag=corewatch-config

# Publish Blade UI views for maximum styling customization
php artisan vendor:publish --tag=corewatch-views
```

---

## 🔌 Embedded Layout Options

CoreWatch is designed to fit seamlessly wherever your administration operations are managed:

### Option A: Standalone Routed Dashboard
Once installed, the dashboard is accessible at `/corewatch` out-of-the-box (configurable in `config/corewatch.php`), protected by your specified route middleware.

### Option B: Native Livewire Component (Filament / Nova Integration)
You can embed the dashboard inside your custom Livewire views or admin dashboards (e.g. custom **Filament** Pages or **Laravel Nova** cards):

```html
<!-- Inside any Blade layout or Livewire page -->
<livewire:corewatch-dashboard />
```

### Option C: Blade Custom Component Embeds
Publish the views and embed the Blade layout within your existing dashboards:

```html
<x-corewatch-views::dashboard />
```

---

## ⚙️ Configuration (`config/corewatch.php`)

Ensure you check your configuration to fine-tune your DevOps rules:

- **Routing:** Access route path defaults to `/corewatch`. Protected by `['web']` middleware out-of-the-box.
- **Allowed Environments:** Defaults to `['local', 'staging', 'production']`.
- **System Service Controllers:** Register custom, pre-whitelisted Artisan or shell actions:
  ```php
  'services' => [
      'php_queue' => [
          'name' => 'Artisan Queue Restart',
          'command' => 'php artisan queue:restart',
          'type' => 'artisan',
      ],
      // ...
  ]
  ```
- **Log Files Parser:** Point to custom paths for Nginx/Apache logs and declare formats (`laravel`, `nginx_access`, `nginx_error`, `apache_access`, `apache_error`).
- **Resource Threshold Limits:** Customize warning and critical thresholds:
  - **CPU:** Default `85%` load capacity.
  - **RAM:** Default `90%` active allocation.
  - **Disk:** Default `90%` storage saturation.

---

## 📡 Automated Health Monitoring Alerting

CoreWatch comes with a built-in health monitor. Register the command in your host application scheduler.

### Laravel 11.x & 12.x / 13.x Scheduling
Add the following line to `routes/console.php` (or your Scheduler registration):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('corewatch:check-health')->everyFiveMinutes();
```

### Setup Environmental Webhooks (.env)
Define notification endpoints in your host application `.env` to receive real-time warnings:

```env
# Slack Alerts Configuration
COREWATCH_SLACK_WEBHOOK_URL="https://hooks.slack.com/services/YOUR_SLACK_WEBHOOK_URL"
COREWATCH_SLACK_CHANNEL="#devops-alerts"

# Telegram Alerts Configuration
COREWATCH_TELEGRAM_BOT_TOKEN="0000000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
COREWATCH_TELEGRAM_CHAT_ID="-1000000000000"
```

---

## 🔒 Security Practices & Fallbacks
1. **RCE Prevention:** CoreWatch **never** runs raw input from request fields. Buttons on the dashboard reference rigid config keys (e.g. `redis_flush`). Only exact commands declared in `config/corewatch.php` are allowed to execute.
2. **Safe Fallbacks:** If PHP's command execution functions (like `exec`, `proc_open`) are disabled via `disable_functions` in `php.ini`, the system falls back gracefully to native `/proc` direct files and displays interactive UI notifications.
3. **Optimized I/O Stream:** The Log Parser seeks directly to the end of the files (`fseek` backward) and parses lines in chunks (64KB), meaning it maintains a flat $O(1)$ memory consumption profile regardless of log file size.

---

## 📄 License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
