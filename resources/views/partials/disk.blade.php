<!-- Disk Space Monospace Telemetry Table Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:border-cyber-blue/40 transition duration-200"
     :class="metrics.disk.usage_percentage >= 90 ? 'shadow-neon-red/10 border-cyber-red/30' : 'hover:shadow-neon-blue/5'">
    <div class="flex items-center justify-between mb-4 border-b border-cyber-border/40 pb-2">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyber-blue animate-pulse"></span>
            <span class="text-xs font-mono font-bold tracking-widest text-cyber-blue uppercase">DISK DIAGNOSTICS</span>
        </div>
        <span class="text-[10px] font-mono bg-cyber-blue/10 border border-cyber-blue/20 text-cyber-blue px-2 py-0.5 rounded" x-text="metrics.disk.total_formatted">0 GB</span>
    </div>

    <table class="w-full text-left text-xs font-mono select-text">
        <tbody>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500 font-semibold">Active Usage</td>
                <td class="py-2.5 text-right font-bold" 
                    :class="metrics.disk.usage_percentage >= 90 ? 'text-cyber-red' : (metrics.disk.usage_percentage >= 75 ? 'text-cyber-orange' : 'text-cyber-green')" 
                    x-text="metrics.disk.usage_percentage + '%'">0%</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Allocated Disk Space</td>
                <td class="py-2.5 text-right text-white font-semibold" x-text="metrics.disk.used_formatted">0 GB</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Available Storage</td>
                <td class="py-2.5 text-right text-white font-semibold" x-text="metrics.disk.free_formatted">0 GB</td>
            </tr>
            <tr class="hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Workspace Base Path</td>
                <td class="py-2.5 text-right text-cyber-blue font-semibold truncate max-w-[120px]" :title="metrics.disk.path" x-text="metrics.disk.path">-</td>
            </tr>
        </tbody>
    </table>
</div>
