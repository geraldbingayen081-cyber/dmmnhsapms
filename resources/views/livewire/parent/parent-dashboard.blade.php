<div class="space-y-6 font-sans">
    <!-- 1. Multi-Child Selector Toolbar (If parent has children) -->
    @if ($children->count() > 1)
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider flex items-center gap-1.5">
                    <i class="fas fa-children text-amber-500"></i>
                    <span>Select Child to Monitor:</span>
                </span>
                <span class="text-[11px] text-zinc-500 font-medium">Click on a child to switch academic view</span>
            </div>

            <div class="flex flex-wrap gap-2.5 pt-1">
                @foreach ($children as $ch)
                    @php
                        $isSelected = (int)$ch->id === (int)$selectedStudentId;
                        $chEnr = $ch->enrollments->last();
                    @endphp
                    <button wire:click="selectChild({{ $ch->id }})" class="px-4 py-2.5 rounded-xl border text-xs font-bold flex items-center gap-3 transition cursor-pointer {{ $isSelected ? 'bg-[#166534] text-white border-emerald-700 shadow-md ring-2 ring-emerald-500/30' : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}">
                        <div class="size-7 rounded-lg {{ $isSelected ? 'bg-amber-400 text-emerald-950' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }} flex items-center justify-center text-xs font-black">
                            {{ substr($ch->first_name, 0, 1) }}
                        </div>
                        <div class="text-start">
                            <span class="block leading-tight font-black">{{ $ch->full_name }}</span>
                            <span class="block text-[10px] {{ $isSelected ? 'text-emerald-200' : 'text-zinc-400' }} font-medium">
                                {{ $chEnr?->schoolYearSection?->yearLevel?->name ?? 'JHS' }} - {{ $chEnr?->schoolYearSection?->section?->name ?? 'Enrolled' }}
                            </span>
                        </div>
                        @if ($isSelected)
                            <i class="fas fa-check-circle text-amber-300 text-xs ml-1"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Hero Learner Profile Banner -->
    @if ($selectedStudent)
        <div class="bg-gradient-to-r from-[#166534] via-[#15803d] to-emerald-800 text-white rounded-2xl p-6 sm:p-7 shadow-md relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-20 top-0 w-32 h-32 bg-amber-400/20 rounded-full blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-2xl bg-white/20 backdrop-blur-md text-amber-300 flex items-center justify-center font-black text-2xl border border-white/30 shadow-inner shrink-0">
                        {{ substr($selectedStudent->first_name ?? 'S', 0, 1) }}{{ substr($selectedStudent->last_name ?? '', 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                {{ $selectedStudent->full_name }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-md text-white border border-white/30">
                                LRN: {{ $selectedStudent->student_number }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-emerald-100 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-school text-amber-300"></i>
                                <span>{{ $activeEnrollment?->schoolYearSection?->yearLevel?->name ?? 'Junior High' }} — Section {{ $activeEnrollment?->schoolYearSection?->section?->name ?? 'Enrolled' }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-chalkboard-user text-emerald-300"></i>
                                <span>Adviser: {{ $activeEnrollment?->schoolYearSection?->adviser?->full_name ?? 'Faculty Assigned' }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-calendar-alt text-amber-300"></i>
                                <span>SY {{ $activeSy?->school_year ?? '2026-2027' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:items-end gap-2 shrink-0">
                    <span class="px-3.5 py-1.5 rounded-xl bg-white/15 backdrop-blur-md border border-white/25 text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-child text-amber-300"></i>
                        <span>Enrolled Child Record</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. KPI Summary Cards (5 Metrics) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 text-xs">
            <!-- 1. Final GWA -->
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-zinc-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Final General Average</span>
                    <i class="fas fa-chart-line text-xs text-emerald-600"></i>
                </div>
                @if (!empty($kpi['isCompleteGwa']) && $kpi['gwa'] !== null)
                    <span class="text-2xl font-black {{ ($kpi['gwa'] ?? 0) >= 90 ? 'text-amber-600 dark:text-amber-400' : (($kpi['gwa'] ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600') }} block">
                        {{ number_format($kpi['gwa'], 2) }}
                    </span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block">Final GWA (Q1–Q3)</span>
                @else
                    <span class="text-2xl font-black text-zinc-400 dark:text-zinc-500 block">
                        —
                    </span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold block">Pending Q1–Q3 Complete</span>
                @endif
            </div>

            <!-- 2. Passed Learning Areas -->
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-zinc-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Learning Areas</span>
                    <i class="fas fa-book-open text-xs text-blue-500"></i>
                </div>
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400 block">
                    {{ $kpi['passedCount'] }} / {{ $kpi['totalSubjects'] }}
                </span>
                <span class="text-[10px] text-zinc-500 block">Completed Subjects</span>
            </div>

            <!-- 3. Best in Subject Awards -->
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-zinc-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Best in Subject</span>
                    <i class="fas fa-medal text-xs text-indigo-500"></i>
                </div>
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 block">
                    {{ $kpi['bestSubjectCount'] }}
                </span>
                <span class="text-[10px] text-zinc-500 block">Top Subject Awards</span>
            </div>

            <!-- 4. Highest Subject Mark -->
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-zinc-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Highest Subject Mark</span>
                    <i class="fas fa-star text-xs text-amber-500"></i>
                </div>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 block">
                    {{ $kpi['highestGrade'] ? number_format($kpi['highestGrade'], 1) : 'N/A' }}
                </span>
                <span class="text-[10px] text-zinc-500 block">Personal Best Score</span>
            </div>

            <!-- 5. Lowest Learning Area Mark -->
            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-zinc-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Lowest Subject Mark</span>
                    <i class="fas fa-shield text-xs text-zinc-400"></i>
                </div>
                <span class="text-2xl font-black {{ ($kpi['lowestGrade'] ?? 0) >= 85 ? 'text-[#166534] dark:text-emerald-400' : (($kpi['lowestGrade'] ?? 0) >= 75 ? 'text-zinc-700 dark:text-zinc-300' : 'text-rose-600') }} block">
                    {{ $kpi['lowestGrade'] ? number_format($kpi['lowestGrade'], 1) : 'N/A' }}
                </span>
                <span class="text-[10px] text-zinc-500 block">
                    {{ ($kpi['lowestGrade'] ?? 0) >= 85 ? 'Honors Floor Met (≥ 85)' : 'Passing Threshold: 75' }}
                </span>
            </div>
        </div>

        <!-- 4. Visual Analytics Section (2 ApexCharts) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart 1: Quarterly Grade Progression vs Class Average -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4 flex flex-col justify-between">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-arrow-trend-up text-[#166534] dark:text-emerald-400"></i>
                            <span>Child Quarterly Performance Trend (Q1–Q3)</span>
                        </h3>
                        <p class="text-[11px] text-zinc-500">
                            Comparison of your child's general average against the section class average
                        </p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-[#166534] border border-emerald-200">
                        Progression
                    </span>
                </div>

                @if ($progressionChart['hasData'])
                    <div wire:ignore
                        x-data="{}"
                        x-init="(function() {
                            var categories = @js($progressionChart['categories']);
                            var childAverages = @js($progressionChart['childAverages']);
                            var sectionAverages = @js($progressionChart['sectionAverages']);

                            function draw() {
                                var el = $el.querySelector('[data-chart-parent-progression]');
                                if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                                if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                                var dark = document.documentElement.classList.contains('dark');
                                var tc = dark ? '#a1a1aa' : '#52525b';
                                var gc = dark ? '#27272a' : '#f4f4f5';

                                el._apexChart = new ApexCharts(el, {
                                    chart: { type: 'line', height: 260, toolbar: { show: false }, background: 'transparent' },
                                    theme: { mode: dark ? 'dark' : 'light' },
                                    series: [
                                        { name: 'My Child', data: childAverages },
                                        { name: 'Section Average', data: sectionAverages }
                                    ],
                                    colors: ['#166534', '#3b82f6'],
                                    stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 4] },
                                    markers: { size: [5, 4], hover: { size: 7 } },
                                    xaxis: {
                                        categories: categories,
                                        labels: { style: { colors: tc, fontSize: '11px', fontWeight: 600 } },
                                        axisBorder: { show: false },
                                        axisTicks: { show: false }
                                    },
                                    yaxis: {
                                        min: 65,
                                        max: 100,
                                        tickAmount: 5,
                                        labels: {
                                            style: { colors: tc, fontSize: '11px' },
                                            formatter: function(val) { return val.toFixed(1); }
                                        }
                                    },
                                    grid: { borderColor: gc, strokeDashArray: 4 },
                                    legend: { position: 'top', labels: { colors: tc } },
                                    tooltip: {
                                        theme: dark ? 'dark' : 'light',
                                        y: {
                                            formatter: function(val) {
                                                return val !== null ? val.toFixed(2) : 'No grade yet';
                                            }
                                        }
                                    }
                                });
                                el._apexChart.render();
                            }
                            draw();
                        })();">
                        <div data-chart-parent-progression class="w-full h-64 min-h-[250px]"></div>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                        <i class="fas fa-chart-line text-2xl mb-2 text-zinc-300"></i>
                        <span class="text-xs font-medium">Quarterly progression data will appear once grades are posted.</span>
                    </div>
                @endif
            </div>

            <!-- Chart 2: Subject Performance & Learning Area Mastery -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4 flex flex-col justify-between">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-chart-simple text-blue-600"></i>
                            <span>Learning Area Mastery Breakdown</span>
                        </h3>
                        <p class="text-[11px] text-zinc-500">
                            Subject ratings across core JHS learning areas (Passing Standard: 75.00)
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <select wire:model.live="filterQuarter" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="all">Q1–Q3 Average</option>
                            @foreach ($quarters as $q)
                                <option value="{{ $q->id }}">{{ $q->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($subjectMasteryChart['hasData'])
                    <div wire:ignore
                        x-data="{}"
                        x-init="(function() {
                            var subjects = @js($subjectMasteryChart['subjects']);
                            var grades = @js($subjectMasteryChart['grades']);

                            function draw() {
                                var el = $el.querySelector('[data-chart-parent-subjects]');
                                if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                                if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                                var dark = document.documentElement.classList.contains('dark');
                                var tc = dark ? '#a1a1aa' : '#52525b';
                                var gc = dark ? '#27272a' : '#f4f4f5';

                                el._apexChart = new ApexCharts(el, {
                                    chart: { type: 'bar', height: 260, toolbar: { show: false }, background: 'transparent' },
                                    theme: { mode: dark ? 'dark' : 'light' },
                                    series: [{ name: 'Grade Rating', data: grades }],
                                    colors: ['#166534'],
                                    plotOptions: {
                                        bar: { borderRadius: 6, columnWidth: '50%', dataLabels: { position: 'top' } }
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: function(val) { return val > 0 ? val.toFixed(1) : ''; },
                                        style: { fontSize: '10px', fontWeight: 700, colors: [dark ? '#d4d4d8' : '#3f3f46'] },
                                        offsetY: -5
                                    },
                                    xaxis: {
                                        categories: subjects,
                                        labels: { style: { colors: tc, fontSize: '10px', fontWeight: 600 }, rotate: -15, trim: true, maxHeight: 60 },
                                        axisBorder: { show: false },
                                        axisTicks: { show: false }
                                    },
                                    yaxis: {
                                        min: 60,
                                        max: 100,
                                        tickAmount: 4,
                                        labels: { style: { colors: tc, fontSize: '11px' }, formatter: function(val) { return Math.round(val); } }
                                    },
                                    grid: { borderColor: gc, strokeDashArray: 4 },
                                    tooltip: {
                                        theme: dark ? 'dark' : 'light',
                                        y: {
                                            formatter: function(val) {
                                                return val.toFixed(2) + (val >= 75 ? ' (PASSED)' : ' (NEEDS SUPPORT)');
                                            }
                                        }
                                    }
                                });
                                el._apexChart.render();
                            }
                            draw();
                        })();">
                        <div data-chart-parent-subjects class="w-full h-64 min-h-[250px]"></div>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                        <i class="fas fa-chart-bar text-2xl mb-2 text-zinc-300"></i>
                        <span class="text-xs font-medium">Subject ratings will appear once grade entries are recorded.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 5. Official Quarterly Subject Grade Sheet (Q1 to Q3) -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden space-y-4 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-book text-[#166534] dark:text-emerald-400"></i>
                        <span>Official Quarterly Subject Grade Sheet</span>
                    </h3>
                    <p class="text-xs text-zinc-500">
                        Consolidated subject marks for {{ $selectedStudent->first_name }} (1st Quarter to 3rd Quarter)
                    </p>
                </div>

                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shrink-0">
                    DepEd Passing: 75.00 • Max: 99.00
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 text-[10px] font-bold uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3 text-center w-10">#</th>
                            <th class="py-3 px-4 text-start">Learning Area / Subject</th>
                            <th class="py-3 px-4 text-start">Faculty Instructor</th>
                            @foreach ($quarters as $q)
                                <th class="py-3 px-3 text-center min-w-[70px]">
                                    {{ $q->short_name }}
                                </th>
                            @endforeach
                            <th class="py-3 px-3 text-center min-w-[85px] bg-emerald-50/50 dark:bg-emerald-950/20 text-[#166534] dark:text-emerald-300 font-extrabold">
                                Final Grade
                            </th>
                            <th class="py-3 px-3 text-center w-24">Status</th>
                            <th class="py-3 px-4 text-start">Awards / Recognition</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                        @forelse ($subjectRows as $idx => $row)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3 px-3 text-center text-zinc-400 font-bold">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $row['name'] }}</span>
                                    <span class="font-mono text-[10px] text-[#166534] dark:text-emerald-400 font-bold">{{ $row['code'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                                    {{ $row['teacher'] }}
                                </td>
                                @foreach ($quarters as $q)
                                    @php
                                        $qMark = $row['q_marks'][$q->id] ?? null;
                                    @endphp
                                    <td class="py-3 px-3 text-center font-mono font-bold">
                                        @if ($qMark !== null)
                                            <span class="{{ $qMark >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600 font-black' }}">
                                                {{ number_format($qMark, 1) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-300 dark:text-zinc-600 text-[11px]">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3 px-3 text-center bg-emerald-50/50 dark:bg-emerald-950/20 font-mono font-black text-sm">
                                    @if ($row['final_avg'] !== null)
                                        <span class="{{ $row['final_avg'] >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                                            {{ number_format($row['final_avg'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500 font-bold" title="Pending complete grades from 1st to 3rd quarter">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if ($row['status'] === 'PASSED')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                            PASSED
                                        </span>
                                    @elseif ($row['status'] === 'FAILED')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            FAILED
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400" title="Complete grades from 1st to 3rd quarter required">
                                            PENDING
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($row['is_best'])
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 inline-flex items-center gap-1 shadow-2xs">
                                            <i class="fas fa-medal text-indigo-500"></i>
                                            <span>Best in {{ $row['name'] }}</span>
                                        </span>
                                    @else
                                        <span class="text-zinc-400 text-[11px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($quarters) + 5 }}" class="p-10 text-center text-zinc-400 italic">
                                    No subject enrollments available for this child.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Awards and Achievements Showcase for the Child -->
        @if (!empty($earnedAwards))
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-award text-amber-500"></i>
                            <span>{{ $selectedStudent->first_name }}'s Awards and Achievements</span>
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Quarterly honors and subject excellence awards earned in SY {{ $activeSy?->school_year }}
                        </p>
                    </div>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                        {{ count($earnedAwards) }} Recognitions
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach ($earnedAwards as $aw)
                        <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-800/80 bg-gradient-to-br from-amber-50/60 to-white dark:from-amber-950/20 dark:to-zinc-900 space-y-2 shadow-2xs flex items-start gap-3">
                            <div class="size-10 rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                                @if ($aw['type'] === 'honor')
                                    <i class="fas fa-crown text-amber-600"></i>
                                @elseif ($aw['type'] === 'subject')
                                    <i class="fas fa-medal text-indigo-600"></i>
                                @else
                                    <i class="fas fa-certificate text-emerald-600"></i>
                                @endif
                            </div>
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-[#166534] border border-emerald-300">
                                        {{ $aw['quarter'] }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm leading-snug">
                                    {{ $aw['title'] }}
                                </h4>
                                @if (isset($aw['grade']))
                                    <p class="text-[11px] font-mono text-emerald-700 dark:text-emerald-400 font-bold">
                                        Rating: {{ number_format($aw['grade'], 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 7. Faculty & Class Adviser Contact Directory -->
        @if (!empty($teachersDirectory) || $activeEnrollment?->schoolYearSection?->adviser)
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-id-badge text-blue-600"></i>
                            <span>Faculty & Class Adviser Contact Directory</span>
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Assigned teachers for {{ $selectedStudent->first_name }}'s section ({{ $activeEnrollment?->schoolYearSection?->yearLevel?->name }} - {{ $activeEnrollment?->schoolYearSection?->section?->name }})
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    @if ($activeEnrollment?->schoolYearSection?->adviser)
                        <div class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/20 space-y-1">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-200 text-amber-900">
                                Class Adviser
                            </span>
                            <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $activeEnrollment->schoolYearSection->adviser->full_name }}
                            </h4>
                            <p class="text-[11px] text-zinc-500">
                                Contact: <span class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $activeEnrollment->schoolYearSection->adviser->contact_number ?? 'School Office' }}</span>
                            </p>
                        </div>
                    @endif

                    @foreach ($teachersDirectory as $td)
                        <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/60 space-y-1">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                {{ $td['subject'] }}
                            </span>
                            <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $td['name'] }}
                            </h4>
                            <p class="text-[11px] text-zinc-500">
                                Contact: <span class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $td['contact'] }}</span>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        <!-- No Child Linked Empty State -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-dashed border-zinc-300 dark:border-zinc-700 p-12 text-center space-y-3">
            <div class="size-16 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center mx-auto text-2xl shadow-inner">
                <i class="fas fa-user-xmark"></i>
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">No Student Records Linked</h3>
            <p class="text-xs text-zinc-500 max-w-md mx-auto">
                There are currently no student accounts linked to your parent portal account. Please contact your child's class adviser or the school administration to link your student records.
            </p>
        </div>
    @endif
</div>
