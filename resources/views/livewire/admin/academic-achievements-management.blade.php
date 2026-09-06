<div class="space-y-5 font-sans">

    {{-- ============================================================ --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-zinc-900 px-6 py-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-award text-amber-500"></i>
                <span>Student Achievements</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Automated student achievement tracking, honor recognition, and excellence awards.</p>
        </div>

        {{-- Horizontal Tab Navigation --}}
        <div class="flex items-center bg-zinc-100 dark:bg-zinc-800 p-1 rounded-xl gap-1">
            <button wire:click="$set('activeTab', 'list')"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200
                    {{ $activeTab === 'list'
                        ? 'bg-white dark:bg-zinc-900 text-amber-600 dark:text-amber-400 shadow-sm border border-zinc-200 dark:border-zinc-700'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <i class="fas fa-list-ol text-[11px]"></i>
                <span>Student Achievements</span>
            </button>
            <button wire:click="$set('activeTab', 'analytics')"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200
                    {{ $activeTab === 'analytics'
                        ? 'bg-white dark:bg-zinc-900 text-amber-600 dark:text-amber-400 shadow-sm border border-zinc-200 dark:border-zinc-700'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <i class="fas fa-chart-line text-[11px]"></i>
                <span>Achievements Analytics</span>
            </button>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: STUDENT ACHIEVEMENTS LIST --}}
    {{-- ============================================================ --}}
    @if ($activeTab === 'list')

        {{-- Filters Toolbar --}}
        <div class="bg-white dark:bg-zinc-900 px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-500 uppercase text-[10px]">School Year:</label>
                <select wire:model.live="selectedSchoolYearId" class="px-3 py-1.5 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}">SY {{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-500 uppercase text-[10px]">Quarter:</label>
                <select wire:model.live="selectedQuarterId" class="px-3 py-1.5 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">Cumulative (All Quarters)</option>
                    @foreach ($quarters as $q)
                        <option value="{{ $q->id }}">{{ $q->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-500 uppercase text-[10px]">Grade Level:</label>
                <select wire:model.live="selectedYearLevelId" class="px-3 py-1.5 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Grade Levels</option>
                    @foreach ($yearLevels as $yl)
                        <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-500 uppercase text-[10px]">Section:</label>
                <select wire:model.live="selectedSectionId" class="px-3 py-1.5 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Sections</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-bold text-zinc-500 uppercase text-[10px]">Award Filter:</label>
                <select wire:model.live="filterHonorCategory" class="px-3 py-1.5 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="all">All Academic Awards</option>
                    <option value="highest">With Highest Honors</option>
                    <option value="high">With High Honors</option>
                    <option value="honors">With Honors</option>
                    <optgroup label="Best in Subject Awards">
                        @foreach ($subjects as $sub)
                            <option value="best_{{ $sub->subject_name }}">Best in {{ $sub->subject_name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </div>

        {{-- Achievers Table --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-list-ol text-amber-500"></i>
                    <span>Official Academic Achievers List</span>
                </h3>
                <span class="text-xs font-bold text-zinc-500">Total Achievers: {{ count($candidates) }} Student(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4 text-start">Achievements</th>
                            <th class="py-3 px-4 text-center">General Average</th>
                            <th class="py-3 px-4 text-start">Grade Level &amp; Section</th>
                            <th class="py-3 px-4 text-start">Student</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                        @forelse ($candidates as $cand)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3.5 px-4 text-start">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($cand['achievements'] as $ach)
                                            @if ($ach === 'With Highest Honors')
                                                <span class="px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-[11px] border border-emerald-300 inline-flex items-center gap-1">
                                                    <i class="fas fa-crown text-amber-500"></i> {{ $ach }}
                                                </span>
                                            @elseif ($ach === 'With High Honors')
                                                <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 font-bold text-[11px] border border-blue-200 inline-flex items-center gap-1">
                                                    <i class="fas fa-star text-blue-500"></i> {{ $ach }}
                                                </span>
                                            @elseif ($ach === 'With Honors')
                                                <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-bold text-[11px] border border-amber-200 inline-flex items-center gap-1">
                                                    <i class="fas fa-award text-amber-600"></i> {{ $ach }}
                                                </span>
                                            @elseif (str_contains($ach, 'Best in'))
                                                <span class="px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold text-[11px] border border-indigo-200 dark:border-indigo-800 inline-flex items-center gap-1">
                                                    <i class="fas fa-medal text-indigo-500"></i> {{ $ach }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold">{{ $ach }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-sm text-[#166534]">
                                    {{ number_format($cand['average'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-start text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $cand['year_level'] }} - {{ $cand['section'] }} <span class="text-zinc-400 font-mono text-[10px]">(SY {{ $cand['school_year'] }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-start">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-xs">{{ $cand['student_name'] }}</span>
                                    <span class="font-mono text-[10px] text-[#166534] block">{{ $cand['student_number'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-zinc-400 italic">
                                    <i class="fas fa-award text-2xl mb-2 text-zinc-300 block"></i>
                                    No honor roll candidates qualified for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    {{-- ============================================================ --}}
    {{-- TAB: ACHIEVEMENTS ANALYTICS --}}
    {{-- ============================================================ --}}
    @if ($activeTab === 'analytics')

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $kpiCards = [
                    ['label' => 'Total Achievers', 'value' => $kpiTotal, 'icon' => 'fa-users', 'color' => 'amber'],
                    ['label' => 'With Highest Honors', 'value' => $kpiHighest, 'icon' => 'fa-crown', 'color' => 'emerald'],
                    ['label' => 'With High Honors', 'value' => $kpiHigh, 'icon' => 'fa-star', 'color' => 'blue'],
                    ['label' => 'With Honors', 'value' => $kpiHonors, 'icon' => 'fa-award', 'color' => 'violet'],
                    ['label' => 'Best in Subject', 'value' => $kpiBestSubj, 'icon' => 'fa-medal', 'color' => 'indigo'],
                ];
                $colorMap = [
                    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-950/30',   'text' => 'text-amber-600 dark:text-amber-400',   'icon' => 'text-amber-500',   'border' => 'border-amber-200 dark:border-amber-800/50'],
                    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'icon' => 'text-emerald-500', 'border' => 'border-emerald-200 dark:border-emerald-800/50'],
                    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-950/30',    'text' => 'text-blue-700 dark:text-blue-400',    'icon' => 'text-blue-500',    'border' => 'border-blue-200 dark:border-blue-800/50'],
                    'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-950/30',  'text' => 'text-violet-700 dark:text-violet-400',  'icon' => 'text-violet-500',  'border' => 'border-violet-200 dark:border-violet-800/50'],
                    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-950/30',  'text' => 'text-indigo-700 dark:text-indigo-400',  'icon' => 'text-indigo-500',  'border' => 'border-indigo-200 dark:border-indigo-800/50'],
                ];
            @endphp
            @foreach ($kpiCards as $card)
                @php $c = $colorMap[$card['color']]; @endphp
                <div class="bg-white dark:bg-zinc-900 rounded-xl border {{ $c['border'] }} shadow-sm px-4 py-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $card['icon'] }} text-sm {{ $c['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xl font-extrabold {{ $c['text'] }} leading-tight">{{ $card['value'] }}</div>
                        <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-wide leading-tight mt-0.5">{{ $card['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts Row: Total Achievers Trend + Grade Level Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Line Chart 1: Total Achievers per School Year --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-chart-line text-amber-500 text-xs"></i>
                            Honor Roll Trend
                        </h3>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Total achievers per honor level across school years</p>
                    </div>
                </div>
                <div wire:ignore
                    x-data="{}"
                    x-init="
                        (function() {
                            var syLabels = @js($syLabels);
                            var honorData = @js($honorTrendData);
                            var hColors = {
                                'With Highest Honors': ['#10b981','rgba(16,185,129,0.10)'],
                                'With High Honors':    ['#3b82f6','rgba(59,130,246,0.10)'],
                                'With Honors':         ['#f59e0b','rgba(245,158,11,0.10)']
                            };
                            function draw() {
                                var el = $el.querySelector('canvas');
                                if (!el || typeof Chart === 'undefined') { setTimeout(draw, 80); return; }
                                var ex = Chart.getChart(el); if (ex) ex.destroy();
                                var dark = document.documentElement.classList.contains('dark');
                                new Chart(el, {
                                    type: 'line',
                                    data: {
                                        labels: syLabels,
                                        datasets: Object.keys(honorData).map(function(t) {
                                            var c = hColors[t] || ['#a78bfa','rgba(167,139,250,0.10)'];
                                            return { label: t, data: syLabels.map(function(s){ return honorData[t][s]||0; }),
                                                borderColor:c[0], backgroundColor:c[1], borderWidth:2.5, fill:true, tension:0.38,
                                                pointRadius:5, pointHoverRadius:7, pointBackgroundColor:c[0] };
                                        })
                                    },
                                    options: {
                                        responsive:true, maintainAspectRatio:true,
                                        interaction:{ mode:'index', intersect:false },
                                        animation:{ duration:600 },
                                        plugins:{ legend:{ position:'top', labels:{ color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}, usePointStyle:true, pointStyle:'circle', boxWidth:10, boxHeight:10 } },
                                            tooltip:{ backgroundColor: dark?'#18181b':'#fff', borderColor: dark?'#3f3f46':'#e4e4e7', borderWidth:1, titleColor: dark?'#f4f4f5':'#18181b', bodyColor: dark?'#a1a1aa':'#71717a', callbacks:{ title:function(i){ return 'SY '+i[0].label; } } } },
                                        scales:{ x:{ grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}} },
                                                 y:{ grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11}, precision:0}, beginAtZero:true } }
                                    }
                                });
                            }
                            if (typeof Chart !== 'undefined') { draw(); }
                            else {
                                var s = document.createElement('script');
                                s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js';
                                s.onload = draw; document.head.appendChild(s);
                            }
                        })();
                    ">
                    <canvas style="max-height:260px"></canvas>
                </div>
            </div>

            {{-- Line Chart 2: Achievers by Grade Level --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-chart-line text-blue-500 text-xs"></i>
                            Achievers by Grade Level
                        </h3>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Honor students per grade level across school years</p>
                    </div>
                </div>
                <div wire:ignore
                    x-data="{}"
                    x-init="
                        (function() {
                            var syLabels = @js($syLabels);
                            var glData   = @js($gradeLevelTrendData);
                            var glC = ['#f59e0b','#3b82f6','#10b981','#8b5cf6'];
                            var glB = ['rgba(245,158,11,0.10)','rgba(59,130,246,0.10)','rgba(16,185,129,0.10)','rgba(139,92,246,0.10)'];
                            function draw() {
                                var el = $el.querySelector('canvas');
                                if (!el || typeof Chart === 'undefined') { setTimeout(draw, 80); return; }
                                var ex = Chart.getChart(el); if (ex) ex.destroy();
                                var dark = document.documentElement.classList.contains('dark');
                                var idx = 0;
                                new Chart(el, {
                                    type: 'line',
                                    data: {
                                        labels: syLabels,
                                        datasets: Object.keys(glData).map(function(yl) {
                                            var i = idx++;
                                            return { label: yl, data: syLabels.map(function(s){ return glData[yl][s]||0; }),
                                                borderColor:glC[i%glC.length], backgroundColor:glB[i%glB.length], borderWidth:2.5, fill:true, tension:0.38,
                                                pointRadius:5, pointHoverRadius:7, pointBackgroundColor:glC[i%glC.length] };
                                        })
                                    },
                                    options: {
                                        responsive:true, maintainAspectRatio:true,
                                        interaction:{ mode:'index', intersect:false },
                                        animation:{ duration:600 },
                                        plugins:{ legend:{ position:'top', labels:{ color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}, usePointStyle:true, pointStyle:'circle', boxWidth:10, boxHeight:10 } },
                                            tooltip:{ backgroundColor: dark?'#18181b':'#fff', borderColor: dark?'#3f3f46':'#e4e4e7', borderWidth:1, titleColor: dark?'#f4f4f5':'#18181b', bodyColor: dark?'#a1a1aa':'#71717a', callbacks:{ title:function(i){ return 'SY '+i[0].label; } } } },
                                        scales:{ x:{ grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}} },
                                                 y:{ grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11}, precision:0}, beginAtZero:true } }
                                    }
                                });
                            }
                            if (typeof Chart !== 'undefined') { draw(); } else { setTimeout(draw, 80); }
                        })();
                    ">
                    <canvas style="max-height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- Charts Row: Average GPA Trend + Subject Awards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Line Chart 3: Average GPA Trend of Achievers --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-chart-line text-emerald-500 text-xs"></i>
                            Average GPA Trend
                        </h3>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Mean general average of honor students over time</p>
                    </div>
                </div>
                <div wire:ignore
                    x-data="{}"
                    x-init="
                        (function() {
                            var syLabels = @js($syLabels);
                            var gpaData  = @js($avgGpaTrendLine);
                            function draw() {
                                var el = $el.querySelector('canvas');
                                if (!el || typeof Chart === 'undefined') { setTimeout(draw, 80); return; }
                                var ex = Chart.getChart(el); if (ex) ex.destroy();
                                var dark = document.documentElement.classList.contains('dark');
                                new Chart(el, {
                                    type: 'line',
                                    data: {
                                        labels: syLabels,
                                        datasets: [{ label:'Avg. General Average',
                                            data: syLabels.map(function(s){ return gpaData[s]!=null ? gpaData[s] : null; }),
                                            borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.12)', borderWidth:2.5,
                                            pointRadius:6, pointHoverRadius:8, pointBackgroundColor:'#10b981',
                                            fill:true, tension:0.38, spanGaps:true }]
                                    },
                                    options: {
                                        responsive:true, maintainAspectRatio:true,
                                        interaction:{ mode:'index', intersect:false },
                                        animation:{ duration:600 },
                                        plugins:{ legend:{ position:'top', labels:{ color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}, usePointStyle:true, pointStyle:'circle', boxWidth:10, boxHeight:10 } },
                                            tooltip:{ backgroundColor: dark?'#18181b':'#fff', borderColor: dark?'#3f3f46':'#e4e4e7', borderWidth:1, titleColor: dark?'#f4f4f5':'#18181b', bodyColor: dark?'#a1a1aa':'#71717a',
                                                callbacks:{ title:function(i){ return 'SY '+i[0].label; }, label:function(i){ return ' Avg. GPA: '+(i.raw!==null?Number(i.raw).toFixed(2):'N/A'); } } } },
                                        scales:{ x:{ grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11,weight:'600'}} },
                                                 y:{ min:85, max:100, grid:{color: dark?'rgba(255,255,255,0.07)':'rgba(0,0,0,0.06)'}, ticks:{color: dark?'#a1a1aa':'#71717a', font:{size:11}, stepSize:1} } }
                                    }
                                });
                            }
                            if (typeof Chart !== 'undefined') { draw(); } else { setTimeout(draw, 80); }
                        })();
                    ">
                    <canvas style="max-height:260px"></canvas>
                </div>
            </div>

            {{-- Subject Awards Leaderboard --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-medal text-indigo-500 text-xs"></i>
                        Best in Subject — Leaderboard
                    </h3>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Total students who earned Best in Subject awards (≥ 95)</p>
                </div>
                <div class="space-y-2.5">
                    @php $maxSubj = max(array_values($subjectAwardCounts) ?: [1]); $rank = 0; @endphp
                    @foreach ($subjectAwardCounts as $subName => $cnt)
                        @php $rank++; $pct = $maxSubj > 0 ? round(($cnt / $maxSubj) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-bold text-zinc-400 w-4 text-right">{{ $rank }}</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $subName }}</span>
                                    <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">{{ $cnt }}</span>
                                </div>
                                <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-indigo-600 transition-all duration-500"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if (array_sum($subjectAwardCounts) === 0)
                        <p class="text-xs text-zinc-400 italic text-center py-4">No Best in Subject awards recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>


    @endif

</div>


