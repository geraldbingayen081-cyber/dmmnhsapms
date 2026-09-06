<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-list-ol text-[#166534]"></i>
                <span>Quarter Management</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage quarterly grading periods and active evaluation periods per school year.</p>
        </div>

        <!--<button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add Quarter</span>
        </button>-->
    </div>

    <!-- School Year Selector Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-3">
            <label class="font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Target School Year:</label>
            <select wire:model.live="selectedSchoolYearId" class="px-3.5 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                @foreach ($schoolYears as $sy)
                    <option value="{{ $sy->id }}">SY {{ $sy->school_year }} {{ $sy->status === 'active' ? '(Active SY)' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Quarters Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-center w-24">Sort Order</th>
                        <th class="py-3 px-4 text-start">Quarter Name</th>
                        <th class="py-3 px-4 text-start">Short Code</th>
                        <th class="py-3 px-4 text-start">Grading Period Dates</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($quarters as $q)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-zinc-500">
                                #{{ $q->sort_order }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $q->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded font-mono font-bold text-[11px] bg-[#DBEAFE] text-blue-800 border border-blue-200">
                                    {{ $q->short_name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400 font-medium">
                                @if ($q->start_date && $q->end_date)
                                    {{ \Carbon\Carbon::parse($q->start_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($q->end_date)->format('M d, Y') }}
                                @else
                                    <span class="text-zinc-400 italic">Not set</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($q->status === 'active')
                                    <span class="px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-[10px] border border-emerald-200 uppercase">
                                        Active
                                    </span>
                                @elseif ($q->status === 'upcoming')
                                    <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-bold text-[10px] border border-amber-200 uppercase">
                                        Upcoming
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 font-bold text-[10px] border border-blue-200 uppercase">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                <button wire:click="openEditModal({{ $q->id }})" title="Edit Details" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                                @if ($q->status !== 'active')
                                    <button wire:click="activateQuarter({{ $q->id }})" title="Set as Active Quarter" class="p-1.5 text-zinc-600 hover:text-emerald-700 hover:bg-zinc-100 rounded-md transition">
                                        <i class="fas fa-play text-xs text-emerald-600"></i>
                                    </button>
                                @endif
                                @if ($q->status === 'active')
                                    <button wire:click="completeQuarter({{ $q->id }})" title="Mark Completed" class="p-1.5 text-zinc-600 hover:text-blue-700 hover:bg-zinc-100 rounded-md transition">
                                        <i class="fas fa-circle-check text-xs text-blue-600"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-list-ol text-2xl mb-2 text-zinc-300 block"></i>
                                No quarters configured for the selected school year.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-plus text-[#166534]"></i>
                        <span>Add Quarter</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createQuarter" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Quarter Name *</label>
                        <input type="text" wire:model.defer="name" required placeholder="e.g. 1st Quarter" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        @error('name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Short Code *</label>
                            <input type="text" wire:model.defer="short_name" required placeholder="e.g. Q1" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                            @error('short_name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sort Order *</label>
                            <input type="number" wire:model.defer="sort_order" required min="1" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Start Date</label>
                            <input type="date" wire:model.defer="start_date" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">End Date</label>
                            <input type="date" wire:model.defer="end_date" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                        <select wire:model.defer="status" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active (Current Evaluation Period)</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Quarter</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- EDIT MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-pen-to-square text-[#166534]"></i>
                        <span>Edit Quarter</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateQuarter" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Quarter Name *</label>
                        <input type="text" wire:model.defer="name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Short Code *</label>
                            <input type="text" wire:model.defer="short_name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sort Order *</label>
                            <input type="number" wire:model.defer="sort_order" required min="1" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Start Date</label>
                            <input type="date" wire:model.defer="start_date" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">End Date</label>
                            <input type="date" wire:model.defer="end_date" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                        <select wire:model.defer="status" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
