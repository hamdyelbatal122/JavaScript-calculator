<!-- Top Active CPU Linux Processes Widget Component -->
<div {{ $attributes->merge(['class' => 'bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-blue rounded"></span>
            Top Active CPU Processes
        </h3>
        <span class="text-[10px] font-mono text-gray-500 uppercase font-mono">Live sorting</span>
    </div>

    <div class="bg-black/80 rounded-lg border border-cyber-border/80 overflow-hidden">
        <table class="w-full text-left text-xs font-mono select-text">
            <thead>
                <tr class="bg-[#0c121e] border-b border-cyber-border text-cyber-blue text-[10px] uppercase font-bold">
                    <th class="p-2.5">PID</th>
                    <th class="p-2.5">USER</th>
                    <th class="p-2.5">CPU</th>
                    <th class="p-2.5">MEM</th>
                    <th class="p-2.5">COMMAND</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loading / Empty Processes fallback -->
                <tr x-show="!metrics.processes || metrics.processes.length === 0">
                    <td colspan="5" class="p-8 text-center text-gray-500 font-mono">
                        No active processes detected (or shell disabled)
                    </td>
                </tr>
                <!-- Processes loop -->
                <template x-for="proc in metrics.processes" :key="proc.pid">
                    <tr class="border-b border-cyber-border/40 hover:bg-cyber-card/40 transition font-mono">
                        <td class="p-2.5 text-gray-400 font-semibold" x-text="proc.pid"></td>
                        <td class="p-2.5 text-gray-400" x-text="proc.user"></td>
                        <td class="p-2.5 text-cyber-green font-bold" x-text="proc.cpu"></td>
                        <td class="p-2.5 text-cyber-blue" x-text="proc.mem"></td>
                        <td class="p-2.5 text-white truncate max-w-[150px]" :title="proc.command" x-text="proc.command"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
