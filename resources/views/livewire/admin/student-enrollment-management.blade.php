<div class="space-y-6 font-sans">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-clipboard-user text-[#166534]"></i>
                <span>Student Enrollment & Academic Transitions</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage section placement history, student promotions, retentions, and transfers.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <button wire:click="openBatchTransitionModal" class="px-4 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-graduation-cap"></i>
                <span>Batch Auto-Promote / Retain</span>
            </button>

            <!--<button wire:click="openAutoEnrollModal" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span>Auto-Enroll Unenrolled</span>
            </button>-->

            <button wire:click="openEnrollModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-plus"></i>
                <span>Manual Enroll</span>
            </button>
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by Student Number, First Name, or Last Name..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div>
                <select wire:model.live="filterSchoolYearId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}">SY {{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterYearLevelId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Year Levels</option>
                    @foreach ($yearLevels as $yl)
                        <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterSectionId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Sections</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="filterStatus" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Placement Statuses</option>
                    <option value="enrolled">Enrolled</option>
                    <option value="promoted">Promoted</option>
                    <option value="retained">Retained</option>
                    <option value="transferred">Transferred</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Enrollment Data Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Student Number</th>
                        <th class="py-3 px-4 text-start">Student Name</th>
                        <th class="py-3 px-4 text-start">School Year</th>
                        <th class="py-3 px-4 text-start">Year Level</th>
                        <th class="py-3 px-4 text-start">Section</th>
                        <th class="py-3 px-4 text-start">Enrollment Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-end">Academic Transitions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($enrollments as $enr)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#166534] dark:text-emerald-400">
                                {{ $enr->student?->student_number }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $enr->student?->full_name }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                SY {{ $enr->schoolYearSection?->schoolYear?->school_year }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                {{ $enr->schoolYearSection?->yearLevel?->name }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $enr->schoolYearSection?->section?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-500">
                                {{ \Carbon\Carbon::parse($enr->enrollment_date)->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($enr->status === 'enrolled')
                                    <span class="px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-[10px] border border-emerald-200 uppercase">
                                        Enrolled
                                    </span>
                                @elseif ($enr->status === 'promoted')
                                    <span class="px-2.5 py-1 rounded-md bg-[#DBEAFE] text-blue-800 font-bold text-[10px] border border-blue-200 uppercase">
                                        Promoted
                                    </span>
                                @elseif ($enr->status === 'retained')
                                    <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-bold text-[10px] border border-amber-200 uppercase">
                                        Retained
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 font-bold text-[10px] border border-rose-200 uppercase">
                                        Transferred
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                @if ($enr->status === 'enrolled')
                                    <button wire:click="openTransitionModal({{ $enr->id }}, 'promote')" title="Promote Student to Next Grade" class="px-2.5 py-1 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-md transition text-[10px]">
                                        <i class="fas fa-graduation-cap mr-1"></i>Promote
                                    </button>
                                    <button wire:click="openTransitionModal({{ $enr->id }}, 'retain')" title="Retain Student in Grade Level" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-md transition text-[10px]">
                                        <i class="fas fa-rotate-right mr-1"></i>Retain
                                    </button>
                                    <button wire:click="openTransitionModal({{ $enr->id }}, 'transfer')" title="Mark Transferred Out" class="px-2.5 py-1 bg-zinc-600 hover:bg-zinc-700 text-white font-bold rounded-md transition text-[10px]">
                                        <i class="fas fa-right-from-bracket mr-1"></i>Transfer
                                    </button>
                                @else
                                    <span class="text-zinc-400 italic text-[11px]">Transition Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-clipboard-user text-2xl mb-2 text-zinc-300 block"></i>
                                No enrollment records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $enrollments->links() }}
        </div>
    </div>

    <!-- ENROLL STUDENT MODAL -->
    @if ($showEnrollModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showEnrollModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-lg p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-plus text-[#166534]"></i>
                        <span>Enroll Student in Section Instance</span>
                    </h3>
                    <button wire:click="$set('showEnrollModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="enrollStudent" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Search & Select Student *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Type student name or student number..." class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 mb-2">
                        
                        <select wire:model.defer="student_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Choose Student...</option>
                            @foreach ($availableStudents as $st)
                                <option value="{{ $st->id }}">{{ $st->student_number }} - {{ $st->full_name }}</option>
                            @endforeach
                        </select>
                        @error('student_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year Section Instance *</label>
                        <select wire:model.defer="school_year_section_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Section Instance...</option>
                            @foreach ($availableSectionInstances as $sys)
                                <option value="{{ $sys->id }}">SY {{ $sys->schoolYear?->school_year }} | {{ $sys->yearLevel?->name }} - {{ $sys->section?->name }}</option>
                            @endforeach
                        </select>
                        @error('school_year_section_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Enrollment Date *</label>
                        <input type="date" wire:model.defer="enrollment_date" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showEnrollModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ACADEMIC TRANSITION MODAL (PROMOTED / RETAINED / TRANSFERRED) -->
    @if ($showTransitionModal && $selectedEnrollment)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showTransitionModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-arrows-split-up-and-left text-[#166534]"></i>
                        <span>Process Academic Transition: <span class="uppercase font-mono text-[#166534]">{{ $transitionType }}</span></span>
                    </h3>
                    <button wire:click="$set('showTransitionModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="processTransition" class="space-y-4">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Current Enrollment</span>
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                            {{ $selectedEnrollment->student?->full_name }} ({{ $selectedEnrollment->student?->student_number }})
                        </div>
                        <div class="text-[11px] text-zinc-500">
                            SY {{ $selectedEnrollment->schoolYearSection?->schoolYear?->school_year }} | {{ $selectedEnrollment->schoolYearSection?->yearLevel?->name }} - {{ $selectedEnrollment->schoolYearSection?->section?->name }}
                        </div>
                    </div>

                    @if ($studentEval)
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Academic Evaluation</span>
                                @if (!$studentEval['has_grades'])
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">No Grades Recorded</span>
                                @elseif ($studentEval['is_passed'])
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Eligible for Promotion</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Needs Retention</span>
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-zinc-500 block text-[10px]">General Average:</span>
                                    <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                        {{ $studentEval['general_average'] ? number_format($studentEval['general_average'], 2) : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block text-[10px]">Failing Subjects (< 75):</span>
                                    <span class="font-mono font-bold text-xs {{ $studentEval['failed_subjects_count'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                        {{ $studentEval['failed_subjects_count'] }} subject(s)
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($transitionType !== 'transfer')
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year & Section Instance *</label>
                            <select wire:model.defer="target_school_year_section_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                <option value="">Select Target Section Instance...</option>
                                @foreach ($availableSectionInstances as $sys)
                                    <option value="{{ $sys->id }}">SY {{ $sys->schoolYear?->school_year }} | {{ $sys->yearLevel?->name }} - {{ $sys->section?->name }}</option>
                                @endforeach
                            </select>
                            @error('target_school_year_section_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 text-[11px] text-[#166534] dark:text-emerald-300 font-medium">
                        <i class="fas fa-shield-halved mr-1 font-bold"></i>
                        Historical record guarantee: Previous enrollment records and past grades are preserved under SY {{ $selectedEnrollment->schoolYearSection?->schoolYear?->school_year }} without overwriting or grade copying.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showTransitionModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Confirm {{ ucfirst($transitionType) }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- AUTOMATIC RANDOM AUTO-ENROLL MODAL -->
    @if ($showAutoEnrollModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showAutoEnrollModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-wand-magic-sparkles text-amber-600"></i>
                        <span>Automatic Random Auto-Enrollment</span>
                    </h3>
                    <button wire:click="$set('showAutoEnrollModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="processAutoEnroll" class="space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/50 text-[11px] text-amber-900 dark:text-amber-200 space-y-1">
                        <span class="font-bold flex items-center gap-1.5 text-xs text-amber-800 dark:text-amber-300">
                            <i class="fas fa-circle-info"></i> Auto-Enrollment Batch Engine
                        </span>
                        <p>This tool automatically assigns all registered students who do not currently have section placement into active section loads for the chosen school year.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year *</label>
                        <select wire:model.defer="autoEnrollSchoolYearId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select School Year...</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}">SY {{ $sy->school_year }} ({{ ucfirst($sy->status) }})</option>
                            @endforeach
                        </select>
                        @error('autoEnrollSchoolYearId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target Year Level Filter</label>
                        <select wire:model.defer="autoEnrollYearLevelId" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="all">All Year Levels (Distribute across active Grade 7 - 10 sections)</option>
                            @foreach ($yearLevels as $yl)
                                <option value="{{ $yl->id }}">{{ $yl->name }} Sections Only</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showAutoEnrollModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-bolt"></i>
                            <span>Run Auto-Enrollment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- BATCH AUTOMATIC PROMOTION & RETENTION MODAL -->
    @if ($showBatchTransitionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showBatchTransitionModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-blue-700"></i>
                        <span>Batch Auto-Promote / Retain Engine</span>
                    </h3>
                    <button wire:click="$set('showBatchTransitionModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="processBatchTransition" class="space-y-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800/50 text-[11px] text-blue-900 dark:text-blue-200 space-y-1">
                        <span class="font-bold flex items-center gap-1.5 text-xs text-blue-800 dark:text-blue-300">
                            <i class="fas fa-shield-halved"></i> DepEd Academic Evaluation Rule Engine
                        </span>
                        <p>Evaluates all enrolled students in the Source SY: Passing students (Gen. Avg &ge; 75 &amp; &lt; 3 failing subjects) are promoted to the next grade level. Failing students are retained in the same grade level.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Source School Year *</label>
                        <select wire:model.defer="batchSourceSchoolYearId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Source SY...</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}">SY {{ $sy->school_year }} ({{ ucfirst($sy->status) }})</option>
                            @endforeach
                        </select>
                        @error('batchSourceSchoolYearId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year (Next Academic Year) *</label>
                        <select wire:model.defer="batchTargetSchoolYearId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Target SY...</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}">SY {{ $sy->school_year }} ({{ ucfirst($sy->status) }})</option>
                            @endforeach
                        </select>
                        @error('batchTargetSchoolYearId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showBatchTransitionModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-blue-700 text-white hover:bg-blue-800 shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-play"></i>
                            <span>Run Batch Auto-Promotion</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
