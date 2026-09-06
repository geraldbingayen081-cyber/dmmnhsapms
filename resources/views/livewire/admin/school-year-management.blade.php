<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-calendar-days text-[#166534]"></i>
                <span>School Year Management</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Setup academic school year containers and activate academic sessions.</p>
        </div>

        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Create School Year</span>
        </button>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm text-xs">
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search school year (e.g. 2026-2027)..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>
    </div>

    <!-- School Year Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">School Year</th>
                        <th class="py-3 px-4 text-start">Duration Period</th>
                        <th class="py-3 px-4 text-center">Sections</th>
                        <th class="py-3 px-4 text-center">Quarters</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($schoolYears as $sy)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-base text-[#166534] dark:text-emerald-400">
                                SY {{ $sy->school_year }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400 font-medium">
                                {{ \Carbon\Carbon::parse($sy->start_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($sy->end_date)->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded font-bold text-[11px] bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                    {{ $sy->school_year_sections_count }} {{ Str::plural('Section', $sy->school_year_sections_count) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded font-bold text-[11px] bg-[#DBEAFE] text-blue-800 border border-blue-200">
                                    {{ $sy->quarters_count }} Quarters
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($sy->status === 'active')
                                    <span class="px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-[10px] border border-emerald-200 uppercase">
                                        Active
                                    </span>
                                @elseif ($sy->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-bold text-[10px] border border-amber-200 uppercase">
                                        Draft
                                    </span>
                                @elseif ($sy->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 font-bold text-[10px] border border-blue-200 uppercase">
                                        Completed
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-zinc-100 text-zinc-600 font-bold text-[10px] border border-zinc-200 uppercase">
                                        Archived
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                <a href="{{ route('admin.school-years.configure', $sy->id) }}" title="Configure Quarters & Sections" class="p-1.5 text-zinc-600 hover:text-[#166534] hover:bg-zinc-100 rounded-md transition inline-block">
                                    <i class="fas fa-sliders text-xs"></i>
                                </a>
                                <button wire:click="openEditModal({{ $sy->id }})" title="Edit Details" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                                @if ($sy->status !== 'active')
                                    <button wire:click="activateSchoolYear({{ $sy->id }})" title="Activate School Year" class="p-1.5 text-zinc-600 hover:text-emerald-700 hover:bg-zinc-100 rounded-md transition font-bold text-[10px]">
                                        <i class="fas fa-play text-xs text-emerald-600"></i>
                                    </button>
                                @endif
                                @if ($sy->status === 'active')
                                    <button wire:click="completeSchoolYear({{ $sy->id }})" title="Mark Completed" class="p-1.5 text-zinc-600 hover:text-blue-700 hover:bg-zinc-100 rounded-md transition">
                                        <i class="fas fa-circle-check text-xs text-blue-600"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-calendar-days text-2xl mb-2 text-zinc-300 block"></i>
                                No school years configured.
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
                        <span>Create School Year</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createSchoolYear" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">School Year (YYYY-YYYY) *</label>
                        <input type="text" wire:model.defer="school_year" required placeholder="e.g. 2026-2027" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        @error('school_year') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Start Date *</label>
                            <input type="date" wire:model.defer="start_date" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                            @error('start_date') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">End Date *</label>
                            <input type="date" wire:model.defer="end_date" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                            @error('end_date') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Operational Status</label>
                        <select wire:model.defer="status" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="draft">Draft (Setup Phase)</option>
                            <option value="active">Active (Set as Active School Year)</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 text-[11px] text-zinc-500">
                        <i class="fas fa-info-circle text-[#166534] mr-1"></i>
                        Creating a school year automatically initializes 3 Quarters (1st Quarter to 3rd Quarter).
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save School Year</button>
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
                        <span>Edit School Year</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateSchoolYear" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">School Year (YYYY-YYYY) *</label>
                        <input type="text" wire:model.defer="school_year" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        @error('school_year') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Start Date *</label>
                            <input type="date" wire:model.defer="start_date" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">End Date *</label>
                            <input type="date" wire:model.defer="end_date" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Operational Status</label>
                        <select wire:model.defer="status" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
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
