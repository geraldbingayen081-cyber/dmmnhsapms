<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-user-group text-[#166534]"></i>
                <span>Parent Accounts & Student Linker</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">
                Create verified parent portal accounts and link them directly to their enrolled children.
            </p>
        </div>

        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Create Parent Account</span>
        </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Parent ID, Name, or Child Name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="filterScope" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="advisees">My Advisees' Parents</option>
                <option value="all">All School Parents</option>
            </select>
        </div>
    </div>

    <!-- Parent Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Parent Login ID</th>
                        <th class="py-3 px-4 text-start">Parent / Guardian Name</th>
                        <th class="py-3 px-4 text-start">Contact Number</th>
                        <th class="py-3 px-4 text-start">Linked Child / Children</th>
                        <th class="py-3 px-4 text-center">Account Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($parents as $p)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#166534] dark:text-emerald-400">
                                {{ $p->user?->login_id ?? 'No Login ID' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $p->full_name }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400 font-mono">
                                {{ $p->contact_number ?? 'Not provided' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @forelse ($p->students as $child)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                            <i class="fas fa-child text-[9px]"></i>
                                            <span>{{ $child->full_name }} ({{ $child->pivot->relationship ?? 'Child' }})</span>
                                            <button wire:click="unlinkChild({{ $p->id }}, {{ $child->id }})" wire:confirm="Are you sure you want to unlink this student from this parent?" title="Unlink Student" class="text-purple-400 hover:text-rose-600 ml-1">
                                                <i class="fas fa-times text-[9px]"></i>
                                            </button>
                                        </span>
                                    @empty
                                        <span class="text-zinc-400 italic text-[11px]">No children linked yet</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $p->user?->status === 'active' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $p->user?->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-end">
                                <div class="inline-flex items-center gap-1.5">
                                    <button wire:click="openLinkModal({{ $p->id }})" title="Link Child to Parent" class="px-2.5 py-1 text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 rounded-lg hover:bg-purple-100 transition inline-flex items-center gap-1">
                                        <i class="fas fa-link text-[10px]"></i>
                                        <span>Link Child</span>
                                    </button>

                                    <button wire:click="openEditModal({{ $p->id }})" title="Edit Parent Profile" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </button>

                                    <button wire:click="viewParentDetails({{ $p->id }})" title="View Details" class="p-1.5 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-400 italic">
                                <i class="fas fa-user-group text-3xl mb-2 text-zinc-300 block"></i>
                                No parent accounts found matching your query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($parents->hasPages())
            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $parents->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE PARENT MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-user-plus text-[#166534]"></i>
                        <span>Register New Parent Account</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createParent" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">First Name *</label>
                            <input type="text" wire:model.defer="first_name" required placeholder="e.g. Maria" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            @error('first_name') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Middle Name</label>
                            <input type="text" wire:model.defer="middle_name" placeholder="Optional" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Last Name *</label>
                            <input type="text" wire:model.defer="last_name" required placeholder="e.g. Dela Cruz" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            @error('last_name') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Suffix</label>
                            <input type="text" wire:model.defer="suffix" placeholder="Jr., Sr., III" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Mobile Contact Number (+63)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400 font-mono text-xs">+63</span>
                            <input type="text" wire:model.defer="contact_number" maxlength="10" placeholder="9171234567" class="w-full pl-12 pr-3 py-2 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        @error('contact_number') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 rounded-xl border border-emerald-200 dark:border-emerald-800 text-[11px] text-[#166534] dark:text-emerald-300">
                        <i class="fas fa-shield-halved mr-1"></i>
                        A parent login ID (e.g. <code>PAR-2026-XXXX</code>) and default password (<code>password</code>) will be generated automatically.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save & Generate Account</button>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Contact Number (+63)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400 font-mono text-xs">+63</span>
                            <input type="text" wire:model.defer="contact_number" maxlength="10" class="w-full pl-12 pr-3 py-2 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
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
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-4 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-link text-purple-600"></i>
                        <span>Link Student Child to Parent</span>
                    </h3>
                    <button wire:click="$set('showLinkModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="linkChild" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Search Student Learner</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Type name or student number..." class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 mb-2">

                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Student *</label>
                        <select wire:model.defer="selectedStudentId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Student Learner --</option>
                            @foreach ($availableStudents as $st)
                                <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->student_number }})</option>
                            @endforeach
                        </select>
                        @error('selectedStudentId') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Relationship to Child *</label>
                        <select wire:model.defer="relationship" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="Mother">Mother</option>
                            <option value="Father">Father</option>
                            <option value="Guardian">Guardian</option>
                            <option value="Grandmother">Grandmother</option>
                            <option value="Grandfather">Grandfather</option>
                            <option value="Aunt">Aunt</option>
                            <option value="Uncle">Uncle</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showLinkModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-purple-700 text-white hover:bg-purple-800 shadow-sm">Link Student</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- DETAIL MODAL -->
    @if ($showDetailModal && $selectedParent)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-4 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-id-card text-[#166534]"></i>
                        <span>Parent Profile Card</span>
                    </h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl space-y-1">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Login Credentials</span>
                        <p class="font-mono font-bold text-sm text-[#166534] dark:text-emerald-400">{{ $selectedParent->user?->login_id }}</p>
                        <p class="text-zinc-500 font-mono text-[11px]">{{ $selectedParent->user?->email }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase block">Linked Student Children</span>
                        @forelse ($selectedParent->students as $ch)
                            <div class="p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $ch->full_name }}</span>
                                    <span class="text-[10px] text-zinc-400 font-mono">{{ $ch->student_number }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                                    {{ $ch->pivot->relationship ?? 'Child' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-zinc-400 italic">No students linked to this account yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" wire:click="$set('showDetailModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
