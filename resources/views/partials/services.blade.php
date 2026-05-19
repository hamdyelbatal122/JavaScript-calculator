<!-- Administrative Services Controller Monospace Table Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200">
    <div class="flex items-center justify-between mb-4 border-b border-cyber-border/40 pb-2">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-green rounded"></span>
            Service Controller Daemon
        </h3>
        <span class="text-xs text-gray-500 font-mono">Secure Whitelisted Triggers</span>
    </div>

    <table class="w-full text-left text-xs font-mono select-text">
        <thead>
            <tr class="bg-[#0c121e] border-b border-cyber-border text-cyber-blue text-[10px] uppercase font-bold">
                <th class="p-2">Service / Task</th>
                <th class="p-2">Type</th>
                <th class="p-2 text-right">Status / Control</th>
            </tr>
        </thead>
        <tbody>
            <!-- Services Status Listing -->
            <template x-for="(status, key) in metrics.services" :key="key">
                <tr class="border-b border-cyber-border/40 hover:bg-cyber-card/40 transition">
                    <td class="p-2 text-white font-semibold" x-text="status.name">Service Name</td>
                    <td class="p-2 text-gray-500" x-text="status.port ? 'Port: ' + status.port : 'Process'">Port/Process</td>
                    <td class="p-2 text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold tracking-wider uppercase border"
                              :class="status.active 
                                ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' 
                                : 'bg-cyber-red/10 border-cyber-red/30 text-cyber-red animate-pulse'"
                              x-text="status.active ? 'ACTIVE' : 'INACTIVE'">
                            Status
                        </span>
                    </td>
                </tr>
            </template>

            <!-- Admin Actions Triggers -->
            <template x-for="service in config.services" :key="service.key">
                <tr class="border-b border-cyber-border/40 hover:bg-cyber-card/40 transition">
                    <td class="p-2 text-gray-300 font-semibold" x-text="service.name">Artisan Action</td>
                    <td class="p-2 text-gray-500">Secure Tool</td>
                    <td class="p-2 text-right">
                        <button @click="triggerServiceCommand(service.key)" 
                                :disabled="runningServiceKey === service.key"
                                class="text-[10px] font-bold tracking-wider uppercase bg-cyber-blue/10 hover:bg-cyber-blue text-cyber-blue hover:text-[#030712] border border-cyber-blue/30 px-3 py-1 rounded transition disabled:opacity-40 flex items-center gap-1.5 ml-auto">
                            <svg x-show="runningServiceKey === service.key" class="animate-spin w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path>
                            </svg>
                            <span x-text="runningServiceKey === service.key ? 'RUNNING' : 'RUN'">RUN</span>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
