<div class="space-y-6 font-sans">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-layer-group text-[#166534]"></i>
                <span>My Subject Classes & Teaching Workload</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">
                Active subject teaching assignments for School Year {{ $activeSy?->school_year ?? 'Current' }}.
            </p>
        </div>

        <a href="{{ route('teacher.grades') }}" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto">
            <i class="fas fa-file-pen"></i>
            <span>Open Grade Sheet</span>
        </a>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by subject code, name, or section..." class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="filterYearLevelId" class="px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                <option value="all">All Year Levels</option>
                @foreach ($yearLevels as $yl)
                    <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($classes as $c)
            @php
                $yl = $c->schoolYearSection?->yearLevel;
                $sec = $c->schoolYearSection?->section;
                $sub = $c->subject;
            @endphp
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-5 space-y-4 hover:border-emerald-400 dark:hover:border-emerald-600 transition flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                {{ $sub?->subject_code }}
                            </span>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-1.5">
                                {{ $sub?->subject_name }}
                            </h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                            {{ $c->enrollments_count }} <span class="text-[10px] font-medium text-zinc-500">students</span>
                        </span>
                    </div>

                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60 text-xs space-y-1 font-medium">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Year Level:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $yl?->name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Section:</span>
                            <span class="font-bold text-[#166534] dark:text-emerald-400">{{ $sec?->name }}</span>
                        </div>
                    </div>

                    <!-- Quarter Grade Submission Status Badges -->
                    <div class="space-y-1.5 pt-1">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Grading Progress</span>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 text-[10px] font-bold">
                            @foreach ($c->quarter_stats as $qs)
                                <div class="px-2 py-1 rounded text-center border {{ $qs['is_complete'] ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : ($qs['encoded'] > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700') }}">
                                    <span>{{ $qs['quarter'] }}: {{ $qs['encoded'] }}/{{ $qs['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-2">
                    <a href="{{ route('teacher.grades', ['classId' => $c->id]) }}" class="w-full text-center px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-file-pen text-xs"></i>
                        <span>Encode Class Grades</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center text-zinc-400 italic bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                <i class="fas fa-layer-group text-3xl mb-2 text-zinc-300 block"></i>
                No subject classes matching your search criteria.
            </div>
        @endforelse
    </div>
</div>
