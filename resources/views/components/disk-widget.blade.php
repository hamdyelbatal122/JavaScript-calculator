<!-- Disk Space Telemetry Widget Component -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 relative overflow-hidden transition hover:border-cyber-blue/40 shadow-neon-blue/5"
     :class="metrics.disk.usage_percentage >= 90 ? 'shadow-neon-red/10 border-cyber-red/30' : 'hover:shadow-neon-blue/5'">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-cyber-blue/10 border border-cyber-blue/30 text-cyber-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
            </span>
            <span class="text-sm font-semibold tracking-wide text-gray-300">DISK SPACE</span>
        </div>
        <span class="text-xs font-mono bg-cyber-blue/10 border border-cyber-blue/20 text-cyber-blue px-2 py-0.5 rounded" x-text="metrics.disk.total_formatted">0 GB</span>
    </div>

    <!-- Metrics Display -->
    <div class="flex items-baseline gap-2 mb-3">
        <span class="text-4xl font-extrabold tracking-tight font-mono" 
              :class="metrics.disk.usage_percentage >= 90 ? 'text-cyber-red' : (metrics.disk.usage_percentage >= 75 ? 'text-cyber-orange' : 'text-cyber-green')"
              x-text="metrics.disk.usage_percentage + '%'">0%</span>
        <span class="text-xs text-gray-400">disk capacity</span>
    </div>

    <!-- Custom Progress Bar -->
    <div class="h-2 w-full bg-[#050b18] border border-cyber-border/80 rounded-full overflow-hidden mb-4">
        <div class="h-full rounded-full transition-all duration-500 ease-out"
             :class="metrics.disk.usage_percentage >= 90 ? 'bg-cyber-red' : (metrics.disk.usage_percentage >= 75 ? 'bg-cyber-orange' : 'bg-cyber-green')"
             :style="'width: ' + metrics.disk.usage_percentage + '%'"></div>
    </div>

    <!-- Extra details -->
    <div class="grid grid-cols-2 gap-2 text-center text-xs font-mono text-gray-400">
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">USED</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.disk.used_formatted">0 GB</div>
        </div>
        <div class="bg-[#050b18]/60 p-1.5 border border-cyber-border/40 rounded">
            <div class="text-[10px] text-gray-500">FREE</div>
            <div class="text-white font-medium mt-0.5" x-text="metrics.disk.free_formatted">0 GB</div>
        </div>
    </div>
</div>
