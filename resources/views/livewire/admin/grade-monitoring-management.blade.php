<div class="space-y-6 font-sans">
    <!-- Header & Batch Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-file-lines text-[#166534]"></i>
                <span>Grade Monitoring & Review</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Monitor, review, edit, and approve quarterly subject grades across Junior High School sections.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
           <!-- <button wire:click="approveFilteredGrades" class="px-3.5 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-circle-check"></i>
                <span>Approve Pending Drafts</span>
            </button>-->
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 text-xs">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Student Number, First Name, or Last Name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
            <div>
                <select wire:model.live="filterSchoolYearId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All SY</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}">SY {{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterQuarterId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Quarters</option>
                    @foreach ($quarters as $q)
                        <option value="{{ $q->id }}">{{ $q->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterYearLevelId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Grades</option>
                    @foreach ($yearLevels as $yl)
                        <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterSectionId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Sections</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterSubjectId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Subjects</option>
                    @foreach ($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->subject_code }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterTeacherId" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Teachers</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterStatus" class="w-full px-2.5 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Statuses</option>
                    <option value="passed">Passed (Grade &ge; 75)</option>
                    <option value="failed">Failed (Grade &lt; 75)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Grade Monitoring Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Student</th>
                        <th class="py-3 px-4 text-start">Section</th>
                        <th class="py-3 px-4 text-start">Subject</th>
                        <th class="py-3 px-4 text-start">Teacher</th>
                        <th class="py-3 px-4 text-center">Quarter</th>
                        <th class="py-3 px-4 text-center">Grade Rating</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($grades as $g)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $g->studentEnrollment?->student?->full_name }}</span>
                                <span class="font-mono text-[10px] text-[#166534] block">{{ $g->studentEnrollment?->student?->student_number }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                {{ $g->studentEnrollment?->schoolYearSection?->yearLevel?->name }} - {{ $g->studentEnrollment?->schoolYearSection?->section?->name }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-blue-700 dark:text-blue-400">
                                {{ $g->sectionSubject?->subject?->subject_code }} - {{ $g->sectionSubject?->subject?->subject_name }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                {{ $g->sectionSubject?->teacher?->full_name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold">
                                {{ $g->quarter?->short_name }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-3 py-1 rounded-lg font-mono font-bold text-xs {{ $g->grade >= 75 ? 'bg-[#DCFCE7] text-[#166534] border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ number_format($g->grade, 2) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($g->grade >= 75.00)
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-black bg-[#DCFCE7] text-[#166534] border border-emerald-300 uppercase inline-flex items-center gap-1">
                                        <i class="fas fa-circle-check text-[9px]"></i> Passed
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-300 uppercase inline-flex items-center gap-1">
                                        <i class="fas fa-circle-xmark text-[9px]"></i> Failed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1 whitespace-nowrap">
                                <button wire:click="openEditGradeModal({{ $g->id }})" title="Edit Grade Rating" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-md transition text-[10px] cursor-pointer">
                                    <i class="fas fa-pen mr-1"></i>Edit
                                </button>

                                @if ($g->status === 'draft')
                                    <button wire:click="approveGrade({{ $g->id }})" title="Approve Grade" class="px-2.5 py-1 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-md transition text-[10px] cursor-pointer">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-file-lines text-2xl mb-2 text-zinc-300 block"></i>
                                No subject grades found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $grades->links() }}
        </div>
    </div>

    <!-- ADMIN EDIT STUDENT GRADE MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-pen-to-square text-amber-600"></i>
                        <span>Edit Student Grade Rating</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="updateGrade" class="space-y-4">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/60 space-y-1">
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs">{{ $editStudentName }}</div>
                        <div class="text-[11px] text-zinc-500 font-mono">{{ $editStudentNumber }} • Subject: <span class="font-bold text-blue-600">{{ $editSubjectCode }} ({{ $editSubjectName }})</span></div>
                        <div class="text-[11px] text-zinc-500 font-medium">Evaluation Quarter: <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $editQuarterName }}</span></div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Quarter Grade Rating (60.00 - 99.00) *</label>
                        <input type="number" step="0.01" min="60" max="99" wire:model.live.debounce.150ms="editGradeValue" required class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                        @error('editGradeValue') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Auto-Generated Status (DepEd Standard &ge; 75)</label>
                        <div class="p-2.5 rounded-lg border text-xs font-bold flex items-center justify-between {{ $editRemarks === 'PASSED' ? 'bg-[#DCFCE7] text-[#166534] border-emerald-300' : ($editRemarks === 'FAILED' ? 'bg-rose-50 text-rose-700 border-rose-300' : 'bg-zinc-100 text-zinc-500 border-zinc-300') }}">
                            <span class="flex items-center gap-1.5 uppercase font-black">
                                <i class="fas {{ $editRemarks === 'PASSED' ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-rose-600' }}"></i>
                                <span>{{ $editRemarks === 'PASSED' ? 'Passed' : ($editRemarks === 'FAILED' ? 'Failed' : 'Awaiting Grade Input...') }}</span>
                            </span>
                            <span class="text-[10px] font-normal text-zinc-500">(Passing Threshold: 75.00)</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Review Approval Status *</label>
                        <select wire:model.defer="editStatus" required class="w-full px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="approved">Approved (Official Grade)</option>
                            <option value="draft">Draft (Pending Review)</option>
                        </select>
                        @error('editStatus') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-check"></i>
                            <span>Save Grade Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
