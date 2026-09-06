<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto font-sans">
    <!-- Header & Search Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 text-xs font-semibold mb-2">
                <span>🎓 Junior High School Directory</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                Student Directory & Performance Cards
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">
                Don Mariano Marcos National High School • Search students, view 3-quarter grades, and print Form 138 report cards.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Search LRN, ID or Name..." class="px-4 py-2.5 text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 w-56 focus:ring-2 focus:ring-sky-500">
            
            <select wire:model.live="gradeLevelId" class="px-3.5 py-2.5 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Grades (7-10)</option>
                @foreach ($gradeLevels as $gl)
                    <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($students as $st)
            @php
                $ga = $st->calculateGeneralAverage($currentSy?->id);
                $honor = $st->honor_standing;
            @endphp
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex flex-col justify-between space-y-5 hover:shadow-md hover:border-sky-300 dark:hover:border-sky-700 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300 border border-sky-200/80">
                            {{ $st->custom_student_id }}
                        </span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-2">
                            {{ $st->full_name }}
                        </h3>
                        <p class="text-xs text-zinc-500 font-medium">LRN: {{ $st->lrn }}</p>
                    </div>
                    @if ($honor)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200/80">
                            🌟 {{ $honor }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-3.5 rounded-2xl border border-zinc-100 dark:border-zinc-800 font-medium">
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold uppercase tracking-wider">Section</span>
                        <span class="font-bold text-zinc-800 dark:text-zinc-200 text-xs">{{ $st->section?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold uppercase tracking-wider">General Avg</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ $ga ? $ga.'%' : 'N/A' }}</span>
                    </div>
                </div>

                <button wire:click="showStudentDetail({{ $st->id }})" class="w-full py-2.5 bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/15 transition active:scale-[0.99]">
                    View Grade Card & Form 138 →
                </button>
            </div>
        @empty
            <div class="col-span-full p-12 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 text-zinc-400 font-medium">
                No students match your filter criteria.
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $students->links() }}
    </div>

    <!-- Student Academic Profile & Form 138 Modal -->
    @if ($selectedStudent)
        <div class="fixed inset-0 z-50 bg-black/70 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-4xl rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 sm:p-8 space-y-6 relative max-h-[90vh] overflow-y-auto">
                <button wire:click="closeStudentDetail" class="absolute top-5 right-5 size-9 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-white flex items-center justify-center font-bold text-sm transition">
                    ✕
                </button>

                <!-- Official Form 138 Header with DMMNHS Logo -->
                <div class="flex flex-col items-center text-center border-b border-zinc-100 dark:border-zinc-800 pb-5 space-y-2">
                    <x-app-logo class="scale-110 mb-1" />
                    <h2 class="text-xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                        DON MARIANO MARCOS NATIONAL HIGH SCHOOL
                    </h2>
                    <p class="text-xs text-zinc-500 font-medium">Republic of the Philippines • Department of Education</p>
                    <span class="inline-block text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950 px-4 py-1 rounded-full border border-sky-200 dark:border-sky-800">
                        OFFICIAL STUDENT ACADEMIC PROGRESS REPORT (FORM 138)
                    </span>
                </div>

                <!-- Student Summary Details -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 font-medium">
                    <div>
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold tracking-wider">Student Name</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $selectedStudent->full_name }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold tracking-wider">Student ID & LRN</span>
                        <span class="font-mono font-bold text-sky-600">{{ $selectedStudent->custom_student_id }}</span>
                        <span class="text-[10px] block text-zinc-500">LRN: {{ $selectedStudent->lrn }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold tracking-wider">Grade & Section</span>
                        <span class="font-bold text-zinc-800 dark:text-zinc-200 text-xs">{{ $selectedStudent->section?->name }}</span>
                        <span class="text-[10px] block text-zinc-500">Adviser: {{ $selectedStudent->section?->adviser?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[10px] uppercase font-bold tracking-wider">General Average</span>
                        <span class="font-black text-emerald-600 text-base">{{ $selectedStudent->calculateGeneralAverage($currentSy?->id) }}%</span>
                        @if ($selectedStudent->honor_standing)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                🌟 {{ $selectedStudent->honor_standing }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 3-Quarter Grades Table -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                        <span>📚 3-Quarter Subject Grade Sheet (SY {{ $currentSy?->year }})</span>
                        <span class="text-xs text-zinc-400 font-normal">Grading Standard: 75 Passing</span>
                    </h3>
                    <div class="overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-xs text-start">
                            <thead class="bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 uppercase text-[10px] font-bold tracking-wider">
                                <tr>
                                    <th class="py-3 px-4 text-start">Subject Code</th>
                                    <th class="py-3 px-4 text-start">Subject Name</th>
                                    <th class="py-3 px-4 text-center">Q1 Grade</th>
                                    <th class="py-3 px-4 text-center">Q2 Grade</th>
                                    <th class="py-3 px-4 text-center">Q3 Grade</th>
                                    <th class="py-3 px-4 text-center">Final Grade</th>
                                    <th class="py-3 px-4 text-end">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                @forelse ($selectedStudent->grades as $g)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                        <td class="py-3 px-4 font-mono font-bold text-sky-600">{{ $g->subject?->code }}</td>
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">{{ $g->subject?->name }}</td>
                                        <td class="py-3 px-4 text-center font-bold">{{ $g->q1 ?? '-' }}</td>
                                        <td class="py-3 px-4 text-center font-bold">{{ $g->q2 ?? '-' }}</td>
                                        <td class="py-3 px-4 text-center font-bold">{{ $g->q3 ?? '-' }}</td>
                                        <td class="py-3 px-4 text-center font-black text-zinc-900 dark:text-zinc-100 text-sm">{{ $g->final_grade ?? '-' }}</td>
                                        <td class="py-3 px-4 text-end">
                                            @if ($g->remarks === 'Passed')
                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">Passed</span>
                                            @elseif ($g->remarks === 'Needs Remediation')
                                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">Remediation</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-zinc-400 italic">No subject grades recorded for this student.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Achievements & Awards Section -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                        <span>🏆 Achievements & Best in Subject Recognitions</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @forelse ($selectedStudent->achievements as $ach)
                            <div class="p-3.5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/60 font-medium">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-xs">{{ $ach->title }}</span>
                                <span class="text-[10px] text-amber-700 dark:text-amber-300 block font-bold">{{ $ach->type }} ({{ $ach->quarter }})</span>
                                <p class="text-[11px] text-zinc-500 mt-1">{{ $ach->remarks }}</p>
                            </div>
                        @empty
                            <div class="col-span-full p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 text-center text-zinc-400 italic">No academic awards logged yet.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Printable Action Button -->
                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                    <button onclick="window.print()" class="py-2.5 px-6 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md transition">
                        <span>🖨️ Print Form 138 Report Card</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
