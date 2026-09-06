<div class="space-y-6 font-sans">
    <!-- Academic Context Selectors Bar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">
            <i class="fas fa-sliders text-sm text-[#166534]"></i>
            <span>Academic Context Filter:</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- School Year Dropdown -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-zinc-500 uppercase">School Year:</label>
                <select wire:model.live="selectedYearId" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}">SY {{ $sy->school_year }} {{ $sy->status === 'active' ? '(Active)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quarter Dropdown -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-zinc-500 uppercase">Quarter:</label>
                <select wire:model.live="selectedQuarterId" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    <option value="">All Quarters</option>
                    @foreach ($quarters as $q)
                        <option value="{{ $q->id }}">{{ $q->name }} {{ $q->status === 'active' ? '(Current)' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- 7 Summary Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- 1. Total Students -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Total Students</span>
                <i class="fas fa-user-graduate text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 block">{{ number_format($metrics['totalStudents']) }}</span>
            <span class="text-[10px] text-zinc-500 block">Enrolled JHS</span>
        </div>

        <!-- 2. Total Teachers -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Total Teachers</span>
                <i class="fas fa-chalkboard-user text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 block">{{ number_format($metrics['totalTeachers']) }}</span>
            <span class="text-[10px] text-zinc-500 block">Faculty Members</span>
        </div>

        <!-- 3. Total Parents -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Total Parents</span>
                <i class="fas fa-user-group text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 block">{{ number_format($metrics['totalParents']) }}</span>
            <span class="text-[10px] text-zinc-500 block">Linked Accounts</span>
        </div>

        <!-- 4. Active Sections -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Active Sections</span>
                <i class="fas fa-users-rectangle text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 block">{{ number_format($metrics['activeSections']) }}</span>
            <span class="text-[10px] text-zinc-500 block">Class Sections</span>
        </div>

        <!-- 5. Grade Completion -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Completion</span>
                <i class="fas fa-chart-pie text-xs text-blue-600"></i>
            </div>
            <span class="text-xl font-bold text-blue-700 dark:text-blue-400 block">{{ $metrics['gradeCompletion'] }}%</span>
            <span class="text-[10px] text-zinc-500 block">Grade Entries</span>
        </div>

        <!-- 6. Academic Average -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Academic Avg</span>
                <i class="fas fa-chart-line text-xs text-[#166534]"></i>
            </div>
            <span class="text-xl font-bold text-[#166534] dark:text-emerald-400 block">{{ $metrics['academicAverage'] }}%</span>
            <span class="text-[10px] text-zinc-500 block">General Average</span>
        </div>

        <!-- 7. Total Achievements -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-1.5">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-[10px] font-bold uppercase tracking-wider">Achievements</span>
                <i class="fas fa-award text-xs text-amber-500"></i>
            </div>
            <span class="text-xl font-bold text-amber-600 dark:text-amber-400 block">{{ number_format($metrics['totalAchievements']) }}</span>
            <span class="text-[10px] text-zinc-500 block">Student Achievers</span>
        </div>
    </div>

    <!-- 4 Performance Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart A: Average Performance by Year Level -->
        <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">A. Average Performance by Year Level</h3>
                <p class="text-[11px] text-zinc-500">Grade 7 to Grade 10 average academic ratings</p>
            </div>
            @if ($yearLevelChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var labels = @js($yearLevelChart['labels']);
                        var values = @js($yearLevelChart['values']);
                        function draw() {
                            var el = $el.querySelector('[data-chart]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            var gc = dark ? '#27272a' : '#f4f4f5';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: [{ name: 'Average Grade', data: values }],
                                colors: ['#166534'],
                                plotOptions: { bar: { borderRadius: 6, columnWidth: '38%', dataLabels: { position: 'top' } } },
                                dataLabels: { enabled: true, formatter: function(v){ return v > 0 ? v.toFixed(1) : ''; }, offsetY: -18, style: { fontSize: '11px', colors: [tc], fontWeight: 'bold' } },
                                xaxis: { categories: labels, labels: { style: { colors: tc, fontSize: '11px', fontWeight: 600 } }, axisBorder: { show: false }, axisTicks: { show: false } },
                                yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: tc, fontSize: '11px' } } },
                                grid: { borderColor: gc, strokeDashArray: 4 },
                                tooltip: { theme: dark ? 'dark' : 'light' }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart class="w-full h-64 min-h-[250px]"></div>
                </div>
            @else
                <div class="h-64 min-h-[250px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-folder-open text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No academic data available for the selected filter.</span>
                </div>
            @endif
        </div>

        <!-- Chart B: Quarterly Performance Trend -->
        <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">B. Quarterly Performance Trend</h3>
                <p class="text-[11px] text-zinc-500">Academic progression across 1st to 3rd Quarters</p>
            </div>
            @if ($quarterlyChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var labels = @js($quarterlyChart['labels']);
                        var values = @js($quarterlyChart['values']);
                        function draw() {
                            var el = $el.querySelector('[data-chart]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            var gc = dark ? '#27272a' : '#f4f4f5';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'area', height: 250, toolbar: { show: false }, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: [{ name: 'Quarter Average', data: values }],
                                colors: ['#2563eb'],
                                stroke: { curve: 'smooth', width: 3 },
                                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] } },
                                markers: { size: 5, hover: { size: 7 } },
                                xaxis: { categories: labels, labels: { style: { colors: tc, fontSize: '11px', fontWeight: 600 } }, axisBorder: { show: false }, axisTicks: { show: false } },
                                yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: tc, fontSize: '11px' } } },
                                grid: { borderColor: gc, strokeDashArray: 4 },
                                tooltip: { theme: dark ? 'dark' : 'light' }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart class="w-full h-64 min-h-[250px]"></div>
                </div>
            @else
                <div class="h-64 min-h-[250px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-chart-line text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No academic data available for the selected filter.</span>
                </div>
            @endif
        </div>

        <!-- Chart C: Subject Performance -->
        <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">C. Subject Performance Breakdown</h3>
                <p class="text-[11px] text-zinc-500">Average rating comparison across core subjects</p>
            </div>
            @if ($subjectChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var labels = @js($subjectChart['labels']);
                        var values = @js($subjectChart['values']);
                        function draw() {
                            var el = $el.querySelector('[data-chart]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            var gc = dark ? '#27272a' : '#f4f4f5';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: [{ name: 'Subject Average', data: values }],
                                colors: ['#0284c7'],
                                plotOptions: { bar: { borderRadius: 6, columnWidth: '45%', dataLabels: { position: 'top' } } },
                                dataLabels: { enabled: true, formatter: function(v){ return v > 0 ? v.toFixed(1) : ''; }, offsetY: -18, style: { fontSize: '10px', colors: [tc], fontWeight: 'bold' } },
                                xaxis: { categories: labels, labels: { style: { colors: tc, fontSize: '10px', fontWeight: 600 }, rotate: -25, rotateAlways: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                                yaxis: { min: 60, max: 100, tickAmount: 4, labels: { style: { colors: tc, fontSize: '11px' } } },
                                grid: { borderColor: gc, strokeDashArray: 4 },
                                tooltip: { theme: dark ? 'dark' : 'light' }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart class="w-full h-64 min-h-[250px]"></div>
                </div>
            @else
                <div class="h-64 min-h-[250px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-book text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No academic data available for the selected filter.</span>
                </div>
            @endif
        </div>

        <!-- Chart D: Achievement Distribution -->
        <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">D. Achievement Distribution</h3>
                    <p class="text-[11px] text-zinc-500">Student honor tier breakdown</p>
                </div>
                @if ($achievementChart['hasData'])
                    <span class="text-[10px] font-bold text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-full">
                        {{ $achievementChart['totalStudents'] }} students
                    </span>
                @endif
            </div>
            @if ($achievementChart['hasData'])
                <div wire:ignore
                    x-data="{}"
                    x-init="(function() {
                        var series = @js($achievementChart['values']);
                        var labels = @js($achievementChart['labels']);
                        var total  = @js($achievementChart['totalStudents']);
                        function draw() {
                            var el = $el.querySelector('[data-chart]');
                            if (!el || typeof ApexCharts === 'undefined') { setTimeout(draw, 80); return; }
                            if (el._apexChart) { try { el._apexChart.destroy(); } catch(e){} }
                            var dark = document.documentElement.classList.contains('dark');
                            var tc = dark ? '#a1a1aa' : '#52525b';
                            el._apexChart = new ApexCharts(el, {
                                chart: { type: 'donut', height: 250, background: 'transparent' },
                                theme: { mode: dark ? 'dark' : 'light' },
                                series: series,
                                labels: labels,
                                colors: ['#10b981', '#3b82f6', '#f59e0b', '#a1a1aa'],
                                legend: {
                                    position: 'bottom',
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    labels: { colors: tc },
                                    itemMargin: { horizontal: 6, vertical: 2 }
                                },
                                stroke: { width: 2, colors: [dark ? '#18181b' : '#ffffff'] },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '65%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: 'Total',
                                                    fontSize: '11px',
                                                    fontWeight: 700,
                                                    color: tc,
                                                    formatter: function() { return total; }
                                                },
                                                value: {
                                                    fontSize: '18px',
                                                    fontWeight: 700,
                                                    color: dark ? '#f4f4f5' : '#18181b'
                                                }
                                            }
                                        }
                                    }
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val, opts) {
                                        return opts.w.globals.series[opts.seriesIndex] > 0
                                            ? opts.w.globals.series[opts.seriesIndex] + ' (' + Math.round(val) + '%)'
                                            : '';
                                    },
                                    style: { fontSize: '10px', fontWeight: 700, colors: ['#fff'] },
                                    dropShadow: { enabled: false }
                                },
                                tooltip: {
                                    theme: dark ? 'dark' : 'light',
                                    y: {
                                        formatter: function(val) {
                                            var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                            return val + ' students (' + pct + '%)';
                                        }
                                    }
                                }
                            });
                            el._apexChart.render();
                        }
                        draw();
                    })();">
                    <div data-chart class="w-full h-64 min-h-[250px]"></div>
                </div>

                {{-- Honor tier mini legend with counts --}}
                <div class="grid grid-cols-2 gap-2 pt-1">
                    @php
                        $honorLabels = $achievementChart['labels'];
                        $honorValues = $achievementChart['values'];
                        $honorColors = ['text-emerald-600 dark:text-emerald-400', 'text-blue-600 dark:text-blue-400', 'text-amber-500 dark:text-amber-400', 'text-zinc-400'];
                        $honorIcons  = ['fas fa-star', 'fas fa-star-half-stroke', 'fas fa-medal', 'fas fa-user'];
                        $total = $achievementChart['totalStudents'];
                    @endphp
                    @foreach ($honorLabels as $i => $lbl)
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700">
                            <i class="{{ $honorIcons[$i] }} text-xs {{ $honorColors[$i] }}"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-zinc-700 dark:text-zinc-300 truncate">{{ $lbl }}</p>
                                <p class="text-[11px] font-extrabold {{ $honorColors[$i] }}">
                                    {{ $honorValues[$i] }}
                                    <span class="text-[9px] text-zinc-400 font-medium">
                                        ({{ $total > 0 ? round(($honorValues[$i] / $total) * 100) : 0 }}%)
                                    </span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-64 min-h-[250px] flex flex-col items-center justify-center text-center p-6 text-zinc-400 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-award text-2xl mb-2 text-zinc-300"></i>
                    <span class="text-xs font-medium">No grade data found for the selected school year / quarter.</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent System Activity Logs -->
    <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-[#166534]"></i>
                    <span>Recent System Activity</span>
                </h3>
                <p class="text-[11px] text-zinc-500">Audit trail of recent administrative actions</p>
            </div>
            <a href="{{ route('admin.activity-logs') }}" class="text-[11px] font-bold text-[#166534] hover:underline">View All Logs →</a>
        </div>

        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
            @forelse ($recentActivities as $log)
                <div class="py-3 flex items-center justify-between gap-4 font-medium">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-xs">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="text-zinc-500 text-[11px] block">{{ $log->action }}: {{ $log->description }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-zinc-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="p-6 text-center text-zinc-400 italic text-xs">
                    No recent system activity logged.
                </div>
            @endforelse
        </div>
    </div>
</div>
