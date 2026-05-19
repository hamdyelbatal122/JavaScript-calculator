<!-- Header Panel Interface Partial -->
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
            <span class="text-cyber-blue font-semibold font-mono ml-1" x-text="metrics.uptime">Loading...</span>
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
