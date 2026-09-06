<div class="space-y-6 font-sans">
    <!-- Header & Export Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chart-column text-[#166534]"></i>
                <span>Academic Analytics & Performance Intelligence</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">In-depth visual analytics, subject mastery breakdowns, and early warning indicators for DMMNHS.</p>
        </div>

        <button wire:click="exportCsv" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-file-csv"></i>
            <span>Export Analytics CSV</span>
        </button>
    </div>

    <!-- Analytics Context Filters Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div>
            <label class="block font-bold text-zinc-500 text-[10px] uppercase mb-1">School Year</label>
            <select wire:model.live="selectedSchoolYearId" class="w-full px-3 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All School Years</option>
                @foreach ($schoolYears as $sy)
                    <option value="{{ $sy->id }}">SY {{ $sy->school_year }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-zinc-500 text-[10px] uppercase mb-1">Quarter</label>
            <select wire:model.live="selectedQuarterId" class="w-full px-3 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Quarters</option>
                @foreach ($quarters as $q)
                    <option value="{{ $q->id }}">{{ $q->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-zinc-500 text-[10px] uppercase mb-1">Grade Level</label>
            <select wire:model.live="selectedYearLevelId" class="w-full px-3 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Grade Levels</option>
                @foreach ($yearLevels as $yl)
                    <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-zinc-500 text-[10px] uppercase mb-1">Section</label>
            <select wire:model.live="selectedSectionId" class="w-full px-3 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Sections</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- 1. Executive Performance Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Overall General Average</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center text-[#166534]">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ number_format($summaryStats['overall_average'], 2) }}
            </div>
            <span class="text-[11px] text-emerald-600 font-bold block">Academic performance index</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Honor Roll Candidates</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950 flex items-center justify-center text-amber-600">
                    <i class="fas fa-award text-sm"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $summaryStats['honor_students'] }} <span class="text-xs font-normal text-zinc-400">Students</span>
            </div>
            <span class="text-[11px] text-amber-600 font-bold block">Average Grade &ge; 90.00</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Overall Passing Rate</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950 flex items-center justify-center text-blue-600">
                    <i class="fas fa-percent text-sm"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ number_format($summaryStats['passing_rate'], 1) }}%
            </div>
            <span class="text-[11px] text-blue-600 font-bold block">Grades &ge; 75.00</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">At-Risk Students</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950 flex items-center justify-center text-rose-600">
                    <i class="fas fa-triangle-exclamation text-sm"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-rose-600">
                {{ $summaryStats['at_risk_students'] }} <span class="text-xs font-normal text-zinc-400">Students</span>
            </div>
            <span class="text-[11px] text-rose-600 font-bold block">Requires Academic Intervention (&lt; 75)</span>
        </div>
    </div>

    <!-- 2. Performance Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Year Level Comparison Bar Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chart-bar text-[#166534]"></i>
                <span>Year Level Performance Comparison</span>
            </h3>
            <div id="chart-analytics-year-level" class="h-64"></div>
        </div>

        <!-- Quarterly Trend Line Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-600"></i>
                <span>Quarterly Performance Trend (Q1 - Q4)</span>
            </h3>
            <div id="chart-analytics-quarter-trend" class="h-64"></div>
        </div>

        <!-- Subject Mastery Column Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-book text-amber-600"></i>
                <span>Core Subject Mastery Averages</span>
            </h3>
            <div id="chart-analytics-subject" class="h-64"></div>
        </div>

        <!-- Grade Distribution Donut Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chart-pie text-emerald-600"></i>
                <span>Grade Rating Distribution Breakdown</span>
            </h3>
            <div id="chart-analytics-distribution" class="h-64"></div>
        </div>
    </div>

    <!-- 3. Subject Mastery Analysis Table -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
            <i class="fas fa-list-check text-[#166534]"></i>
            <span>Detailed Subject Mastery Breakdown</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Subject Code</th>
                        <th class="py-3 px-4 text-start">Subject Name</th>
                        <th class="py-3 px-4 text-center">Students Assessed</th>
                        <th class="py-3 px-4 text-center">General Average</th>
                        <th class="py-3 px-4 text-center">Highest Grade</th>
                        <th class="py-3 px-4 text-center">Lowest Grade</th>
                        <th class="py-3 px-4 text-center">Passing Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($subjectBreakdown as $sb)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-700 dark:text-blue-400">
                                {{ $sb['subject_code'] }}
                            </td>
                            <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $sb['subject_name'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                {{ $sb['students_count'] }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-[#166534]">
                                {{ number_format($sb['average'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-emerald-700">
                                {{ number_format($sb['highest'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono {{ $sb['lowest'] < 75 ? 'text-rose-600 font-bold' : 'text-zinc-600' }}">
                                {{ number_format($sb['lowest'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded font-mono font-bold text-[11px] {{ $sb['passing_rate'] >= 90 ? 'bg-[#DCFCE7] text-[#166534]' : ($sb['passing_rate'] >= 75 ? 'bg-blue-50 text-blue-800' : 'bg-rose-50 text-rose-700') }}">
                                    {{ number_format($sb['passing_rate'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-zinc-400 italic">No subject data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. At-Risk Students Warning Feed -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 flex items-center gap-2">
            <i class="fas fa-triangle-exclamation"></i>
            <span>At-Risk Students Early Warning Feed (Failing Grades &lt; 75)</span>
        </h3>

        <div class="space-y-2">
            @forelse ($atRiskStudents as $ar)
                <div class="p-3.5 rounded-xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-rose-800 dark:text-rose-300">{{ $ar['student_number'] }}</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $ar['student_name'] }}</span>
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5">
                            {{ $ar['year_level'] }} - {{ $ar['section'] }} | Subject: <span class="font-bold text-rose-700">{{ $ar['failing_subjects'] }}</span> | Adviser: {{ $ar['adviser'] }}
                        </div>
                    </div>

                    <div class="self-end sm:self-center">
                        <span class="px-3 py-1 rounded-lg font-mono font-bold text-xs bg-rose-600 text-white shadow-sm">
                            Grade: {{ number_format($ar['grade'], 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-emerald-700 bg-emerald-50 rounded-xl border border-emerald-200 italic text-xs font-medium">
                    <i class="fas fa-circle-check text-base mb-1 block"></i>
                    No at-risk students identified for the selected criteria. All students are meeting or exceeding passing standards!
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    (function () {
        window.academicChartInstances = window.academicChartInstances || {};

        function destroyChart(containerSelector) {
            if (window.academicChartInstances[containerSelector]) {
                try {
                    window.academicChartInstances[containerSelector].destroy();
                } catch (e) {}
                window.academicChartInstances[containerSelector] = null;
            }
            const el = document.querySelector(containerSelector);
            if (el) el.innerHTML = '';
        }

        function initAcademicAnalyticsCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const themeMode = isDark ? 'dark' : 'light';
            const textClr = isDark ? '#a1a1aa' : '#52525b';
            const gridClr = isDark ? '#27272a' : '#f4f4f5';

            // Year Level Chart
            destroyChart('#chart-analytics-year-level');
            if (document.querySelector("#chart-analytics-year-level")) {
                var chartYL = new ApexCharts(document.querySelector("#chart-analytics-year-level"), {
                    chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                    theme: { mode: themeMode },
                    series: [{ name: 'Average Grade', data: @json($yearLevelChart['series'] ?? []) }],
                    colors: ['#166534'],
                    plotOptions: {
                        bar: { borderRadius: 6, columnWidth: '38%', dataLabels: { position: 'top' } }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) { return val > 0 ? val.toFixed(1) : ''; },
                        offsetY: -18,
                        style: { fontSize: '11px', colors: [textClr], fontWeight: 'bold' }
                    },
                    xaxis: {
                        categories: @json($yearLevelChart['categories'] ?? []),
                        labels: { style: { colors: textClr, fontSize: '11px', fontWeight: 600 } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: textClr, fontSize: '11px' } } },
                    grid: { borderColor: gridClr, strokeDashArray: 4 },
                    tooltip: { theme: themeMode }
                });
                chartYL.render();
                window.academicChartInstances['#chart-analytics-year-level'] = chartYL;
            }

            // Quarter Trend Chart
            destroyChart('#chart-analytics-quarter-trend');
            if (document.querySelector("#chart-analytics-quarter-trend")) {
                var chartQT = new ApexCharts(document.querySelector("#chart-analytics-quarter-trend"), {
                    chart: { type: 'area', height: 250, toolbar: { show: false }, background: 'transparent' },
                    theme: { mode: themeMode },
                    series: [{ name: 'Quarterly Average', data: @json($quarterTrendChart['series'] ?? []) }],
                    colors: ['#2563eb'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    markers: { size: 5, hover: { size: 7 } },
                    xaxis: {
                        categories: @json($quarterTrendChart['categories'] ?? []),
                        labels: { style: { colors: textClr, fontSize: '11px', fontWeight: 600 } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: textClr, fontSize: '11px' } } },
                    grid: { borderColor: gridClr, strokeDashArray: 4 },
                    tooltip: { theme: themeMode }
                });
                chartQT.render();
                window.academicChartInstances['#chart-analytics-quarter-trend'] = chartQT;
            }

            // Subject Mastery Chart
            destroyChart('#chart-analytics-subject');
            if (document.querySelector("#chart-analytics-subject")) {
                var chartSub = new ApexCharts(document.querySelector("#chart-analytics-subject"), {
                    chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                    theme: { mode: themeMode },
                    series: [{ name: 'Subject Average', data: @json($subjectChart['series'] ?? []) }],
                    colors: ['#d97706'],
                    plotOptions: {
                        bar: { borderRadius: 6, columnWidth: '45%', dataLabels: { position: 'top' } }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) { return val > 0 ? val.toFixed(1) : ''; },
                        offsetY: -18,
                        style: { fontSize: '10px', colors: [textClr], fontWeight: 'bold' }
                    },
                    xaxis: {
                        categories: @json($subjectChart['categories'] ?? []),
                        labels: {
                            style: { colors: textClr, fontSize: '10px', fontWeight: 600 },
                            rotate: -25,
                            rotateAlways: false
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: textClr, fontSize: '11px' } } },
                    grid: { borderColor: gridClr, strokeDashArray: 4 },
                    tooltip: { theme: themeMode }
                });
                chartSub.render();
                window.academicChartInstances['#chart-analytics-subject'] = chartSub;
            }

            // Grade Distribution Donut Chart
            destroyChart('#chart-analytics-distribution');
            if (document.querySelector("#chart-analytics-distribution")) {
                var chartDist = new ApexCharts(document.querySelector("#chart-analytics-distribution"), {
                    chart: { type: 'donut', height: 250, background: 'transparent' },
                    theme: { mode: themeMode },
                    series: @json($gradeDistributionChart['series'] ?? []),
                    labels: @json($gradeDistributionChart['labels'] ?? []),
                    colors: ['#166534', '#0284c7', '#2563eb', '#f59e0b', '#ef4444'],
                    legend: {
                        position: 'bottom',
                        fontSize: '11px',
                        labels: { colors: textClr }
                    },
                    stroke: { width: 0 },
                    dataLabels: { enabled: true }
                });
                chartDist.render();
                window.academicChartInstances['#chart-analytics-distribution'] = chartDist;
            }
        }

        document.addEventListener('livewire:navigated', initAcademicAnalyticsCharts);
        document.addEventListener('DOMContentLoaded', initAcademicAnalyticsCharts);
        setTimeout(initAcademicAnalyticsCharts, 100);

        if (window.Livewire) {
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(() => {
                    setTimeout(initAcademicAnalyticsCharts, 100);
                });
            });
        }
    })();
</script>
