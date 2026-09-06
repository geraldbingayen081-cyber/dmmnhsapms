<div class="space-y-6 font-sans">
    <!-- Header Banner -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[11px] font-bold mb-2">
                    <i class="fas fa-medal"></i>
                    <span>DepEd Academic Excellence Awards • SY {{ $activeSy?->school_year ?? '2026-2027' }}</span>
                </div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    My Awards and Achievements Wall
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Official quarterly honors and subject citations based on DepEd Order No. 36, s. 2016.
                </p>
            </div>

            <!-- Quarter Filter & Total Pill -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-zinc-500 font-medium">Filter by:</span>
                    <select wire:model.live="selectedQuarter" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="all">All Quarters ({{ $totalAwardsCount }})</option>
                        @foreach ($quarters as $q)
                            <option value="{{ $q->id }}">{{ $q->name }} ({{ $q->short_name }})</option>
                        @endforeach
                    </select>
                </div>

                <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3.5 py-1.5 rounded-full shrink-0">
                    🌟 {{ count($awardsList) }} {{ count($awardsList) === 1 ? 'Award' : 'Awards' }}
                </span>
            </div>
        </div>

        <!-- Meta Strip -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-user-graduate text-emerald-600"></i>
                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $student?->full_name }}</span>
            </span>
            <span>•</span>
            <span class="flex items-center gap-1.5">
                <i class="fas fa-id-card text-zinc-400"></i>
                <span class="font-mono">{{ $student?->student_number }}</span>
            </span>
            <span>•</span>
            <span class="flex items-center gap-1.5">
                <i class="fas fa-calendar-check text-amber-500"></i>
                <span>Quarterly Recognition Scope: Q1 to Q3</span>
            </span>
        </div>
    </div>

    <!-- Awards Cards Grid with Prominent Quarter Badges -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($awardsList as $aw)
            <div class="p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-amber-400 dark:hover:border-amber-600 transition space-y-4 flex flex-col justify-between shadow-xs">
                <div class="space-y-3">
                    <!-- Top Ribbon: Quarter Badge & Award Type -->
                    <div class="flex items-center justify-between gap-2">
                        <!-- Quarter Badge -->
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black tracking-wider uppercase bg-emerald-100 dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1.5 shadow-2xs">
                            <i class="fas fa-calendar-day text-emerald-600 dark:text-emerald-400"></i>
                            <span>{{ $aw['quarter'] }}</span>
                        </span>

                        <!-- Type Pill -->
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                            {{ $aw['type'] === 'honor' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($aw['type'] === 'subject' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-zinc-100 text-zinc-700') }}">
                            {{ $aw['type'] === 'honor' ? 'Academic Honor' : ($aw['type'] === 'subject' ? 'Subject Excellence' : 'Special Award') }}
                        </span>
                    </div>

                    <!-- Title & Icon -->
                    <div class="flex items-start gap-3">
                        <div class="size-11 rounded-xl {{ $aw['type'] === 'honor' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-200' : ($aw['type'] === 'subject' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }} flex items-center justify-center text-lg font-black shrink-0">
                            @if ($aw['type'] === 'honor')
                                <i class="fas fa-crown"></i>
                            @elseif ($aw['type'] === 'subject')
                                <i class="fas fa-medal"></i>
                            @else
                                <i class="fas fa-certificate"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-black text-zinc-900 dark:text-zinc-100 text-base leading-snug">
                                {{ $aw['title'] }}
                            </h4>
                            <span class="text-[11px] font-bold text-zinc-500 block mt-0.5">
                                Awarded in: <strong class="text-zinc-800 dark:text-zinc-200">{{ $aw['quarter'] }}</strong>
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-zinc-500 leading-relaxed">
                        {{ $aw['description'] }}
                    </p>
                </div>

                <!-- Footer details: Quarter mark or Awarded Date -->
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold">
                    @if (isset($aw['grade']))
                        <span class="text-zinc-400 text-[11px]">{{ $aw['quarter'] }} Rating:</span>
                        <span class="font-mono text-emerald-700 dark:text-emerald-400 text-sm font-black">{{ number_format($aw['grade'], 2) }}</span>
                    @elseif (isset($aw['date']))
                        <span class="text-zinc-400 text-[11px]">Awarded Date:</span>
                        <span class="font-mono text-zinc-700 dark:text-zinc-300 text-xs">{{ $aw['date'] }}</span>
                    @else
                        <span class="text-emerald-600 text-xs flex items-center gap-1">
                            <i class="fas fa-check-circle"></i> Official Recognition
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center text-zinc-400 italic bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-700">
                <i class="fas fa-award text-3xl mb-2 text-zinc-300 block"></i>
                No academic awards found for the selected quarter filter.
            </div>
        @endforelse
    </div>
</div>
