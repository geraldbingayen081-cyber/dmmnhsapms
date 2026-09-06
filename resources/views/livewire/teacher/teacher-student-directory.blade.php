<div class="space-y-6 font-sans">
    <!-- Header & Search Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-user-graduate text-[#166534]"></i>
                <span>Learner Directory & Academic Records</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">
                Students officially enrolled in your assigned subject classes or advisory section (SY {{ $activeSy?->school_year }}).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 text-xs">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, ID, or LRN..." class="w-full pl-8 pr-3 py-2 text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
            </div>

            <select wire:model.live="filterYearLevelId" class="px-3.5 py-2 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Year Levels</option>
                @foreach ($yearLevels as $yl)
                    <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($enrollments as $enr)
            @php
                $st = $enr->student;
                $sys = $enr->schoolYearSection;
                $grades = $enr->grades;
                $avg = $grades->count() > 0 ? $grades->avg('grade') : null;
                $minG = $grades->count() > 0 ? $grades->min('grade') : null;

                $honor = null;
                if ($avg >= 98.00 && $minG >= 85.00) $honor = 'With Highest Honors';
                elseif ($avg >= 95.00 && $minG >= 85.00) $honor = 'With High Honors';
                elseif ($avg >= 90.00 && $minG >= 85.00) $honor = 'With Honors';

                $parents = $st?->parents ?? collect();
            @endphp
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5 space-y-4 hover:border-emerald-400 dark:hover:border-emerald-600 transition flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-11 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 font-bold flex items-center justify-center text-sm border border-emerald-200 dark:border-emerald-800">
                                {{ substr($st?->first_name ?? 'S', 0, 1) }}{{ substr($st?->last_name ?? '', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                    {{ $st?->full_name }}
                                </h3>
                                <p class="text-[11px] font-mono text-[#166534] dark:text-emerald-400 font-bold">
                                    {{ $st?->student_number }}
                                </p>
                            </div>
                        </div>

                        @if ($honor)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200">
                                🌟 {{ $honor }}
                            </span>
                        @endif
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60 text-xs space-y-1 font-medium">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Class Section:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $sys?->yearLevel?->name }} - {{ $sys?->section?->name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Learner LRN:</span>
                            <span class="font-mono text-[11px] text-zinc-700 dark:text-zinc-300">{{ $st?->lrn ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>General Average:</span>
                            <span class="font-mono font-black {{ ($avg ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-zinc-400' }}">
                                {{ $avg ? number_format($avg, 2) : 'No grades' }}
                            </span>
                        </div>
                    </div>

                    <!-- Linked Parents Tag -->
                    <div class="text-[11px]">
                        <span class="text-zinc-400 font-bold block text-[10px] uppercase">Parent / Guardian:</span>
                        @if ($parents->isNotEmpty())
                            <span class="text-purple-700 dark:text-purple-300 font-medium">
                                <i class="fas fa-user-tag text-[9px] mr-1"></i>
                                {{ $parents->first()->full_name }} ({{ $parents->first()->pivot->relationship ?? 'Parent' }})
                            </span>
                        @else
                            <span class="text-zinc-400 italic">No parent linked yet</span>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button wire:click="showStudentDetail({{ $st->id }})" class="w-full py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-eye text-xs"></i>
                        <span>View Academic Card</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center text-zinc-400 italic bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                <i class="fas fa-user-graduate text-3xl mb-2 text-zinc-300 block"></i>
                No students found matching your search.
            </div>
        @endforelse
    </div>

    @if ($enrollments->hasPages())
        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            {{ $enrollments->links() }}
        </div>
    @endif

    <!-- STUDENT DETAIL CARD MODAL -->
    @if ($selectedEnrollment)
        @php
            $sStudent = $selectedEnrollment->student;
            $sSys = $selectedEnrollment->schoolYearSection;
            $sGrades = $selectedEnrollment->grades;
            $sAvg = $sGrades->count() > 0 ? $sGrades->avg('grade') : null;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm" wire:click="closeStudentDetail"></div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col relative z-10 text-xs">
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-id-card text-[#166534]"></i>
                        <span>Learner Academic Profile: {{ $sStudent->full_name }}</span>
                    </h3>
                    <button wire:click="closeStudentDetail" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto">
                    <!-- Learner Info -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl text-xs font-medium">
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Student ID:</span>
                            <span class="font-mono font-bold text-[#166534] dark:text-emerald-400">{{ $sStudent->student_number }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Learner LRN:</span>
                            <span class="font-mono font-bold">{{ $sStudent->lrn ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Current Section:</span>
                            <span class="font-bold">{{ $sSys?->yearLevel?->name }} - {{ $sSys?->section?->name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Gender:</span>
                            <span class="uppercase font-bold">{{ $sStudent->gender ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Class Adviser:</span>
                            <span class="font-bold">{{ $sSys?->adviser?->user?->name ?? 'None' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">General Average:</span>
                            <span class="font-black font-mono text-sm {{ ($sAvg ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-zinc-400' }}">
                                {{ $sAvg ? number_format($sAvg, 2) : '—' }}
                            </span>
                        </div>
                    </div>

                    <!-- Grades Table -->
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 mb-2">Subject Quarterly Ratings</h4>
                        <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-xl">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-zinc-100 dark:bg-zinc-800 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="py-2.5 px-3 text-start">Subject</th>
                                        <th class="py-2.5 px-3 text-center">Quarter</th>
                                        <th class="py-2.5 px-3 text-center">Rating</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                    @forelse ($sGrades as $gr)
                                        <tr>
                                            <td class="py-2 px-3 font-bold">
                                                {{ $gr->sectionSubject?->subject?->subject_name }}
                                                <span class="text-[10px] text-zinc-400 font-normal">({{ $gr->sectionSubject?->subject?->subject_code }})</span>
                                            </td>
                                            <td class="py-2 px-3 text-center">
                                                {{ $gr->quarter?->name }}
                                            </td>
                                            <td class="py-2 px-3 text-center font-mono font-bold {{ $gr->grade >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600' }}">
                                                {{ number_format($gr->grade, 2) }}
                                            </td>
                                            <td class="py-2 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $gr->grade >= 75 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $gr->grade >= 75 ? 'PASSED' : 'FAILED' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-4 text-center text-zinc-400 italic">No grade records yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                    <button wire:click="closeStudentDetail" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-700 dark:text-zinc-300 font-bold rounded-xl transition">
                        Close Profile
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
