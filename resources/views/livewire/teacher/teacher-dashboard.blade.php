<div class="space-y-6 font-sans">
    <!-- Clean Executive Context & Filter Toolbar (Mabuhay banner replaced) -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold mb-2">
                <i class="fas fa-chalkboard-user"></i>
                <span>Faculty Analytics • {{ $teacher?->employee_number ?? auth()->user()->login_id }}</span>
                <span>•</span>
                <span>SY {{ $activeSy?->school_year ?? 'Current' }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                Teacher Academic Dashboard & Performance Analytics
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5 font-medium">
                Monitor student grade progression across quarters, identify learning gaps, and track teaching workload metrics.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter by Class -->
            <div>
                <select wire:model.live="filterClassId" class="px-3 py-2 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    <option value="all">All Assigned Classes</option>
                    @foreach ($allTeachingLoads as $ld)
                        <option value="{{ $ld->id }}">
                            {{ $ld->schoolYearSection?->yearLevel?->name }} - {{ $ld->schoolYearSection?->section?->name }} ({{ $ld->subject?->subject_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Quarter -->
            <div>
                <select wire:model.live="filterQuarterId" class="px-3 py-2 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    <option value="all">All Quarters (Cumulative)</option>
                    @foreach ($quarters as $q)
                        <option value="{{ $q->id }}">{{ $q->name }} {{ $q->status === 'active' ? '(Current)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Action: Encode -->
            <a href="{{ route('teacher.grades') }}" class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fas fa-file-pen text-xs"></i>
                <span>Grade Sheet</span>
            </a>
        </div>
    </div>

    <!-- 6 KPI Metric Cards Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
        <!-- 1. Teaching Loads -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Teaching Classes</span>
                <i class="fas fa-layer-group text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-black text-zinc-900 dark:text-zinc-100 block">{{ $allTeachingLoads->count() }}</span>
            <span class="text-[10px] text-zinc-500 block">Assigned sections</span>
        </div>

        <!-- 2. Students Handled -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Students Taught</span>
                <i class="fas fa-user-graduate text-xs text-blue-600"></i>
            </div>
            <span class="text-xl font-black text-zinc-900 dark:text-zinc-100 block">{{ $totalStudentsHandled }}</span>
            <span class="text-[10px] text-zinc-500 block">Enrolled learners</span>
        </div>

        <!-- 3. Overall Average -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Subject Average</span>
                <i class="fas fa-chart-line text-xs text-emerald-600"></i>
            </div>
            <span class="text-xl font-black {{ ($overallAverage ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }} block">
                {{ $overallAverage ? number_format($overallAverage, 2) : '—' }}
            </span>
            <span class="text-[10px] text-zinc-500 block">Mean class rating</span>
        </div>

        <!-- 4. Passing Rate % -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Passing Rate</span>
                <i class="fas fa-circle-check text-xs text-emerald-600"></i>
            </div>
            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 block">{{ $passingRate }}%</span>
            <span class="text-[10px] text-zinc-500 block">Grades ≥ 75.00</span>
        </div>

        <!-- 5. Achievers -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Achievers</span>
                <i class="fas fa-award text-xs text-amber-500"></i>
            </div>
            <span class="text-xl font-black text-amber-600 dark:text-amber-400 block">{{ $honorsCount }}</span>
            <span class="text-[10px] text-zinc-500 block">Student Achievers</span>
        </div>

        <!-- 6. At Risk / Intervention -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">At-Risk Learners</span>
                <i class="fas fa-triangle-exclamation text-xs text-rose-500"></i>
            </div>
            <span class="text-xl font-black {{ $atRiskCount > 0 ? 'text-rose-600' : 'text-zinc-400' }} block">{{ $atRiskCount }}</span>
            <span class="text-[10px] text-zinc-500 block">Ratings &lt; 75.00</span>
        </div>
    </div>

    <!-- MAIN ANALYTICS SECTION: 2 Focused Line Graphs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Quarterly Grade Progression Trend (Line Graph) -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-line text-[#166534]"></i>
                        <span>Quarterly Academic Performance Trend</span>
                    </h3>
                    <p class="text-[11px] text-zinc-500">
                        Progression of class averages across academic quarters
                    </p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-[#166534] border border-emerald-200">
                    Line Trend
                </span>
            </div>

            @if ($progressionChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var labels = @js($progressionChart['labels']);
                        var series = @js($progressionChart['series']);
                        function draw() {
                            var el = $el.querySelector('[data-chart-progression]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            var gc = dark ? '#27272a' : '#f4f4f5';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'line', height: 280, toolbar: { show: false }, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: series,
                                colors: ['#166534', '#0284c7', '#d97706', '#7c3aed', '#e11d48', '#059669'],
                                stroke: { curve: 'smooth', width: 3 },
                                markers: { size: 5, hover: { size: 7 } },
                                annotations: {
                                    yaxis: [{
                                        y: 75,
                                        borderColor: '#ef4444',
                                        strokeDashArray: 4,
                                        label: {
                                            borderColor: '#ef4444',
                                            style: { color: '#fff', background: '#ef4444', fontSize: '10px', fontWeight: 700 },
                                            text: 'Passing Benchmark: 75.00'
                                        }
                                    }]
                                },
                                xaxis: {
                                    categories: labels,
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
                                        formatter: function(val) { return val ? val.toFixed(1) : ''; }
                                    }
                                },
                                grid: { borderColor: gc, strokeDashArray: 4 },
                                tooltip: { theme: dark ? 'dark' : 'light' },
                                legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart-progression class="w-full h-72 min-h-[270px]"></div>
                </div>
            @else
                <div class="h-72 min-h-[270px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-chart-line text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No recorded grades available for quarterly progression.</span>
                </div>
            @endif
        </div>

        <!-- Chart 2: Learner Mastery & Performance Distribution (Line / Curve) -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-area text-blue-600"></i>
                        <span>Learner Performance & Mastery Distribution</span>
                    </h3>
                    <p class="text-[11px] text-zinc-500">
                        Student count frequency curve across official DepEd achievement levels
                    </p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                    Mastery Curve
                </span>
            </div>

            @if ($masteryChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var labels = @js($masteryChart['labels']);
                        var values = @js($masteryChart['values']);
                        function draw() {
                            var el = $el.querySelector('[data-chart-mastery]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            var gc = dark ? '#27272a' : '#f4f4f5';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: [{ name: 'Students Count', data: values }],
                                colors: ['#0284c7'],
                                stroke: { curve: 'smooth', width: 3 },
                                fill: {
                                    type: 'gradient',
                                    gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
                                },
                                markers: { size: 5, hover: { size: 7 } },
                                xaxis: {
                                    categories: labels,
                                    labels: {
                                        style: { colors: tc, fontSize: '10px', fontWeight: 600 },
                                        rotate: -15,
                                        trim: false
                                    },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                yaxis: {
                                    min: 0,
                                    tickAmount: 4,
                                    labels: {
                                        style: { colors: tc, fontSize: '11px' },
                                        formatter: function(val) { return Math.round(val); }
                                    }
                                },
                                grid: { borderColor: gc, strokeDashArray: 4 },
                                tooltip: { theme: dark ? 'dark' : 'light' }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart-mastery class="w-full h-72 min-h-[270px]"></div>
                </div>
            @else
                <div class="h-72 min-h-[270px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-chart-area text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No student mastery data available for the selected filter.</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Adviser Class Overview (Conditional if adviser) -->
    @if ($advisedSection)
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="size-9 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            Class Advisory Performance Summary: {{ $advisedSection->yearLevel?->name }} - {{ $advisedSection->section?->name }}
                        </h2>
                        <p class="text-[11px] text-zinc-500">Official advisee class standing across all academic subjects</p>
                    </div>
                </div>
                <a href="{{ route('teacher.advisory') }}" class="text-xs font-bold text-[#166534] dark:text-emerald-400 hover:underline flex items-center gap-1 self-start sm:self-auto">
                    <span>Open Class Roster & Consolidated Matrix</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700/60">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">Total Advisees</span>
                    <span class="text-lg font-extrabold text-zinc-900 dark:text-zinc-100">{{ $advisoryStats['totalStudents'] }} students</span>
                </div>
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700/60">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">Class General Average</span>
                    <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $advisoryStats['generalAverage'] ? number_format($advisoryStats['generalAverage'], 2) : 'N/A' }}
                    </span>
                </div>
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700/60 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-zinc-400 uppercase block">Achievers</span>
                        <span class="text-base font-extrabold text-amber-600 dark:text-amber-400 block">
                            🌟 {{ $advisoryStats['honorsCount'] }} {{ $advisoryStats['honorsCount'] === 1 ? 'Honors Achiever' : 'Honors Achievers' }}
                        </span>
                    </div>
                    @if (!empty($advisoryStats['bestSubjectsCount']))
                        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold block mt-1">
                            🏅 {{ $advisoryStats['bestSubjectsCount'] }} Best in Subject {{ $advisoryStats['bestSubjectsCount'] === 1 ? 'Award' : 'Awards' }}
                        </span>
                    @endif
                </div>
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700/60">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">At-Risk Learners</span>
                    <span class="text-lg font-extrabold {{ $advisoryStats['atRiskCount'] > 0 ? 'text-rose-600' : 'text-zinc-500' }}">
                        {{ $advisoryStats['atRiskCount'] }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Teaching Schedule / Assigned Subject Classes Grid -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden space-y-4 p-6">
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <div>
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-chalkboard text-[#166534]"></i>
                    <span>My Teaching Loads & Subject Classes</span>
                </h2>
                <p class="text-[11px] text-zinc-500">Active class assignments for School Year {{ $activeSy?->school_year }}</p>
            </div>
            <a href="{{ route('teacher.classes') }}" class="text-xs font-bold text-[#166534] dark:text-emerald-400 hover:underline">
                View All Loads
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($allTeachingLoads as $load)
                @php
                    $ylName = $load->schoolYearSection?->yearLevel?->name ?? 'Grade Level';
                    $secName = $load->schoolYearSection?->section?->name ?? 'Section';
                    $subName = $load->subject?->subject_name ?? 'Subject';
                    $subCode = $load->subject?->subject_code ?? 'SUBJ';
                    $enrolledCount = \App\Models\StudentEnrollment::where('school_year_section_id', $load->school_year_section_id)->count();
                @endphp
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700/60 flex flex-col justify-between space-y-3 hover:border-emerald-400 dark:hover:border-emerald-600 transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-100 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300">
                                {{ $subCode }}
                            </span>
                            <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mt-1">
                                {{ $subName }}
                            </h3>
                            <p class="text-xs text-zinc-500 font-medium">
                                {{ $ylName }} • Section {{ $secName }}
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-zinc-200/80 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200">
                            {{ $enrolledCount }} students
                        </span>
                    </div>

                    <div class="pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between">
                        <span class="text-[11px] text-zinc-400">Quarter Grading</span>
                        <a href="{{ route('teacher.grades', ['classId' => $load->id]) }}" class="px-3 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white rounded-lg font-bold text-[11px] shadow-sm transition flex items-center gap-1.5">
                            <i class="fas fa-file-pen text-[10px]"></i>
                            <span>Encode Grades</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-8 text-center text-zinc-400 italic">
                    <i class="fas fa-book-open text-3xl mb-2 text-zinc-300 block"></i>
                    No subject classes currently assigned for this school year.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <h2 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-[#166534]"></i>
                <span>My Recent Activity Logs</span>
            </h2>
            <span class="text-[10px] text-zinc-400">System audit trail</span>
        </div>

        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
            @forelse ($recentActivities as $act)
                <div class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="size-2 rounded-full bg-emerald-500 shrink-0"></div>
                        <div>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $act->action }}</span>
                            <p class="text-[11px] text-zinc-500">{{ $act->description }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] text-zinc-400 shrink-0">{{ $act->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-zinc-400 italic py-3 text-center text-xs">No recent activity logs recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>
