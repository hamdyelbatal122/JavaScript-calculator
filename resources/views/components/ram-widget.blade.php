<!-- RAM Telemetry Widget Component -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 relative overflow-hidden transition hover:border-cyber-blue/40 shadow-neon-blue/5"
     :class="metrics.ram.usage_percentage >= 90 ? 'shadow-neon-red/10 border-cyber-red/30' : 'hover:shadow-neon-blue/5'">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-cyber-blue/10 border border-cyber-blue/30 text-cyber-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </span>
            <span class="text-sm font-semibold tracking-wide text-gray-300">RAM ALLOCATION</span>
        </div>
        <span class="text-xs font-mono bg-cyber-blue/10 border border-cyber-blue/20 text-cyber-blue px-2 py-0.5 rounded" x-text="metrics.ram.total_formatted">0 GB</span>
    </div>

    <!-- Metrics Display -->
    <div class="flex items-baseline gap-2 mb-3">
        <span class="text-4xl font-extrabold tracking-tight font-mono" 
              :class="metrics.ram.usage_percentage >= 90 ? 'text-cyber-red' : (metrics.ram.usage_percentage >= 70 ? 'text-cyber-orange' : 'text-cyber-green')"
              x-text="metrics.ram.usage_percentage + '%'">0%</span>
        <span class="text-xs text-gray-400">active memory</span>
    </div>

    <!-- Custom Progress Bar -->
    <div class="h-2 w-full bg-[#050b18] border border-cyber-border/80 rounded-full overflow-hidden mb-4">
        <div class="h-full rounded-full transition-all duration-500 ease-out"
             :class="metrics.ram.usage_percentage >= 90 ? 'bg-cyber-red' : (metrics.ram.usage_percentage >= 70 ? 'bg-cyber-orange' : 'bg-cyber-green')"
             :style="'width: ' + metrics.ram.usage_percentage + '%'"></div>
    </div>

    <!-- Extra details -->
    <div class="grid grid-cols-2 gap-2 text-center text-xs font-mono text-gray-400">
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">USED</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.ram.used_formatted">0 MB</div>
        </div>
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">AVAILABLE</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.ram.available_formatted">0 MB</div>
        </div>
    </div>
</div>
