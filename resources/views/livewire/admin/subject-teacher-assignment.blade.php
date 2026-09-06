<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-book text-[#166534]"></i>
                <span>Subject Teacher Load Assignment</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Assign faculty members to teach specific subjects per school year section instance.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <button wire:click="openAutoAssignModal" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span>Auto-Assign Subject Loads</span>
            </button>

            <button wire:click="openAssignModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-plus"></i>
                <span>Manual Assign Subject</span>
            </button>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">School Year:</label>
                <select wire:model.live="selectedSchoolYearId" class="px-3.5 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}">SY {{ $sy->school_year }} {{ $sy->status === 'active' ? '(Active)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Section Instance:</label>
                <select wire:model.live="selectedSchoolYearSectionId" class="px-3.5 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Sections</option>
                    @foreach ($sections as $sys)
                        <option value="{{ $sys->id }}">{{ $sys->yearLevel?->name }} - {{ $sys->section?->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Subject Teacher Loads Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Section Instance</th>
                        <th class="py-3 px-4 text-start">Subject Code</th>
                        <th class="py-3 px-4 text-start">Subject Name</th>
                        <th class="py-3 px-4 text-start">Assigned Subject Teacher</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($sectionSubjects as $ss)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $ss->schoolYearSection?->yearLevel?->name }} - {{ $ss->schoolYearSection?->section?->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded font-mono font-bold text-[11px] bg-[#DBEAFE] text-blue-800 border border-blue-200">
                                    {{ $ss->subject?->subject_code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $ss->subject?->subject_name }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($ss->teacher)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-[10px] text-[#166534] bg-emerald-50 dark:bg-emerald-950 px-1.5 py-0.5 rounded border border-emerald-200">
                                            {{ $ss->teacher->employee_number }}
                                        </span>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $ss->teacher->full_name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic text-[11px]">Unassigned Teacher</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                <button wire:click="openEditTeacherModal({{ $ss->id }})" title="Assign / Edit Teacher" class="px-3 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-lg transition text-[11px]">
                                    <i class="fas fa-user-pen mr-1"></i>
                                    {{ $ss->teacher ? 'Change Teacher' : 'Assign Teacher' }}
                                </button>
                                <button wire:click="removeSubjectLoad({{ $ss->id }})" title="Remove Subject Load" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-book text-2xl mb-2 text-zinc-300 block"></i>
                                No subject loads assigned for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ASSIGN SUBJECT LOAD MODAL -->
    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showAssignModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-book text-[#166534]"></i>
                        <span>Assign Subject Load</span>
                    </h3>
                    <button wire:click="$set('showAssignModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="assignSubjectTeacher" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target Section Instance *</label>
                        <select wire:model.defer="school_year_section_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Section Instance...</option>
                            @foreach ($sections as $sys)
                                <option value="{{ $sys->id }}">{{ $sys->yearLevel?->name }} - {{ $sys->section?->name }}</option>
                            @endforeach
                        </select>
                        @error('school_year_section_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Core Subject *</label>
                        <select wire:model.defer="subject_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Core Subject...</option>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->subject_code }} - {{ $sub->subject_name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Assigned Subject Teacher (Optional)</label>
                        <select wire:model.defer="teacher_id" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Leave Unassigned</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->employee_number }} - {{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showAssignModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Subject Load</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- AUTOMATIC SUBJECT & TEACHER LOAD ASSIGNMENT MODAL -->
    @if ($showAutoAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showAutoAssignModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-wand-magic-sparkles text-amber-600"></i>
                        <span>Auto-Assign Subject Loads Engine</span>
                    </h3>
                    <button wire:click="$set('showAutoAssignModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="processAutoAssign" class="space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/50 text-[11px] text-amber-900 dark:text-amber-200 space-y-1">
                        <span class="font-bold flex items-center gap-1.5 text-xs text-amber-800 dark:text-amber-300">
                            <i class="fas fa-circle-info"></i> Subject Workload Auto-Allocation
                        </span>
                        <p>Automatically attaches all DepEd Core Subjects (FIL, ENG, MAT, SCI, AP, ESP, MAPEH, TLE) to every section in the selected school year and distributes available faculty members evenly across unassigned loads.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year *</label>
                        <select wire:model.defer="autoAssignSchoolYearId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select School Year...</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}">SY {{ $sy->school_year }} ({{ ucfirst($sy->status) }})</option>
                            @endforeach
                        </select>
                        @error('autoAssignSchoolYearId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Auto-Assignment Scope</label>
                        <select wire:model.defer="autoAssignOption" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="all">Full Auto (Populate Core Subjects + Distribute Faculty Workloads)</option>
                            <option value="subjects_only">Populate Core Subjects Only (Keep Teachers Unassigned)</option>
                            <option value="teachers_only">Distribute Faculty Workloads Only (To Existing Subject Loads)</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showAutoAssignModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-bolt"></i>
                            <span>Run Auto-Assignment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
