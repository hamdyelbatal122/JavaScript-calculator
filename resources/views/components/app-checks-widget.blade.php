<!-- Application Diagnostics integrity Checklist Widget Component -->
<div {{ $attributes->merge(['class' => 'bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200 flex flex-col justify-between h-full']) }}>
    <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2 mb-4">
        <span class="w-1.5 h-3 bg-cyber-orange rounded"></span>
        App Integrity Checks
    </h3>

    <div class="space-y-2.5 font-mono text-xs">
        <template x-for="(check, key) in metrics.app_checks" :key="key">
            <div class="flex items-center justify-between p-2 bg-[#050b18]/40 border border-cyber-border/40 rounded hover:border-cyber-blue/30 transition">
                <div class="space-y-0.5">
                    <div class="text-white font-semibold text-[11px]" x-text="check.name">Check</div>
                    <div class="text-[9px] text-gray-500" x-text="check.detail">Detail</div>
                </div>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border"
                      :class="check.active 
                        ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' 
                        : 'bg-cyber-orange/10 border-cyber-orange/30 text-cyber-orange'"
                      x-text="check.status">
                    Status
                </span>
            </div>
        </template>
    </div>
</div>
