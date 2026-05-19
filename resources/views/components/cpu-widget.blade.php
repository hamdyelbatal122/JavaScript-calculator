<!-- CPU Telemetry Widget Component -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 relative overflow-hidden transition hover:border-cyber-blue/40 shadow-neon-blue/5"
     :class="metrics.cpu.usage_percentage >= 85 ? 'shadow-neon-red/10 border-cyber-red/30' : 'hover:shadow-neon-blue/5'">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-cyber-blue/10 border border-cyber-blue/30 text-cyber-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
            </span>
            <span class="text-sm font-semibold tracking-wide text-gray-300">CPU UTILIZATION</span>
        </div>
        <span class="text-xs font-mono bg-cyber-blue/10 border border-cyber-blue/20 text-cyber-blue px-2 py-0.5 rounded" x-text="metrics.cpu.cores + ' Cores'">0 Cores</span>
    </div>

    <!-- Metrics Display -->
    <div class="flex items-baseline gap-2 mb-3">
        <span class="text-4xl font-extrabold tracking-tight font-mono" 
              :class="metrics.cpu.usage_percentage >= 85 ? 'text-cyber-red' : (metrics.cpu.usage_percentage >= 60 ? 'text-cyber-orange' : 'text-cyber-green')"
              x-text="metrics.cpu.usage_percentage + '%'">0%</span>
        <span class="text-xs text-gray-400">average capacity</span>
    </div>

    <!-- Custom Progress Bar -->
    <div class="h-2 w-full bg-[#050b18] border border-cyber-border/80 rounded-full overflow-hidden mb-4">
        <div class="h-full rounded-full transition-all duration-500 ease-out"
             :class="metrics.cpu.usage_percentage >= 85 ? 'bg-cyber-red' : (metrics.cpu.usage_percentage >= 60 ? 'bg-cyber-orange' : 'bg-cyber-green')"
             :style="'width: ' + metrics.cpu.usage_percentage + '%'"></div>
    </div>

    <!-- Extra details -->
    <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono text-gray-400">
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">LOAD (1M)</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.cpu.load_1">0.0</div>
        </div>
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">LOAD (5M)</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.cpu.load_5">0.0</div>
        </div>
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">LOAD (15M)</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.cpu.load_15">0.0</div>
        </div>
    </div>
</div>
