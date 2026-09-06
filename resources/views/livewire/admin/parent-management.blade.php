<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-user-group text-[#166534]"></i>
                <span>Parent Management & Student Linker</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage parent master accounts and link students to parent/guardian profiles.</p>
        </div>

        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add Parent Account</span>
        </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Parent ID, First Name, or Last Name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div>
            <select wire:model.live="filterStatus" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Parent Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Parent ID</th>
                        <th class="py-3 px-4 text-start">Parent / Guardian Name</th>
                        <th class="py-3 px-4 text-start">Contact Number</th>
                        <th class="py-3 px-4 text-start">Linked Student Children</th>
                        <th class="py-3 px-4 text-center">Account Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($parents as $p)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#166534] dark:text-emerald-400">
                                {{ $p->user?->login_id }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $p->full_name }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                {{ $p->contact_number ?: 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                @forelse ($p->students as $child)
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-200 mr-1 mb-1">
                                        {{ $child->full_name }} ({{ $child->pivot->relationship }})
                                    </span>
                                @empty
                                    <span class="text-zinc-400 italic text-[11px]">No Linked Children</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($p->user?->status === 'active')
                                    <span class="px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-[10px] border border-emerald-200">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-zinc-100 text-zinc-600 font-bold text-[10px] border border-zinc-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                <button wire:click="viewParentDetails({{ $p->id }})" title="View Details" class="p-1.5 text-zinc-600 hover:text-[#166534] hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button wire:click="openLinkModal({{ $p->id }})" title="Link Student Child" class="p-1.5 text-zinc-600 hover:text-emerald-700 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-link text-xs"></i>
                                </button>
                                <button wire:click="openEditModal({{ $p->id }})" title="Edit Profile" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                                <button wire:click="toggleStatus({{ $p->id }})" title="Toggle Account Status" class="p-1.5 text-zinc-600 hover:text-amber-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-user-gear text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-user-group text-2xl mb-2 text-zinc-300 block"></i>
                                No parent accounts found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $parents->links() }}
        </div>
    </div>

    <!-- CREATE PARENT MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-user-plus text-[#166534]"></i>
                        <span>Add New Parent Account</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createParent" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">First Name *</label>
                            <input type="text" wire:model.defer="first_name" required placeholder="First Name" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Middle Name</label>
                            <input type="text" wire:model.defer="middle_name" placeholder="Middle Name" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Last Name *</label>
                            <input type="text" wire:model.defer="last_name" required placeholder="Last Name" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Suffix</label>
                            <input type="text" wire:model.defer="suffix" placeholder="Sr." class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Contact Number</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold border border-r-0 border-zinc-300 dark:border-zinc-700 rounded-l-lg text-xs">+63</span>
                            <input type="text" wire:model.defer="contact_number" maxlength="10" placeholder="9XXXXXXXXX" class="w-full px-3 py-2 text-xs rounded-r-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>
                        @error('contact_number') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 text-[11px] text-zinc-500">
                        <i class="fas fa-info-circle text-[#166534] mr-1"></i>
                        Parent ID format (e.g. <code>PARENT-0001</code>) and parent login account will be generated automatically. Default password: <code>password</code>.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Create Parent Account</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- EDIT PARENT MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-pen-to-square text-[#166534]"></i>
                        <span>Edit Parent Profile</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateParent" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">First Name *</label>
                            <input type="text" wire:model.defer="first_name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Middle Name</label>
                            <input type="text" wire:model.defer="middle_name" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Last Name *</label>
                            <input type="text" wire:model.defer="last_name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Suffix</label>
                            <input type="text" wire:model.defer="suffix" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Contact Number</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold border border-r-0 border-zinc-300 dark:border-zinc-700 rounded-l-lg text-xs">+63</span>
                            <input type="text" wire:model.defer="contact_number" maxlength="10" placeholder="9XXXXXXXXX" class="w-full px-3 py-2 text-xs rounded-r-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                        </div>
                        @error('contact_number') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- LINK CHILD MODAL -->
    @if ($showLinkModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showLinkModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-link text-[#166534]"></i>
                        <span>Link Student Child to Parent</span>
                    </h3>
                    <button wire:click="$set('showLinkModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="linkChild" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Search & Select Student</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Type student name or student number..." class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 mb-2">
                        
                        <select wire:model.defer="selectedStudentId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Student Child...</option>
                            @foreach ($availableStudents as $st)
                                <option value="{{ $st->id }}">{{ $st->student_number }} - {{ $st->full_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedStudentId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Family Relationship</label>
                        <select wire:model.defer="relationship" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="Mother">Mother</option>
                            <option value="Father">Father</option>
                            <option value="Guardian">Guardian</option>
                            <option value="Grandparent">Grandparent</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showLinkModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Link Student Child</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- VIEW PARENT DETAILS MODAL -->
    @if ($showDetailModal && $selectedParent)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-xl p-6 relative z-10 space-y-6 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-[#DCFCE7] text-[#166534] flex items-center justify-center font-bold text-base">
                            <i class="fas fa-user-group"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedParent->full_name }}</h3>
                            <span class="font-mono text-[11px] text-[#166534] font-bold">{{ $selectedParent->user?->login_id }}</span>
                        </div>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Parent Account Info</span>
                    <div class="space-y-1">
                        <div><span class="text-zinc-400">Login ID:</span> <span class="font-mono font-bold text-[#166534]">{{ $selectedParent->user?->login_id }}</span></div>
                        <div><span class="text-zinc-400">Contact Number:</span> <span class="font-bold">{{ $selectedParent->contact_number ?: 'N/A' }}</span></div>
                        <div><span class="text-zinc-400">Status:</span> <span class="font-bold capitalize">{{ $selectedParent->user?->status }}</span></div>
                    </div>
                </div>

                <!-- Linked Children List -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-children text-[#166534]"></i>
                        <span>Linked Student Children</span>
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-xs text-start">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="py-2.5 px-3 text-start">Student Number</th>
                                    <th class="py-2.5 px-3 text-start">Child Name</th>
                                    <th class="py-2.5 px-3 text-start">Relationship</th>
                                    <th class="py-2.5 px-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                @forelse ($selectedParent->students as $ch)
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono font-bold text-[#166534]">{{ $ch->student_number }}</td>
                                        <td class="py-2.5 px-3 font-bold text-zinc-900 dark:text-zinc-100">{{ $ch->full_name }}</td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                                                {{ $ch->pivot->relationship }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-end">
                                            <button wire:click="unlinkChild({{ $selectedParent->id }}, {{ $ch->id }})" title="Unlink Student" class="p-1 text-rose-600 hover:bg-rose-50 rounded transition font-bold text-[11px]">
                                                <i class="fas fa-link-slash mr-1"></i>Unlink
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-zinc-400 italic">No student children linked to this parent account.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" wire:click="$set('showDetailModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg bg-zinc-800 text-white">Close Details</button>
                </div>
            </div>
        </div>
    @endif
</div>
