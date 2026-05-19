<!-- Administrative Services Controller Widget Component -->
<div {{ $attributes->merge(['class' => 'bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-green rounded"></span>
            Service Controller Daemon
        </h3>
        <span class="text-xs text-gray-500 font-mono">Secure Pre-Whitelisted Triggers</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Loop for System Process statuses -->
        <template x-for="(status, key) in metrics.services" :key="key">
            <div class="bg-[#050b18]/60 p-3.5 border border-cyber-border/60 rounded-xl flex items-center justify-between">
                <div class="space-y-1">
                    <div class="text-xs font-semibold text-white" x-text="status.name">Service</div>
                    <div class="text-[10px] text-gray-500 font-mono">
                        <span x-text="status.port ? 'Port: ' + status.port : 'Process daemon'"></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold tracking-wider uppercase border"
                          :class="status.active 
                            ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' 
                            : 'bg-cyber-red/10 border-cyber-red/30 text-cyber-red animate-pulse'"
                          x-text="status.active ? 'ACTIVE' : 'INACTIVE'">
                        Status
                    </span>
                </div>
            </div>
        </template>

        <!-- Whitelisted administrator tasks triggers -->
        <template x-for="service in config.services" :key="service.key">
            <div class="bg-[#050b18]/40 border border-cyber-border/40 p-3.5 rounded-xl flex items-center justify-between hover:border-cyber-blue/30 transition">
                <div class="space-y-0.5">
                    <div class="text-xs font-semibold text-gray-300" x-text="service.name">Artisan Action</div>
                    <div class="text-[9px] font-mono text-gray-500 font-mono">Trigger safe execution</div>
                </div>
                
                <button @click="triggerServiceCommand(service.key)" 
                        :disabled="runningServiceKey === service.key"
                        class="text-[10px] font-bold tracking-wider uppercase bg-cyber-blue/10 hover:bg-cyber-blue text-cyber-blue hover:text-[#030712] border border-cyber-blue/30 px-3 py-1.5 rounded transition disabled:opacity-40 flex items-center gap-1.5">
                    <svg x-show="runningServiceKey === service.key" class="animate-spin w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path>
                    </svg>
                    <span x-text="runningServiceKey === service.key ? 'RUNNING' : 'RUN'">RUN</span>
                </button>
            </div>
        </template>

    </div>
</div>
