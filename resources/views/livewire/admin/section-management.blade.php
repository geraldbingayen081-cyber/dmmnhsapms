<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-users-rectangle text-[#166534]"></i>
                <span>Sections</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage reusable section names (e.g. Rizal, Bonifacio, Mabini, Luna).</p>
        </div>

        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add Section Name</span>
        </button>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm text-xs">
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search section name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>
    </div>

    <!-- Sections Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Section Name</th>
                        <th class="py-3 px-4 text-center">Total Students</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($sections as $sec)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $sec->name }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded font-bold text-[11px] bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                    {{ $sec->student_enrollments_count }} {{ Str::plural('Student', $sec->student_enrollments_count) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-end">
                                <button wire:click="openEditModal({{ $sec->id }})" title="Edit Section Name" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-users-rectangle text-2xl mb-2 text-zinc-300 block"></i>
                                No sections created yet.
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
                        <span>Add Section</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createSection" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Section Name *</label>
                        <input type="text" wire:model.defer="name" required placeholder="e.g. Rizal, Bonifacio, Mabini, Luna" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        @error('name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 text-[11px] text-zinc-500">
                        <i class="fas fa-info-circle text-[#166534] mr-1"></i>
                        Section names are reusable containers. Specific school year assignments (e.g. Grade 7 Rizal 2026-2027) are configured under School Year setup.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Section Name</button>
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
                        <span>Edit Section</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateSection" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Section Name *</label>
                        <input type="text" wire:model.defer="name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        @error('name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
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
