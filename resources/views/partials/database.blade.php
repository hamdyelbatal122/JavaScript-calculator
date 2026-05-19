<!-- Database Sizing Telemetry Table Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200 flex flex-col justify-between h-full">
    <div class="flex items-center justify-between mb-4 border-b border-cyber-border/40 pb-2">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-purple rounded"></span>
            Database Telemetry
        </h3>
        <span class="px-2.5 py-0.5 rounded text-[9px] font-bold font-mono border"
              :class="metrics.database && metrics.database.active ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' : 'bg-cyber-red/10 border-cyber-red/30 text-cyber-red'"
              x-text="metrics.database ? metrics.database.connection : 'Checking connection...'">
        </span>
    </div>

    <table class="w-full text-left text-xs font-mono select-text">
        <tbody>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500 font-semibold">Active Engine</td>
                <td class="py-2.5 text-right text-cyber-blue font-bold" x-text="metrics.database ? metrics.database.driver : '-'">-</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Database Size</td>
                <td class="py-2.5 text-right text-white font-semibold" x-text="metrics.database ? metrics.database.size_formatted : '0 B'">0 B</td>
            </tr>
            <tr class="hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Total Tables Count</td>
                <td class="py-2.5 text-right text-cyber-green font-semibold" x-text="metrics.database ? metrics.database.tables_count + ' Tables' : '0 Tables'">0 Tables</td>
            </tr>
        </tbody>
    </table>
</div>
