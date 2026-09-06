<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-[#166534]"></i>
                <span>System Audit Trail & Activity Logs</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Immutable audit trail logging administrative, faculty, and system security actions.</p>
        </div>

        <button wire:click="clearOldLogs" onclick="return confirm('Are you sure you want to purge log records older than 30 days?')" class="px-4 py-2.5 bg-zinc-800 hover:bg-zinc-900 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-trash-can"></i>
            <span>Purge Logs (>30 Days)</span>
        </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Action, Actor Name, Login ID, or Detail Description..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div>
                <select wire:model.live="filterAction" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Action Types</option>
                    @foreach ($actions as $act)
                        <option value="{{ $act }}">{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterUserId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All System Actors</option>
                    @foreach ($users as $usr)
                        <option value="{{ $usr->id }}">{{ $usr->name }} ({{ $usr->login_id }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Audit Logs Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Timestamp</th>
                        <th class="py-3 px-4 text-start">System Actor</th>
                        <th class="py-3 px-4 text-start">Action Category</th>
                        <th class="py-3 px-4 text-start">Activity Description</th>
                        <th class="py-3 px-4 text-end">Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($logs as $l)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono text-[11px] text-zinc-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($l->created_at)->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $l->user?->name ?? 'System' }}</span>
                                    <span class="font-mono text-[10px] text-[#166534] bg-emerald-50 dark:bg-emerald-950 px-1.5 py-0.5 rounded border border-emerald-200 uppercase">
                                        {{ $l->user?->role ?? 'System' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded font-bold text-[11px] bg-[#DBEAFE] text-blue-800 border border-blue-200">
                                    {{ $l->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                {{ $l->description }}
                            </td>
                            <td class="py-3.5 px-4 text-end">
                                <button wire:click="openDetailModal({{ $l->id }})" title="View Log Detail JSON" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-clock-rotate-left text-2xl mb-2 text-zinc-300 block"></i>
                                No audit log records found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- LOG DETAIL MODAL -->
    @if ($showDetailModal && $selectedLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-info-circle text-[#166534]"></i>
                        <span>Audit Log Entry #{{ $selectedLog->id }}</span>
                    </h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="space-y-3 font-mono text-[11px]">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1">
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold">Action Category</span>
                        <span class="font-bold text-blue-700 dark:text-blue-400 block">{{ $selectedLog->action }}</span>
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1">
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold">Actor / User</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $selectedLog->user?->name }} (ID: {{ $selectedLog->user?->login_id }})</span>
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1">
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold">Description Payload</span>
                        <p class="font-sans text-zinc-800 dark:text-zinc-200 text-xs leading-relaxed">{{ $selectedLog->description }}</p>
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1">
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold">Recorded Timestamp</span>
                        <span class="font-bold text-zinc-700 dark:text-zinc-300 block">{{ $selectedLog->created_at }}</span>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <button wire:click="$set('showDetailModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
