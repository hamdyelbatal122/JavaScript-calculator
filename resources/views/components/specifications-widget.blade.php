<!-- Host Specifications Widget Component -->
<div {{ $attributes->merge(['class' => 'bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200']) }}>
    <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase mb-4 flex items-center gap-2">
        <span class="w-1.5 h-3 bg-cyber-blue rounded"></span>
        Host Specifications
    </h3>

    <div class="space-y-3 font-mono text-xs">
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">Host Domain / IP</span>
            <span class="text-white font-semibold text-right" x-text="metrics.system_info.hostname">localhost</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">Operating System</span>
            <span class="text-white font-semibold text-right" x-text="metrics.system_info.os">Linux</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">Kernel Version</span>
            <span class="text-cyber-blue font-semibold text-right" x-text="metrics.system_info.kernel">-</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">PHP Version</span>
            <span class="text-white font-semibold text-right" x-text="metrics.system_info.php_version">-</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">Laravel Core</span>
            <span class="text-cyber-green font-semibold text-right" x-text="'v' + metrics.system_info.laravel_version">-</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-cyber-border/40">
            <span class="text-gray-400">Server Software</span>
            <span class="text-white font-semibold text-right truncate max-w-[200px]" :title="metrics.system_info.server_software" x-text="metrics.system_info.server_software">-</span>
        </div>
        <div class="flex justify-between items-center py-2">
            <span class="text-gray-400">Base Workspace Path</span>
            <span class="text-cyber-blue font-semibold text-right truncate max-w-[200px]" :title="metrics.disk.path" x-text="metrics.disk.path">-</span>
        </div>
    </div>
</div>
