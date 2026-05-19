<!-- Main Dashboard Script Partial -->
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
