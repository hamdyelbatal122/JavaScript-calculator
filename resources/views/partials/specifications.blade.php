<!-- Host Specifications Monospace Table Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200">
    <div class="flex items-center justify-between mb-4 border-b border-cyber-border/40 pb-2">
        <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase mb-0.5 flex items-center gap-2">
            <span class="w-1.5 h-3 bg-cyber-blue rounded"></span>
            Host Specifications
        </h3>
        <span class="text-[10px] font-mono text-gray-500 uppercase">Static Stats</span>
    </div>

    <table class="w-full text-left text-xs font-mono select-text">
        <tbody>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500 font-semibold">Host / IP</td>
                <td class="py-2.5 text-right text-white" x-text="metrics.system_info.hostname">localhost</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Operating System</td>
                <td class="py-2.5 text-right text-white" x-text="metrics.system_info.os">Linux</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Kernel Version</td>
                <td class="py-2.5 text-right text-cyber-blue font-semibold truncate max-w-[150px]" :title="metrics.system_info.kernel" x-text="metrics.system_info.kernel">-</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">PHP Version</td>
                <td class="py-2.5 text-right text-white font-semibold" x-text="metrics.system_info.php_version">-</td>
            </tr>
            <tr class="border-b border-cyber-border/20 hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Laravel Core</td>
                <td class="py-2.5 text-right text-cyber-green font-bold" x-text="'v' + metrics.system_info.laravel_version">-</td>
            </tr>
            <tr class="hover:bg-[#050b18]/40 transition">
                <td class="py-2.5 text-gray-500">Server Software</td>
                <td class="py-2.5 text-right text-white truncate max-w-[150px]" :title="metrics.system_info.server_software" x-text="metrics.system_info.server_software">-</td>
            </tr>
        </tbody>
    </table>
</div>
