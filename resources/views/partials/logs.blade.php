<!-- Live Log File Stream Engine Partial -->
<div class="bg-cyber-card border border-cyber-border rounded-xl p-5 hover:shadow-neon-blue/5 transition duration-200">
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-cyber-border/60 pb-4 mb-4 gap-4">
        <div>
            <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase flex items-center gap-2">
                <span class="w-1.5 h-3 bg-cyber-orange rounded"></span>
                Live Log File Stream Engine
            </h3>
            <p class="text-xs text-gray-500 mt-1">Chunked backwards-seeking read, safe for multi-gigabyte log files.</p>
        </div>

        <!-- Log File Dropdown -->
        <div class="flex flex-wrap items-center gap-3">
            <select x-model="logs.activeFile" @change="fetchLogs(true)" class="bg-[#050b18] border border-cyber-border text-gray-300 text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:border-cyber-blue font-semibold">
                <template x-for="file in config.logs" :key="file.key">
                    <option :value="file.key" x-text="file.name">Log File</option>
                </template>
            </select>

            <!-- Clear filters -->
            <button @click="resetLogFilters()" class="text-xs border border-cyber-border hover:bg-cyber-card text-gray-400 px-3 py-1.5 rounded-lg transition duration-200 font-mono">
                Clear Filters
            </button>
        </div>
    </div>

    <!-- FILTERS FORM PANEL -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4 p-4 bg-[#050b18]/60 border border-cyber-border/60 rounded-xl text-xs">
        <!-- Filter: Level -->
        <div>
            <label class="block text-[10px] text-gray-500 font-bold uppercase mb-1 font-mono">Log Level</label>
            <select x-model="logs.filters.level" @change="fetchLogs(true)" class="w-full bg-cyber-card border border-cyber-border rounded p-1.5 text-gray-300 font-mono">
                <option value="">ALL LEVELS</option>
                <option value="DEBUG">DEBUG</option>
                <option value="INFO">INFO</option>
                <option value="WARNING">WARNING</option>
                <option value="ERROR">ERROR</option>
                <option value="CRITICAL">CRITICAL</option>
            </select>
        </div>

        <!-- Filter: Search Text -->
        <div class="col-span-2 md:col-span-2">
            <label class="block text-[10px] text-gray-500 font-bold uppercase mb-1 font-mono">Search Text</label>
            <input type="text" x-model="logs.filters.search" @input.debounce.500ms="fetchLogs(true)" placeholder="Query string / errors / URLs..." 
                   class="w-full bg-cyber-card border border-cyber-border rounded p-1.5 text-gray-300 placeholder-gray-600 focus:outline-none focus:border-cyber-blue font-mono">
        </div>

        <!-- Filter: IP Address -->
        <div>
            <label class="block text-[10px] text-gray-500 font-bold uppercase mb-1 font-mono">IP Address</label>
            <input type="text" x-model="logs.filters.ip" @input.debounce.500ms="fetchLogs(true)" placeholder="127.0.0.1" 
                   class="w-full bg-cyber-card border border-cyber-border rounded p-1.5 text-gray-300 placeholder-gray-600 focus:outline-none focus:border-cyber-blue font-mono">
        </div>

        <!-- Filter: HTTP Status -->
        <div>
            <label class="block text-[10px] text-gray-500 font-bold uppercase mb-1 font-mono">Status Code</label>
            <input type="number" x-model="logs.filters.status" @input.debounce.500ms="fetchLogs(true)" placeholder="e.g. 500" 
                   class="w-full bg-cyber-card border border-cyber-border rounded p-1.5 text-gray-300 placeholder-gray-600 focus:outline-none focus:border-cyber-blue font-mono">
        </div>
    </div>

    <!-- TERMINAL VIEWPORT -->
    <div class="relative bg-black/95 rounded-xl border border-cyber-border overflow-hidden shadow-2xl">
        <!-- Terminal Top Controls Bar -->
        <div class="bg-[#0c121e] border-b border-cyber-border px-4 py-2.5 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-cyber-red inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-cyber-orange inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-cyber-green inline-block"></span>
                <span class="text-gray-400 font-mono text-[10px] ml-2 tracking-widest uppercase" x-text="'Active Log File: ' + logs.filePath">Terminal Log Viewer</span>
            </div>
            <div class="flex items-center gap-3 font-mono text-gray-500 text-[10px]">
                <span x-show="logs.totalScanned > 0" x-text="'Scanned: ' + logs.totalScanned + ' lines'"></span>
                <span class="text-cyber-green font-bold">ONLINE</span>
            </div>
        </div>

        <!-- Terminal log list container -->
        <div class="p-4 h-[450px] overflow-y-auto code-font text-xs space-y-2.5" id="terminal-screen">
            
            <!-- Log Loading state -->
            <div x-show="logs.loading" class="flex flex-col items-center justify-center h-full space-y-3">
                <svg class="animate-spin h-8 w-8 text-cyber-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path>
                </svg>
                <span class="text-gray-400 font-mono animate-pulse">Streaming logs from end of file...</span>
            </div>

            <!-- Empty Log state -->
            <div x-show="!logs.loading && logs.list.length === 0" class="flex flex-col items-center justify-center h-full text-center space-y-1.5">
                <svg class="h-10 w-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-gray-400 font-mono">No matching log entries found</div>
                <div class="text-[10px] text-gray-600">Try adjusting the filter configuration or checking another file</div>
            </div>

            <!-- Log List content -->
            <div class="space-y-1.5" x-show="!logs.loading && logs.list.length > 0">
                <template x-for="(log, idx) in logs.list" :key="idx">
                    <div class="bg-cyber-card/30 border border-cyber-border/40 hover:border-cyber-blue/30 rounded p-3 transition duration-150">
                        
                        <!-- Header Row of line -->
                        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-1.5 text-[11px]">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Log Level Badge -->
                                <span class="px-2 py-0.5 rounded-[3px] text-[9px] font-bold font-mono border"
                                      :class="{
                                        'bg-cyber-green/10 border-cyber-green/30 text-cyber-green': log.level === 'INFO' || log.level === 'DEBUG',
                                        'bg-cyber-orange/10 border-cyber-orange/30 text-cyber-orange': log.level === 'WARNING' || log.level === 'WARN',
                                        'bg-cyber-red/10 border-cyber-red/30 text-cyber-red': log.level === 'ERROR' || log.level === 'CRITICAL' || log.level === 'ALERT' || log.level === 'EMERGENCY'
                                      }"
                                      x-text="log.level">
                                    INFO
                                </span>

                                <!-- Timestamp -->
                                <span class="text-gray-500 font-mono" x-text="log.date">2026-05-19 12:00:00</span>

                                <!-- IP Address if exists -->
                                <span x-show="log.ip" class="text-cyber-blue font-mono" x-text="'IP: ' + log.ip"></span>

                                <!-- Status badge if access log -->
                                <span x-show="log.status" class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono"
                                      :class="{
                                        'bg-cyber-green/20 text-cyber-green': log.status < 400,
                                        'bg-cyber-orange/20 text-cyber-orange': log.status >= 400 && log.status < 500,
                                        'bg-cyber-red/20 text-cyber-red': log.status >= 500
                                      }"
                                      x-text="'HTTP ' + log.status">
                                </span>
                            </div>
                            
                            <!-- Expand button -->
                            <button @click="toggleLogExpand(idx)" class="text-gray-500 hover:text-cyber-blue transition text-[10px] font-mono">
                                <span x-text="expandedLogIndexes.includes(idx) ? '[-] COLLAPSE' : '[+] EXPAND RAW'"></span>
                            </button>
                        </div>

                        <!-- Main Message Content -->
                        <div class="text-gray-300 break-words whitespace-pre-wrap selection:bg-cyber-blue/30" x-text="log.message">Log Message Content</div>

                        <!-- Expandable Raw Stack Trace / Raw Details -->
                        <div x-show="expandedLogIndexes.includes(idx)" 
                             x-transition
                             class="mt-3 p-3 bg-black/80 border border-cyber-border rounded overflow-x-auto text-[10px] text-gray-400 select-text font-mono leading-relaxed max-h-72 overflow-y-auto">
                            <div class="text-[9px] uppercase tracking-wider text-gray-500 mb-1 border-b border-cyber-border/40 pb-1 font-bold">Raw Shell Log Line / Stack Trace Block:</div>
                            <pre x-text="log.raw"></pre>
                        </div>

                    </div>
                </template>
            </div>

        </div>

        <!-- Terminal bottom navigation / Pagination -->
        <div class="bg-[#0c121e] border-t border-cyber-border px-4 py-3 flex items-center justify-between text-xs" x-show="!logs.loading && logs.list.length > 0">
            <!-- Left controls -->
            <button @click="paginateLogs(-1)" :disabled="logs.page <= 1" class="flex items-center gap-1 border border-cyber-border px-3 py-1.5 rounded-lg text-gray-400 hover:bg-cyber-card transition disabled:opacity-30">
                ← PREV
            </button>
            
            <!-- Middle Page Tracker -->
            <span class="font-mono text-gray-400">
                PAGE <span class="text-cyber-blue font-bold" x-text="logs.page">1</span>
            </span>

            <!-- Right controls -->
            <button @click="paginateLogs(1)" :disabled="!logs.hasMore" class="flex items-center gap-1 border border-cyber-border px-3 py-1.5 rounded-lg text-gray-400 hover:bg-cyber-card transition disabled:opacity-30">
                NEXT →
            </button>
        </div>
    </div>
</div>
