<div class="space-y-6 font-sans">
    <!-- Header Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold mb-2">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>DepEd Junior High School • Permanent Academic History</span>
                </div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span>Student Academic History (Grade 7 – Grade 10)</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Multi-year permanent academic performance and consolidated term ratings across all enrolled grade levels.
                </p>
            </div>

            <!-- Quick Stats Pill -->
            <div class="flex items-center gap-2 sm:self-start">
                <div class="px-3 py-1.5 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-end">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Available Levels</span>
                    <span class="text-xs font-black text-[#166534] dark:text-emerald-400 font-mono">
                        {{ $summary['total_available_years'] ?? 0 }} of 4 JHS Levels
                    </span>
                </div>
                @if (!empty($summary['overall_gwa']))
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-end">
                        <span class="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400 block">Cumulative GWA</span>
                        <span class="text-xs font-black text-[#166534] dark:text-emerald-300 font-mono">
                            {{ number_format($summary['overall_gwa'], 2) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Student Meta Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Learner Name</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block truncate">{{ $student?->full_name ?? auth()->user()->name }}</span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Student LRN / ID</span>
                <span class="font-mono font-bold text-[#166534] dark:text-emerald-400 text-sm block">{{ $student?->student_number ?? auth()->user()->login_id }}</span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Total Subjects Passed</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block">
                    {{ $summary['all_passed_count'] ?? 0 }} Learning Area(s)
                </span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Curriculum Track</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block truncate">DepEd K to 12 JHS</span>
            </div>
        </div>
    </div>

    <!-- JHS Progression Tabs (Grade 7 through Grade 10) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-graduation-cap text-[#166534] dark:text-emerald-400"></i>
                <span>Junior High School Progression (Grades 7 to 10)</span>
            </span>
            <span class="text-[11px] text-zinc-400">Select a grade level to review historical marks</span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach (['G7' => 'Grade 7', 'G8' => 'Grade 8', 'G9' => 'Grade 9', 'G10' => 'Grade 10'] as $code => $label)
                @php
                    $ylData = $yearLevels[$code] ?? null;
                    $hasRec = $ylData && $ylData['has_records'];
                    $isSelected = $selectedGradeLevel === $code;
                    $primaryRec = $hasRec ? ($ylData['primary_record'] ?? null) : null;
                @endphp

                <button
                    type="button"
                    wire:click="selectGradeLevel('{{ $code }}')"
                    class="p-4 rounded-2xl border text-start transition-all relative overflow-hidden cursor-pointer {{ $isSelected ? 'bg-white dark:bg-zinc-900 border-[#166534] dark:border-emerald-500 shadow-md ring-2 ring-emerald-600/20' : ($hasRec ? 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-emerald-300 dark:hover:border-emerald-700 shadow-sm' : 'bg-zinc-100/70 dark:bg-zinc-900/40 border-dashed border-zinc-300 dark:border-zinc-800 opacity-80') }}"
                >
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-black {{ $isSelected ? 'text-[#166534] dark:text-emerald-400' : 'text-zinc-900 dark:text-zinc-100' }}">
                            {{ $label }}
                        </span>

                        @if ($hasRec)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                <i class="fas fa-check-circle text-[9px]"></i>
                                <span>Available</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-200/80 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                <i class="fas fa-circle-minus text-[9px]"></i>
                                <span>Unavailable</span>
                            </span>
                        @endif
                    </div>

                    @if ($hasRec && $primaryRec)
                        <div class="space-y-0.5 text-xs">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300 block truncate">
                                SY {{ $primaryRec['school_year'] }} • {{ $primaryRec['section_name'] }}
                            </span>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-[10px] text-zinc-400 font-mono">
                                    {{ $primaryRec['total_subjects'] }} Subjects
                                </span>
                                @if ($primaryRec['gwa'] !== null)
                                    <span class="text-xs font-black font-mono {{ $primaryRec['gwa'] >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                                        GWA: {{ number_format($primaryRec['gwa'], 2) }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="space-y-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                            <span class="text-[11px] block">No enrolled records</span>
                            <span class="text-[10px] block italic">Records will show once reached</span>
                        </div>
                    @endif

                    @if ($isSelected)
                        <div class="absolute bottom-0 inset-x-0 h-1 bg-[#166534] dark:bg-emerald-500"></div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Active Grade Level Record Detail -->
    @if ($selectedData && $selectedData['has_records'])
        @foreach ($selectedData['records'] as $recordIndex => $rec)
            <div class="space-y-4">
                <!-- Record Header Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#166534] text-white">
                                    {{ $rec['year_level_name'] }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    School Year {{ $rec['school_year'] }}
                                </span>
                                @if ($rec['is_active_sy'])
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200">
                                        Current Active School Year
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        Previous Completed Year
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 pt-1">
                                Section {{ $rec['section_name'] }} • Adviser: {{ $rec['adviser_name'] }}
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Official grades retrieved from permanent institutional records for {{ $rec['year_level_name'] }}.
                            </p>
                        </div>

                        <!-- GWA & Remarks Badge -->
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/80 rounded-xl border border-zinc-200 dark:border-zinc-700/80 text-center min-w-[110px]">
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">General Average</span>
                                <span class="text-lg font-black font-mono {{ ($rec['gwa'] ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }} block">
                                    {{ $rec['gwa'] !== null ? number_format($rec['gwa'], 2) : '—' }}
                                </span>
                            </div>

                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/80 rounded-xl border border-zinc-200 dark:border-zinc-700/80 text-center min-w-[110px]">
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Academic Remarks</span>
                                <span class="text-xs font-black block pt-1">
                                    @if ($rec['gwa'] !== null && $rec['gwa'] >= 75.00)
                                        <span class="px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-[10px] font-black border border-emerald-200">
                                            PASSED
                                        </span>
                                    @elseif ($rec['gwa'] !== null)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200">
                                            FAILED
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 text-[10px] font-bold">
                                            PENDING
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grades Table -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-start">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 text-[10px] font-bold uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                                <tr>
                                    <th class="py-3 px-3 text-center w-10">#</th>
                                    <th class="py-3 px-4 text-start">Learning Areas</th>
                                    <th class="py-3 px-4 text-start">Faculty Instructor</th>
                                    @foreach ($rec['quarters'] as $q)
                                        <th class="py-3 px-3 text-center min-w-[70px]">
                                            {{ $q->short_name }}
                                        </th>
                                    @endforeach
                                    <th class="py-3 px-3 text-center min-w-[85px] bg-emerald-50/50 dark:bg-emerald-950/20 text-[#166534] dark:text-emerald-300 font-black">
                                        Final Grade
                                    </th>
                                    <th class="py-3 px-3 text-center w-28">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                                @forelse ($rec['subject_rows'] as $idx => $row)
                                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                                        <td class="py-3 px-3 text-center text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $row['name'] }}</span>
                                            <span class="font-mono text-[10px] text-[#166534] dark:text-emerald-400 font-bold">{{ $row['code'] }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                                            {{ $row['teacher'] }}
                                        </td>
                                        @foreach ($rec['quarters'] as $q)
                                            @php $val = $row['q_marks'][$q->id] ?? null; @endphp
                                            <td class="py-3 px-3 text-center font-mono font-bold">
                                                @if ($val !== null)
                                                    <span class="{{ $val >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600 font-black' }}">
                                                        {{ number_format($val, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="py-3 px-3 text-center bg-emerald-50/50 dark:bg-emerald-950/20 font-mono font-black text-sm">
                                            @if ($row['final_avg'] !== null)
                                                <span class="{{ $row['final_avg'] >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                                                    {{ number_format($row['final_avg'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-400 dark:text-zinc-500 font-bold">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @if ($row['remarks'] === 'PASSED')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                                    PASSED
                                                </span>
                                            @elseif ($row['remarks'] === 'FAILED')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                                    FAILED
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                                    PENDING
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($rec['quarters']) + 5 }}" class="p-8 text-center text-zinc-400 italic">
                                            No learning areas or subject records found for this enrollment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <!-- Footer GWA Row -->
                            @if (!empty($rec['subject_rows']))
                                <tfoot class="bg-zinc-50 dark:bg-zinc-800/80 border-t-2 border-zinc-200 dark:border-zinc-700 font-bold text-xs">
                                    <tr>
                                        <td colspan="3" class="py-3.5 px-4 text-end uppercase text-zinc-600 dark:text-zinc-300 tracking-wider">
                                            General Weighted Average (GWA):
                                        </td>
                                        <td colspan="{{ count($rec['quarters']) }}" class="text-center font-normal text-zinc-400 text-[10px]">
                                            {{ $rec['gwa'] !== null ? 'Final Consolidated Rating' : 'Pending Term Completion' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center bg-emerald-100/60 dark:bg-emerald-950/60 font-mono font-black text-base text-[#166534] dark:text-emerald-300">
                                            {{ $rec['gwa'] !== null ? number_format($rec['gwa'], 2) : '—' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            @if ($rec['gwa'] !== null)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $rec['gwa'] >= 75 ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-rose-50 text-rose-700' }}">
                                                    {{ $rec['gwa'] >= 75 ? 'PASSED' : 'FAILED' }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                                    PENDING
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <!-- Grade Level Records Unavailable / Unreached State -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-800 p-12 text-center space-y-4 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fas fa-folder-closed"></i>
            </div>
            <div class="space-y-1 max-w-md mx-auto">
                <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-200">
                    No Records Available for {{ $selectedData['name'] ?? 'Selected Grade Level' }}
                </h3>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    {{ $selectedData['message'] ?? 'The student has no academic history records for this grade level in the system database.' }}
                </p>
            </div>
            <div class="pt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-info-circle text-zinc-400"></i>
                    <span>Records are created only when a student is officially enrolled and graded.</span>
                </span>
            </div>
        </div>
    @endif
</div>
