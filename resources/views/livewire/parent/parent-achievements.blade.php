<div class="space-y-6 font-sans">
    <!-- Child Selector if Multiple Children -->
    @if ($children->count() > 1)
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm flex items-center justify-between">
            <span class="text-xs font-bold text-zinc-500">Select Child for Achievements:</span>
            <div class="flex items-center gap-2">
                @foreach ($children as $ch)
                    @php $isSelected = (int)$ch->id === (int)$selectedStudentId; @endphp
                    <button wire:click="selectChild({{ $ch->id }})" class="px-3 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer {{ $isSelected ? 'bg-[#166534] text-white border-emerald-700 shadow-sm' : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 border-zinc-200 dark:border-zinc-700' }}">
                        {{ $ch->full_name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($selectedStudent)
        <!-- Header & Filter Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[11px] font-bold mb-2">
                        <i class="fas fa-award"></i>
                        <span>Honors & Recognitions • SY {{ $activeSy?->school_year ?? '2026-2027' }}</span>
                    </div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $selectedStudent->full_name }}'s Awards and Achievements
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Quarterly DepEd academic honors and subject excellence recognitions.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-bold text-zinc-500">Quarter:</span>
                        <select wire:model.live="selectedQuarter" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="all">All Quarters (Q1–Q3)</option>
                            @foreach ($quarters as $q)
                                <option value="{{ $q->id }}">{{ $q->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <span class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-bold">
                        {{ $totalAwardsCount }} Total
                    </span>
                </div>
            </div>

            <!-- Learner Snapshot Strip -->
            <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                <span><strong>LRN:</strong> <span class="font-mono text-[#166534] dark:text-emerald-400 font-bold">{{ $selectedStudent->student_number }}</span></span>
                <span>•</span>
                <span><strong>Section:</strong> {{ $selectedStudent->enrollments->last()?->schoolYearSection?->yearLevel?->name }} - {{ $selectedStudent->enrollments->last()?->schoolYearSection?->section?->name }}</span>
            </div>
        </div>

        <!-- Awards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($awardsList as $aw)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="size-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-xl border border-amber-200 dark:border-amber-800 shrink-0">
                            @if ($aw['type'] === 'honor')
                                <i class="fas fa-crown"></i>
                            @elseif ($aw['type'] === 'subject')
                                <i class="fas fa-medal text-indigo-600"></i>
                            @else
                                <i class="fas fa-certificate text-emerald-600"></i>
                            @endif
                        </div>

                        <div class="text-end">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-[#166534] border border-emerald-300">
                                <i class="fas fa-calendar-check text-[9px]"></i>
                                <span>{{ $aw['quarter'] }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <h3 class="font-black text-zinc-900 dark:text-zinc-100 text-base leading-tight">
                            {{ $aw['title'] }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                            {{ $aw['description'] }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                        @if (isset($aw['grade']))
                            <span class="font-bold text-zinc-500">
                                Rating: <span class="font-mono text-emerald-700 dark:text-emerald-400 font-black text-sm">{{ number_format($aw['grade'], 2) }}</span>
                            </span>
                        @else
                            <span class="text-zinc-400 text-[11px]">Official School Award</span>
                        @endif

                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                            {{ $aw['type'] === 'honor' ? 'Academic Honor' : ($aw['type'] === 'subject' ? 'Subject Excellence' : 'Special Citation') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-700 p-12 text-center text-zinc-500 space-y-2">
                    <i class="fas fa-award text-3xl text-zinc-300 mb-1"></i>
                    <p class="text-xs font-bold">No awards found for the selected quarter.</p>
                    <p class="text-[11px] text-zinc-400">Awards and honors are updated whenever quarter grades are approved.</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-dashed border-zinc-300 dark:border-zinc-700 p-12 text-center text-zinc-500">
            No linked child record found to view awards.
        </div>
    @endif
</div>
