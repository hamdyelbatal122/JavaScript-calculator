<!-- Database Sizing Telemetry Widget Component -->
<div {{ $attributes->merge(['class' => 'bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200 flex flex-col justify-between h-full']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-purple rounded"></span>
            Database Telemetry
        </h3>
        <span class="px-2.5 py-0.5 rounded text-[9px] font-bold font-mono border"
              :class="metrics.database && metrics.database.active ? 'bg-cyber-green/10 border-cyber-green/30 text-cyber-green' : 'bg-cyber-red/10 border-cyber-red/30 text-cyber-red'"
              x-text="metrics.database ? metrics.database.connection : 'Checking connection...'">
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 text-center font-mono">
        <div class="bg-[#050b18]/60 p-3 border border-cyber-border/60 rounded-lg">
            <div class="text-[10px] text-gray-500 font-semibold uppercase">Engine</div>
            <div class="text-cyber-blue font-bold text-base mt-1" x-text="metrics.database ? metrics.database.driver : '-'">-</div>
        </div>
        <div class="bg-[#050b18]/60 p-3 border border-cyber-border/60 rounded-lg">
            <div class="text-[10px] text-gray-500 font-semibold uppercase font-mono">Sizing</div>
            <div class="text-white font-bold text-base mt-1" x-text="metrics.database ? metrics.database.size_formatted : '0 B'">0 B</div>
        </div>
        <div class="bg-[#050b18]/60 p-3 border border-cyber-border/60 rounded-lg col-span-2">
            <div class="text-[10px] text-gray-500 font-semibold uppercase font-mono">Total Database Tables</div>
            <div class="text-cyber-green font-bold text-base mt-1" x-text="metrics.database ? metrics.database.tables_count + ' Tables' : '0 Tables'">0 Tables</div>
        </div>
    </div>
</div>
