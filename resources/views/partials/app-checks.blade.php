<!-- Application Integrity Checklist Table Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200 flex flex-col justify-between h-full">
    <div class="flex items-center justify-between mb-4 border-b border-cyber-border/40 pb-2">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-orange rounded"></span>
            App Integrity Checks
        </h3>
        <span class="text-[10px] font-mono text-gray-500 uppercase">Operational Checks</span>
    </div>

    <table class="w-full text-left text-xs font-mono select-text">
        <tbody>
            <template x-for="(check, key) in metrics.app_checks" :key="key">
                <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                    <td class="py-2.5">
                        <div class="text-white font-semibold text-[11px]" x-text="check.name">Check</div>
                        <div class="text-[9px] text-gray-500" x-text="check.detail">Detail</div>
                    </td>
                    <td class="py-2.5 text-right">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border"
                              :class="check.active 
                                ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' 
                                : 'bg-cyber-orange/10 border-cyber-orange/30 text-cyber-orange'"
                              x-text="check.status">
                            Status
                        </span>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
