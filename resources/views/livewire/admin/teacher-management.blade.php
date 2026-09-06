<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chalkboard-user text-[#166534]"></i>
                <span>Teacher Management</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage faculty profiles, advisory sections, and subject workloads.</p>
        </div>

        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add Teacher</span>
        </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Employee Number, First Name, or Last Name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Year Level Filter --}}
            <select wire:model.live="filterYearLevelId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Year Levels</option>
                @foreach ($yearLevels as $yl)
                    <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                @endforeach
            </select>

            {{-- Account Status Filter --}}
            <select wire:model.live="filterStatus" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Teacher Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Employee Number</th>
                        <th class="py-3 px-4 text-start">Faculty Member</th>
                        <th class="py-3 px-4 text-start">Official Advisory Sections</th>
                        <th class="py-3 px-4 text-start">Assigned Subjects</th>
                        <th class="py-3 px-4 text-center">Account Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($teachers as $t)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#166534] dark:text-emerald-400">
                                {{ $t->employee_number }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $t->full_name }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                @forelse ($t->advisedSections as $adv)
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-200 mr-1 mb-1">
                                        {{ $adv->yearLevel?->name }} {{ $adv->section?->name }}
                                    </span>
                                @empty
                                    <span class="text-zinc-400 italic text-[11px]">No Advisory</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                @forelse ($t->sectionSubjects as $ss)
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#DBEAFE] text-blue-800 border border-blue-200 mr-1 mb-1">
                                        {{ $ss->subject?->subject_code }} ({{ $ss->schoolYearSection?->section?->name }})
                                    </span>
                                @empty
                                    <span class="text-zinc-400 italic text-[11px]">No Subject Load</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($t->user?->status === 'active')
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
                                <button wire:click="viewTeacherDetails({{ $t->id }})" title="View Details & Workloads" class="p-1.5 text-zinc-600 hover:text-[#166534] hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button wire:click="openEditModal({{ $t->id }})" title="Edit Profile" class="p-1.5 text-zinc-600 hover:text-blue-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                                <button wire:click="toggleStatus({{ $t->id }})" title="Toggle Account Status" class="p-1.5 text-zinc-600 hover:text-amber-600 hover:bg-zinc-100 rounded-md transition">
                                    <i class="fas fa-user-gear text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-chalkboard-user text-2xl mb-2 text-zinc-300 block"></i>
                                No faculty records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $teachers->links() }}
        </div>
    </div>

    <!-- CREATE TEACHER MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-user-plus text-[#166534]"></i>
                        <span>Add New Faculty Member</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createTeacher" class="space-y-4">
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
                            <input type="text" wire:model.defer="suffix" placeholder="Jr., LPT" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
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
                        Employee Number format (e.g. <code>EMP-0001</code>) and teacher login account will be generated automatically. Default password: <code>password</code>.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Create Faculty Account</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- EDIT TEACHER MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-pen-to-square text-[#166534]"></i>
                        <span>Edit Faculty Profile</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateTeacher" class="space-y-4">
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

                    <!-- Change Password (Optional) -->
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-key text-[#166534]"></i>
                                <span>Change Account Password (Optional)</span>
                            </span>
                            <span class="text-[10px] text-zinc-400">Leave blank to keep current</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">New Password</label>
                                <input type="password" wire:model.defer="new_password" placeholder="New password" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                                @error('new_password') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Confirm New Password</label>
                                <input type="password" wire:model.defer="new_password_confirmation" placeholder="Confirm password" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                            </div>
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

    <!-- VIEW TEACHER DETAILS MODAL -->
    @if ($showDetailModal && $selectedTeacher)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-2xl p-6 relative z-10 space-y-6 text-xs max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-[#DCFCE7] text-[#166534] flex items-center justify-center font-bold text-base">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedTeacher->full_name }}</h3>
                            <span class="font-mono text-[11px] text-[#166534] font-bold">{{ $selectedTeacher->employee_number }}</span>
                        </div>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Personal & Account Summary -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Faculty Contact</span>
                        <div class="space-y-1">
                            <div><span class="text-zinc-400">Employee ID:</span> <span class="font-mono font-bold text-[#166534]">{{ $selectedTeacher->employee_number }}</span></div>
                            <div><span class="text-zinc-400">Contact:</span> <span class="font-bold">{{ $selectedTeacher->contact_number ?: 'N/A' }}</span></div>
                        </div>
                    </div>

                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Account Credentials</span>
                        <div class="space-y-1">
                            <div><span class="text-zinc-400">Login ID:</span> <span class="font-mono font-bold text-[#166534]">{{ $selectedTeacher->user?->login_id }}</span></div>
                            <div><span class="text-zinc-400">Email:</span> <span class="font-bold">{{ $selectedTeacher->user?->email }}</span></div>
                            <div><span class="text-zinc-400">Status:</span> <span class="font-bold capitalize">{{ $selectedTeacher->user?->status }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Official Adviser Assignments -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-user-shield text-[#166534]"></i>
                        <span>Official Class Adviser Assignments (school_year_sections.adviser_id)</span>
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-xs text-start">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="py-2.5 px-3 text-start">School Year</th>
                                    <th class="py-2.5 px-3 text-start">Year Level</th>
                                    <th class="py-2.5 px-3 text-start">Section</th>
                                    <th class="py-2.5 px-3 text-end">Advisory Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                @forelse ($selectedTeacher->advisedSections as $adv)
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono font-bold text-zinc-900 dark:text-zinc-100">SY {{ $adv->schoolYear?->school_year }}</td>
                                        <td class="py-2.5 px-3">{{ $adv->yearLevel?->name }}</td>
                                        <td class="py-2.5 px-3 font-bold">{{ $adv->section?->name }}</td>
                                        <td class="py-2.5 px-3 text-end">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534] capitalize">
                                                {{ $adv->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-zinc-400 italic">No class advisory assignments assigned.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subject Teacher Assignments -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-book text-blue-600"></i>
                        <span>Subject Teacher Assignments (section_subjects.teacher_id)</span>
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-xs text-start">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="py-2.5 px-3 text-start">School Year</th>
                                    <th class="py-2.5 px-3 text-start">Year Level & Section</th>
                                    <th class="py-2.5 px-3 text-start">Subject Code & Name</th>
                                    <th class="py-2.5 px-3 text-end">Subject Load Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                @forelse ($selectedTeacher->sectionSubjects as $ss)
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono font-bold text-zinc-900 dark:text-zinc-100">SY {{ $ss->schoolYearSection?->schoolYear?->school_year }}</td>
                                        <td class="py-2.5 px-3">{{ $ss->schoolYearSection?->yearLevel?->name }} - {{ $ss->schoolYearSection?->section?->name }}</td>
                                        <td class="py-2.5 px-3 font-bold text-blue-700 dark:text-blue-400">{{ $ss->subject?->subject_code }} - {{ $ss->subject?->subject_name }}</td>
                                        <td class="py-2.5 px-3 text-end">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DBEAFE] text-blue-800 capitalize">
                                                Active Load
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-zinc-400 italic">No subject teacher loads assigned.</td>
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
