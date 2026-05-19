<!-- Terminal Service Output Modal Partial -->
<div x-show="outputModal.show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
    <div class="bg-cyber-card border border-cyber-border rounded-xl max-w-2xl w-full overflow-hidden shadow-neon-blue/20" @click.away="outputModal.show = false">
        
        <!-- Modal Header -->
        <div class="bg-[#0c121e] border-b border-cyber-border px-5 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyber-blue opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyber-blue"></span>
                </span>
                <span class="text-xs font-mono font-bold tracking-wider text-white" x-text="'EXECUTED: ' + outputModal.title">EXECUTION RESULT</span>
            </div>
            <button @click="outputModal.show = false" class="text-gray-400 hover:text-white transition font-mono text-sm">&times; CLOSE</button>
        </div>

        <!-- Modal Content (Terminal Output stdout) -->
        <div class="p-5">
            <div class="bg-black text-gray-300 p-4 rounded-lg font-mono text-xs overflow-x-auto h-72 border border-cyber-border/80 select-text leading-relaxed">
                <div class="flex items-center justify-between text-[10px] text-gray-500 border-b border-cyber-border pb-1.5 mb-2 uppercase font-bold">
                    <span>Standard Output / Response</span>
                    <span :class="outputModal.success ? 'text-cyber-green' : 'text-cyber-red'" x-text="outputModal.success ? 'Status Code: 0 (SUCCESS)' : 'Status Code: >0 (FAILED)'"></span>
                </div>
                <pre class="whitespace-pre-wrap select-text selection:bg-cyber-blue/30" x-text="outputModal.content"></pre>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-[#0c121e]/80 border-t border-cyber-border px-5 py-3 flex justify-end">
            <button @click="outputModal.show = false" class="bg-cyber-blue hover:bg-cyber-blue/80 text-[#030712] font-semibold text-xs px-4 py-2 rounded transition">
                ACKNOWLEDGE
            </button>
        </div>

    </div>
</div>
