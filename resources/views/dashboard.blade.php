<!DOCTYPE html>
<html lang="en" class="h-full bg-[#030712] text-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreWatch // DevOps Server Health Sentinel</title>
    <!-- Tailwind CSS 3.x via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cyber: {
                            bg: '#050b18',
                            card: '#0c1528',
                            border: '#1f2e4d',
                            green: '#00ff88',
                            blue: '#00ccff',
                            purple: '#ab47bc',
                            orange: '#ff9100',
                            red: '#ff3366',
                        }
                    },
                    boxShadow: {
                        'neon-green': '0 0 15px rgba(0, 255, 136, 0.15)',
                        'neon-blue': '0 0 15px rgba(0, 204, 255, 0.15)',
                        'neon-red': '0 0 15px rgba(255, 51, 102, 0.15)',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Inter & JetBrains Mono for log/terminal code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #0d1e3d 0%, #050b18 100%);
        }
        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0c1528;
        }
        ::-webkit-scrollbar-thumb {
            background: #1f2e4d;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #00ccff;
        }
    </style>
    <!-- AlpineJS 3.x via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full antialiased" x-data="corewatchDashboard()">
    
    <!-- Top Glowing Bar -->
    <div class="h-1.5 w-full bg-gradient-to-r from-cyber-blue via-cyber-green to-cyber-red animate-pulse"></div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- HEADER SECTION -->
        <header class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-cyber-border/80 pb-6 gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-cyber-card border border-cyber-blue/40 rounded-lg shadow-neon-blue">
                    <svg class="w-8 h-8 text-cyber-blue animate-spin" style="animation-duration: 8s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        COREWATCH
                        <span class="text-xs font-mono px-2 py-0.5 border border-cyber-green/30 bg-cyber-green/10 text-cyber-green rounded-full shadow-neon-green/10">v13.8-Sentinel</span>
                    </h1>
                    <p class="text-sm text-gray-400">Stealthy DevOps & Real-time Server Health Monitor</p>
                </div>
            </div>

            <!-- Header Controller -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Polling Pulse -->
                <div class="flex items-center gap-2 px-3 py-1.5 bg-cyber-card border border-cyber-border rounded-lg text-xs">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="polling ? 'bg-cyber-green' : 'bg-cyber-red'"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="polling ? 'bg-cyber-green' : 'bg-cyber-red'"></span>
                    </span>
                    <span class="text-gray-300" x-text="polling ? 'Polling Active' : 'Polling Suspended'"></span>
                </div>

                <!-- Last Uptime Info -->
                <div class="px-3.5 py-1.5 bg-cyber-card border border-cyber-border rounded-lg text-xs">
                    <span class="text-gray-400 font-mono">UPTIME:</span>
                    <span class="text-cyber-blue font-semibold font-mono ml-1" x-text="metrics.uptime font-mono">Loading...</span>
                </div>

                <!-- Manual Force Refresh -->
                <button @click="fetchMetrics()" class="flex items-center gap-2 bg-cyber-blue hover:bg-cyber-blue/80 text-[#030712] font-semibold text-xs px-4 py-2 rounded-lg transition duration-200">
                    <svg class="w-3.5 h-3.5" :class="loadingMetrics ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path>
                    </svg>
                    RE-POLL
                </button>
            </div>
        </header>

        <!-- TOP METRICS GRID (CPU, RAM, DISK) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-corewatch::cpu-widget />
            <x-corewatch::ram-widget />
            <x-corewatch::disk-widget />
        </div>

        <!-- COMPETITIVE TELEMETRY GRID: TOP CPU PROCESSES & DATABASE TELEMETRY -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <x-corewatch::processes-widget class="lg:col-span-7" />
            <div class="lg:col-span-5 flex flex-col gap-6">
                <x-corewatch::database-widget />
                <x-corewatch::app-checks-widget />
            </div>
        </section>

        <!-- MIDDLE ROW: HOST SPECIFICATIONS & SERVICES CONTROLLER -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <x-corewatch::specifications-widget class="lg:col-span-5" />
            <x-corewatch::services-widget class="lg:col-span-7" />
        </section>

        <!-- BOTTOM ROW: LIVE LOG STREAMING TERMINAL -->
        <section class="grid grid-cols-1 gap-6">
            <x-corewatch::logs-widget />
        </section>
        
    </div>

    <!-- TERMINAL SERVICE OUTPUT MODAL -->
    <div x-show="outputModal.show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-cyber-card border border-cyber-border rounded-xl max-w-2xl w-full overflow-hidden shadow-neon-blue/20" @click.away="outputModal.show = false">
            
            <!-- Modal Header -->
            <div class="bg-[#0c121e] border-b border-cyber-border px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyber-blue opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-cyber-blue"></span>
                    </span>
                    <span class="text-xs font-mono font-bold tracking-wider text-white" x-text="'EXECUTED: ' + outputModal.title">EXECUTION RESULT</span>
                </div>
                <button @click="outputModal.show = false" class="text-gray-400 hover:text-white transition font-mono text-sm">&times; CLOSE</button>
            </div>

            <!-- Modal Content (Terminal Output stdout) -->
            <div class="p-5">
                <div class="bg-black text-gray-300 p-4 rounded-lg font-mono text-xs overflow-x-auto h-72 border border-cyber-border/80 select-text leading-relaxed">
                    <div class="flex items-center justify-between text-[10px] text-gray-500 border-b border-cyber-border pb-1.5 mb-2 uppercase font-bold">
                        <span>Standard Output / Response</span>
                        <span :class="outputModal.success ? 'text-cyber-green' : 'text-cyber-red'" x-text="outputModal.success ? 'Status Code: 0 (SUCCESS)' : 'Status Code: >0 (FAILED)'"></span>
                    </div>
                    <pre class="whitespace-pre-wrap select-text selection:bg-cyber-blue/30" x-text="outputModal.content"></pre>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-[#0c121e]/80 border-t border-cyber-border px-5 py-3 flex justify-end">
                <button @click="outputModal.show = false" class="bg-cyber-blue hover:bg-cyber-blue/80 text-[#030712] font-semibold text-xs px-4 py-2 rounded transition">
                    ACKNOWLEDGE
                </button>
            </div>

        </div>
    </div>

    <!-- MAIN APP JAVASCRIPT CONTROLLER (AlpineJS corewatchDashboard) -->
    <script>
        function corewatchDashboard() {
            return {
                config: @json($config),
                polling: true,
                loadingMetrics: false,
                metrics: {
                    cpu: { cores: 0, load_1: 0.0, load_5: 0.0, load_15: 0.0, usage_percentage: 0.0 },
                    ram: { total: 0, total_formatted: '-', used: 0, used_formatted: '-', free: 0, free_formatted: '-', available: 0, available_formatted: '-', usage_percentage: 0.0 },
                    disk: { total: 0, total_formatted: '-', used: 0, used_formatted: '-', free: 0, free_formatted: '-', usage_percentage: 0.0, path: '-' },
                    uptime: 'Loading...',
                    system_info: { os: '-', kernel: '-', hostname: '-', php_version: '-', laravel_version: '-', server_software: '-' },
                    services: {},
                    processes: [],
                    database: { driver: '-', tables_count: 0, size_formatted: '-', connection: 'Checking...', active: false },
                    app_checks: {}
                },
                runningServiceKey: '',
                expandedLogIndexes: [],
                outputModal: {
                    show: false,
                    title: '',
                    content: '',
                    success: true
                },
                logs: {
                    loading: false,
                    list: [],
                    activeFile: '',
                    filePath: '-',
                    page: 1,
                    hasMore: false,
                    totalScanned: 0,
                    filters: {
                        level: '',
                        search: '',
                        ip: '',
                        status: ''
                    }
                },
                pollIntervalId: null,

                init() {
                    // Initialize first default log file if configured
                    if (this.config.logs && this.config.logs.length > 0) {
                        this.logs.activeFile = this.config.logs[0].key;
                    }

                    // Initial fetch
                    this.fetchMetrics();
                    this.fetchLogs();

                    // Start automatic metric poll interval
                    this.pollIntervalId = setInterval(() => {
                        if (this.polling) {
                            this.fetchMetrics();
                        }
                    }, this.config.refresh_interval);
                },

                fetchMetrics() {
                    this.loadingMetrics = true;
                    fetch("{{ route('corewatch.api.metrics') }}")
                        .then(response => {
                            if (!response.ok) throw new Error('Authorization failure or server exception.');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                this.metrics = data.metrics;
                            }
                        })
                        .catch(err => {
                            console.error('CoreWatch metrics fetch error:', err);
                            this.polling = false; // Suspend polling on persistent error
                        })
                        .finally(() => {
                            this.loadingMetrics = false;
                        });
                },

                fetchLogs(resetPage = false) {
                    if (resetPage) {
                        this.logs.page = 1;
                    }

                    if (!this.logs.activeFile) return;

                    this.logs.loading = true;
                    this.expandedLogIndexes = [];

                    // Construct URL query parameters
                    let params = new URLSearchParams({
                        file: this.logs.activeFile,
                        page: this.logs.page
                    });

                    if (this.logs.filters.level) params.append('level', this.logs.filters.level);
                    if (this.logs.filters.search) params.append('search', this.logs.filters.search);
                    if (this.logs.filters.ip) params.append('ip', this.logs.filters.ip);
                    if (this.logs.filters.status) params.append('status', this.logs.filters.status);

                    fetch("{{ route('corewatch.api.logs') }}?" + params.toString())
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to retrieve server log stream.');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                this.logs.list = data.logs;
                                this.logs.hasMore = data.has_more;
                                this.logs.totalScanned = data.total_scanned;
                                this.logs.filePath = data.file_path;
                            } else {
                                this.logs.list = [];
                                this.logs.filePath = data.file_path || '-';
                                console.error('CoreWatch error response:', data.error);
                            }
                        })
                        .catch(err => {
                            console.error('CoreWatch log stream connection issue:', err);
                            this.logs.list = [];
                        })
                        .finally(() => {
                            this.logs.loading = false;
                        });
                },

                resetLogFilters() {
                    this.logs.filters = { level: '', search: '', ip: '', status: '' };
                    this.fetchLogs(true);
                },

                toggleLogExpand(index) {
                    if (this.expandedLogIndexes.includes(index)) {
                        this.expandedLogIndexes = this.expandedLogIndexes.filter(i => i !== index);
                    } else {
                        this.expandedLogIndexes.push(index);
                    }
                },

                paginateLogs(direction) {
                    let newPage = this.logs.page + direction;
                    if (newPage < 1) return;
                    this.logs.page = newPage;
                    this.fetchLogs();
                    // Scroll to top of terminal screen
                    document.getElementById('terminal-screen').scrollTop = 0;
                },

                triggerServiceCommand(serviceKey) {
                    if (!confirm('Are you absolutely sure you want to trigger this administrative control task?')) {
                        return;
                    }

                    this.runningServiceKey = serviceKey;

                    fetch("{{ route('corewatch.api.services.control') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ service_key: serviceKey })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.outputModal.title = data.service || 'Control Utility';
                        this.outputModal.success = data.success;
                        this.outputModal.content = data.success 
                            ? (data.output || 'Command executed successfully with no returned output.') 
                            : (data.error || 'Execution encountered an unhandled exception status.');
                        this.outputModal.show = true;

                        // Re-poll metrics to catch updated active/inactive status immediately
                        this.fetchMetrics();
                    })
                    .catch(err => {
                        console.error('Service control transmission issue:', err);
                        this.outputModal.title = 'Transmission Failure';
                        this.outputModal.success = false;
                        this.outputModal.content = 'CoreWatch API connection failed. Ensure web application and PHP process are running.';
                        this.outputModal.show = true;
                    })
                    .finally(() => {
                        this.runningServiceKey = '';
                    });
                }
            };
        }
    </script>
</body>
</html>
