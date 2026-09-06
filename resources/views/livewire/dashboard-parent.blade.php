<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto font-sans">
    <!-- Parent Banner Header -->
    <div class="bg-gradient-to-r from-zinc-900 via-sky-950 to-zinc-900 p-6 md:p-8 rounded-3xl border border-sky-900/50 shadow-xl text-white flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 border border-sky-400/30 text-xs font-bold">
                <span>👨‍👩‍👧 Parent Academic Portal</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Child Academic Progress Portal
            </h1>
            <p class="text-xs text-zinc-300 font-medium">
                Don Mariano Marcos National High School • Real-time 3-quarter grades and subject performance for your child.
            </p>
        </div>

        @if ($children->count() > 1)
            <!-- Multi-Child Selector -->
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md p-2 rounded-2xl border border-white/15">
                <span class="text-[11px] font-bold text-sky-200 px-2">Child:</span>
                @foreach ($children as $ch)
                    <button wire:click="$set('selectedChildId', {{ $ch->id }})" class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition {{ $activeChild && $activeChild->id === $ch->id ? 'bg-sky-500 text-white shadow-md' : 'text-zinc-300 hover:text-white' }}">
                        {{ $ch->first_name }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if ($activeChild)
        <!-- Child Summary Header -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xl">
                    🎓
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider block">Child Profile</span>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $activeChild->full_name }}</h3>
                    <span class="text-xs text-sky-600 dark:text-sky-400 font-mono font-bold">{{ $activeChild->custom_student_id }} • {{ $activeChild->section?->name }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl">
                    📈
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider block">General Average</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $childGA ? $childGA.'%' : 'N/A' }}</span>
                    <span class="text-[11px] text-zinc-500 block">SY {{ $currentSy?->year }} Average</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-amber-50 dark:bg-amber-950 text-amber-500 flex items-center justify-center font-bold text-xl">
                    🌟
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider block">Academic Standing</span>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 block">{{ $activeChild->honor_standing ?? 'Satisfactory Standing' }}</span>
                    <span class="text-[11px] text-zinc-500 block">DepEd Standard</span>
                </div>
            </div>
        </div>

        <!-- Personal Analytics Charts for Child -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Child Quarterly Trend -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $activeChild->first_name }}'s 3-Quarter Trajectory</h3>
                    <p class="text-xs text-zinc-400">Quarterly average progression from Q1 to Q3</p>
                </div>
                <div id="chart-parent-quarter" class="w-full h-64"></div>
            </div>

            <!-- Child Subject Breakdown -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $activeChild->first_name }}'s Subject Ratings</h3>
                    <p class="text-xs text-zinc-400">Subject average ratings across core JHS subjects</p>
                </div>
                <div id="chart-parent-subjects" class="w-full h-64"></div>
            </div>
        </div>

        <!-- Achievements Feed -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-base font-bold text-amber-500 flex items-center gap-2">
                    <span>🏆 {{ $activeChild->first_name }}'s Achievements & Awards</span>
                </h3>
                <p class="text-xs text-zinc-400">Recognitions logged for the current school year</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                @forelse ($activeChild->achievements as $ach)
                    <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/60 space-y-1">
                        <span class="font-black text-zinc-900 dark:text-zinc-100 text-sm block">{{ $ach->title }}</span>
                        <span class="text-amber-700 dark:text-amber-300 font-bold block text-[11px]">{{ $ach->type }} ({{ $ach->quarter }})</span>
                        <p class="text-zinc-500 text-[11px] mt-1">{{ $ach->remarks }}</p>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-zinc-400 italic">No awards logged yet for this school year.</div>
                @endforelse
            </div>
        </div>

        <!-- Parent Script -->
        <script>
            document.addEventListener('livewire:navigated', function () {
                initParentCharts();
            });
            document.addEventListener('DOMContentLoaded', function () {
                initParentCharts();
            });

            function initParentCharts() {
                const quarterOptions = {
                    chart: { type: 'line', height: 250, toolbar: { show: false } },
                    series: [{
                        name: 'Average',
                        data: [{{ $childQ1 }}, {{ $childQ2 }}, {{ $childQ3 }}]
                    }],
                    colors: ['#0284c7'],
                    stroke: { curve: 'smooth', width: 4 },
                    xaxis: { categories: ['Quarter 1', 'Quarter 2', 'Quarter 3'] },
                    yaxis: { min: 70, max: 100 }
                };
                new ApexCharts(document.querySelector("#chart-parent-quarter"), quarterOptions).render();

                const subjectOptions = {
                    chart: { type: 'bar', height: 250, toolbar: { show: false } },
                    series: [{
                        name: 'Grade',
                        data: [
                            @foreach ($childSubjects as $cs)
                                {{ $cs['grade'] }},
                            @endforeach
                        ]
                    }],
                    colors: ['#10b981'],
                    plotOptions: { bar: { borderRadius: 8, horizontal: true } },
                    xaxis: {
                        categories: [
                            @foreach ($childSubjects as $cs)
                                '{{ $cs['name'] }}',
                            @endforeach
                        ],
                        min: 70, max: 100
                    }
                };
                new ApexCharts(document.querySelector("#chart-parent-subjects"), subjectOptions).render();
            }
        </script>
    @else
        <div class="p-12 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 text-zinc-400 font-medium">
            No children are currently linked to your parent account.
        </div>
    @endif
</div>
