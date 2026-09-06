<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto font-sans">
    <!-- Student Banner Header -->
    <div class="bg-gradient-to-r from-zinc-900 via-sky-950 to-zinc-900 p-6 md:p-8 rounded-3xl border border-sky-900/50 shadow-xl text-white flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 border border-sky-400/30 text-xs font-bold">
                <span>🎓 Student Performance Dashboard</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Welcome back, {{ $student?->first_name }}!
            </h1>
            <p class="text-xs text-zinc-300 font-medium">
                Don Mariano Marcos National High School • {{ $student?->section?->name }} (Adviser: {{ $student?->section?->adviser?->name ?? 'N/A' }})
            </p>
        </div>

        <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 text-center min-w-40">
            <span class="text-[10px] font-bold text-sky-200 uppercase tracking-wider block">My General Average</span>
            <span class="text-3xl font-black text-emerald-400 tracking-tight block">{{ $ga ? $ga.'%' : 'N/A' }}</span>
            @if ($student?->honor_standing)
                <span class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-zinc-950">
                    🌟 {{ $student->honor_standing }}
                </span>
            @endif
        </div>
    </div>

    <!-- Personal Analytics Charts (2 Clean Charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Personal 3-Quarter Growth Line Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">My 3-Quarter Academic Trajectory</h3>
                <p class="text-xs text-zinc-400">Personal quarterly average from Q1 to Q3</p>
            </div>
            <div id="chart-student-quarter" class="w-full h-64"></div>
        </div>

        <!-- Personal Subject Performance Bar Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">My Subject Ratings Breakdown</h3>
                <p class="text-xs text-zinc-400">Comparing your grades across all core JHS subjects</p>
            </div>
            <div id="chart-student-subjects" class="w-full h-64"></div>
        </div>
    </div>

    <!-- Achievements & Honor Badges Feed -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <h3 class="text-base font-bold text-amber-500 flex items-center gap-2">
                <span>🏆 My Achievements & Awards Showcase</span>
            </h3>
            <p class="text-xs text-zinc-400">Recognitions awarded for academic excellence</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            @forelse ($achievements as $ach)
                <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/60 space-y-1">
                    <span class="font-black text-zinc-900 dark:text-zinc-100 text-sm block">{{ $ach->title }}</span>
                    <span class="text-amber-700 dark:text-amber-300 font-bold block text-[11px]">{{ $ach->type }} ({{ $ach->quarter }})</span>
                    <p class="text-zinc-500 text-[11px] mt-1">{{ $ach->remarks }}</p>
                </div>
            @empty
                <div class="col-span-full p-6 text-center text-zinc-400 italic">No academic awards logged yet for this school year. Keep up the good work!</div>
            @endforelse
        </div>
    </div>

    <!-- Student Charts Initialization Script -->
    <script>
        document.addEventListener('livewire:navigated', function () {
            initStudentCharts();
        });
        document.addEventListener('DOMContentLoaded', function () {
            initStudentCharts();
        });

        function initStudentCharts() {
            // Chart 1: Quarter Trend
            const quarterOptions = {
                chart: { type: 'line', height: 250, toolbar: { show: false } },
                series: [{
                    name: 'My Average',
                    data: [{{ $q1 }}, {{ $q2 }}, {{ $q3 }}]
                }],
                colors: ['#0284c7'],
                stroke: { curve: 'smooth', width: 4 },
                xaxis: { categories: ['Quarter 1', 'Quarter 2', 'Quarter 3'] },
                yaxis: { min: 70, max: 100 }
            };
            new ApexCharts(document.querySelector("#chart-student-quarter"), quarterOptions).render();

            // Chart 2: Subject Breakdown
            const subjectOptions = {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [{
                    name: 'Grade',
                    data: [
                        @foreach ($personalSubjects as $ps)
                            {{ $ps['grade'] }},
                        @endforeach
                    ]
                }],
                colors: ['#10b981'],
                plotOptions: { bar: { borderRadius: 8, horizontal: true } },
                xaxis: {
                    categories: [
                        @foreach ($personalSubjects as $ps)
                            '{{ $ps['name'] }}',
                        @endforeach
                    ],
                    min: 70, max: 100
                }
            };
            new ApexCharts(document.querySelector("#chart-student-subjects"), subjectOptions).render();
        }
    </script>
</div>
