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
    <!-- Google Fonts: Inter & JetBrains Mono -->
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
        @include('corewatch::partials.header')

        <!-- TOP METRICS GRID (CPU, RAM, DISK AS MONOSPACE TABLES) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @include('corewatch::partials.cpu')
            @include('corewatch::partials.ram')
            @include('corewatch::partials.disk')
        </div>

        <!-- COMPETITIVE TELEMETRY GRID: TOP CPU PROCESSES & DATABASE TELEMETRY -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7">
                @include('corewatch::partials.processes')
            </div>
            <div class="lg:col-span-5 flex flex-col gap-6">
                @include('corewatch::partials.database')
                @include('corewatch::partials.app-checks')
            </div>
        </section>

        <!-- MIDDLE ROW: HOST SPECIFICATIONS & SERVICES CONTROLLER -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-5">
                @include('corewatch::partials.specifications')
            </div>
            <div class="lg:col-span-7">
                @include('corewatch::partials.services')
            </div>
        </section>

        <!-- BOTTOM ROW: LIVE LOG STREAMING TERMINAL -->
        <section class="grid grid-cols-1 gap-6">
            @include('corewatch::partials.logs')
        </section>
        
    </div>

    <!-- TERMINAL SERVICE OUTPUT MODAL -->
    @include('corewatch::partials.modal')

    <!-- MAIN APP JAVASCRIPT CONTROLLER -->
    @include('corewatch::partials.script')

</body>
</html>
