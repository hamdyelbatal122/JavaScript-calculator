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

> [!IMPORTANT]
> **CoreWatch** is a zero-dependency, self-contained server monitoring utility designed specifically for production Laravel systems. It replaces heavy external daemons by exposing highly performant, read-only system files directly through Laravel's secure execution pipeline.

---

## 🗺️ System Architecture Flowchart

The following diagram illustrates how CoreWatch isolates data collection, streams log buffers, routes controller requests, and schedules alerting triggers:

```mermaid
graph TD
    %% Styling
    classDef primary fill:#0c1528,stroke:#00ccff,stroke-width:2px,color:#fff;
    classDef secondary fill:#050b18,stroke:#1f2e4d,stroke-width:1px,color:#aaa;
    classDef alert fill:#0c1528,stroke:#ff3366,stroke-width:2px,color:#fff;

    %% Elements
    A["💻 Master Dashboard View <br> (AlpineJS Client)"] ::: primary
    B["🛣️ CoreWatch Routing Gateway <br> (Protected Middleware)"] ::: secondary
    C["⚙️ SystemMonitor Service"] ::: primary
    D["📄 LogParser Streamer <br> (Direct fseek seek)"] ::: primary
    E["⚡ Whitelisted Services Exec <br> (RCE-Proof Command List)"] ::: primary
    F["⏰ Sentinel Health Command <br> (Artisan Cron Daemon)"] ::: alert
    
    G["📡 Host System <br> (/proc, top processes, disk filesystem)"] ::: secondary
    H["💾 Database Engine <br> (MySQL, SQLite, PGSQL Sizing)"] ::: secondary
    I["💬 DevOps Channels <br> (Slack & Telegram API)"] ::: alert

    %% Connections
    A -->|1. Poll Metrics API| B
    B --> C
    C -->|Native Syscalls| G
    C -->|Schema Sizing| H
    A -->|2. Stream Log Chunk| B
    B --> D
    D -->|O(1) Seek Buffer| G
    A -->|3. Trigger Secure Action| B
    B --> E
    E -->|Execute Whitelist| G
    F -->|Resource Threshold Checks| C
    F -->|Alert Breaches| I
```

---

## 🧱 Modular `@include` Partial Architecture

CoreWatch separates all diagnostics into elegant, self-contained monospace tables inside `resources/views/partials/`. This modular structure allows clients to easily publish views and include specific tables anywhere inside their custom dashboards:

| Partial Blade View Path | Diagnostic Target | Layout Display Style | Customization Purpose |
| :--- | :--- | :--- | :--- |
| `partials.cpu` | CPU Cores & Load averages | Monospace UNIX Table | Monitor core load thresholds (1M, 5M, 15M) |
| `partials.ram` | Physical Memory (RAM) Allocation | Monospace Memory Table | Track active, free, and cached allocation bytes |
| `partials.disk` | Disk Storage Saturated Volumes | Saturated Space Table | Monitor root storage partition size limits |
| `partials.processes` | Active CPU Top Linux Processes | Live CLI System Table | Identify high CPU usage processes (PID, User) |
| `partials.database` | Database Engine & Schema size | Monospace DB Status Table | Track table counts and database file sizes |
| `partials.app-checks` | Operational Application Integrity | Status Indicator List | Verify Cache, Queue, and Security modes |
| `partials.specifications` | OS Kernel & Laravel specifications | Static Specs Table | Quick access to PHP, OS, and server version info |
| `partials.services` | Whitelisted system task controls | Command Action Table | Safe execution of authorized terminal commands |
| `partials.logs` | Live chunked stream terminal view | Cyberpunk Log Console | View and filter real-time logs with pagination |

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

## 🛠️ Installation & Setup

### 1. Link Local Package (Development)
Configure your host application's `composer.json` to register the local package folder path:

```json
"repositories": [
    {
        "type": "path",
        "url": "../corewatch"
    }
],
```

Then pull the package via composer:
```bash
composer require hamzi/corewatch:dev-main
```

### 2. Publish Configuration & Views
Publish the assets to customize layout templates and rule lists:

```bash
# Publish CoreWatch configuration (config/corewatch.php)
php artisan vendor:publish --tag=corewatch-config

# Publish Blade partials & layouts for customized dashboards
php artisan vendor:publish --tag=corewatch-views
```

---

## 🔌 Flexible Dashboard Integration Options

CoreWatch is designed to fit seamlessly wherever your administration operations are managed:

> [!TIP]
> Make sure to wrap any custom page elements that use these modular tables inside the parent AlpineJS data controller: `<div x-data="corewatchDashboard()">...</div>`.

### Option A: Standalone Routed View
Once active, navigate directly to `/corewatch` to view the comprehensive Cyberpunk DevOps terminal.

### Option B: Modular Table Includes
Publish the views and embed specific partial views inside your existing administrative panels:

```html
<div x-data="corewatchDashboard()">
    <div class="grid grid-cols-2 gap-4">
        <!-- Render CPU and Database tables directly -->
        @include('corewatch::partials.cpu')
        @include('corewatch::partials.database')
    </div>
</div>
```

### Option C: Blade Custom Component Embeds
Embed the full dashboard seamlessly:

```html
<x-corewatch-views::dashboard />
```

### Option D: Livewire Drag-and-Drop
Embed the Livewire component in Filament dashboards or custom panels:

```html
<livewire:corewatch-dashboard />
```

---

## ⚙️ Threshold Sentinel alerts Alerting

Enable real-time warnings on Slack or Telegram by configuring your host `.env`:

```env
# Slack Alerts Configuration
COREWATCH_SLACK_WEBHOOK_URL="https://hooks.slack.com/services/YOUR_SLACK_WEBHOOK_URL"
COREWATCH_SLACK_CHANNEL="#devops-alerts"

# Telegram Alerts Configuration
COREWATCH_TELEGRAM_BOT_TOKEN="0000000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
COREWATCH_TELEGRAM_CHAT_ID="-1000000000000"
```

Register the checker command in `routes/console.php` to run every five minutes:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('corewatch:check-health')->everyFiveMinutes();
```

---

## 🔒 Security Practices & Fallbacks
1. **RCE Protection:** CoreWatch never accepts raw input strings to execute shell commands. It maps requests to rigid keys registered in `config/corewatch.php` and blocks any unauthorized requests.
2. **Memory Safety:** The Log Parser uses direct `fseek` backward seeking to stream logs in 64KB blocks, maintaining a strict $O(1)$ memory consumption profile regardless of log file size.
3. **Graceful Fallbacks:** If commands like `exec` or `proc_open` are disabled in `php.ini`, the package falls back to parsing native `/proc` direct files and displays interactive notifications.

---

## 📄 License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
