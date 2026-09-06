<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>👨‍👩‍👧 Parent Academic Performance Portal</span>
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Don Mariano Marcos National High School • Real-time 3-quarter grades, general average, and awards for your children.
            </p>
        </div>

        @if ($linkedStudents->count() > 1)
            <!-- Multi-Child Switcher Tabs -->
            <div class="flex items-center gap-2 bg-zinc-100 dark:bg-zinc-900 p-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <span class="text-[11px] font-bold text-zinc-400 px-2">Select Child:</span>
                @foreach ($linkedStudents as $child)
                    <button wire:click="selectChild({{ $child->id }})" class="px-3 py-1.5 text-xs font-bold rounded-lg transition {{ $activeChild && $activeChild->id === $child->id ? 'bg-sky-600 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-300' }}">
                        {{ $child->first_name }} ({{ $child->section?->name }})
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if ($activeChild)
        @php
            $ga = $activeChild->calculateGeneralAverage($currentSy?->id);
            $honor = $activeChild->honor_standing;
        @endphp

        <!-- Child Academic Overview Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-800 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 flex items-center justify-center text-xl font-bold">
                    🎓
                </div>
                <div>
                    <span class="text-[11px] text-zinc-400 font-semibold block uppercase">Child Profile</span>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $activeChild->full_name }}</h3>
                    <span class="text-xs text-sky-600 dark:text-sky-400 font-mono font-bold">{{ $activeChild->custom_student_id }} • {{ $activeChild->section?->name }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                    📈
                </div>
                <div>
                    <span class="text-[11px] text-zinc-400 font-semibold block uppercase">General Average</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $ga ? $ga.'%' : 'N/A' }}</span>
                    <span class="text-[11px] text-zinc-500 block">SY {{ $currentSy?->year }} 3-Quarter Average</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center text-xl font-bold">
                    🌟
                </div>
                <div>
                    <span class="text-[11px] text-zinc-400 font-semibold block uppercase">Academic Standing</span>
                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400 block">{{ $honor ?? 'Satisfactory Standing' }}</span>
                    <span class="text-[11px] text-zinc-500 block">DepEd Recognition Standard</span>
                </div>
            </div>
        </div>

        <!-- 3-Quarter Grades Table -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                <span>📚 3-Quarter Subject Grades (SY {{ $currentSy?->year }})</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider text-[10px]">
                            <th class="py-2.5 px-3 text-start">Subject Code</th>
                            <th class="py-2.5 px-3 text-start">Subject Name</th>
                            <th class="py-2.5 px-3 text-center">Quarter 1</th>
                            <th class="py-2.5 px-3 text-center">Quarter 2</th>
                            <th class="py-2.5 px-3 text-center">Quarter 3</th>
                            <th class="py-2.5 px-3 text-center">Final Grade</th>
                            <th class="py-2.5 px-3 text-end">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                        @forelse ($activeChild->grades as $g)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                <td class="py-3 px-3 font-mono font-bold text-sky-600">{{ $g->subject?->code }}</td>
                                <td class="py-3 px-3 font-bold text-zinc-900 dark:text-zinc-100">{{ $g->subject?->name }}</td>
                                <td class="py-3 px-3 text-center font-bold">{{ $g->q1 ?? '-' }}</td>
                                <td class="py-3 px-3 text-center font-bold">{{ $g->q2 ?? '-' }}</td>
                                <td class="py-3 px-3 text-center font-bold">{{ $g->q3 ?? '-' }}</td>
                                <td class="py-3 px-3 text-center font-black text-zinc-900 dark:text-zinc-100 text-sm">{{ $g->final_grade ?? '-' }}</td>
                                <td class="py-3 px-3 text-end">
                                    @if ($g->remarks === 'Passed')
                                        <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Passed</span>
                                    @elseif ($g->remarks === 'Needs Remediation')
                                        <span class="px-2.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Needs Remediation</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]">Failed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-zinc-400 italic">No subject grades recorded for your child yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Academic Achievements Section -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-amber-600 dark:text-amber-400 flex items-center gap-2">
                <span>🏅 Achievements & Recognitions Earned</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                @forelse ($activeChild->achievements as $ach)
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 font-medium space-y-1">
                        <span class="font-black text-zinc-900 dark:text-zinc-100 text-sm block">{{ $ach->title }}</span>
                        <span class="text-amber-700 dark:text-amber-300 font-bold block text-[11px]">{{ $ach->type }} ({{ $ach->quarter }})</span>
                        <p class="text-zinc-500 text-[11px]">{{ $ach->remarks }}</p>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-zinc-400 italic">No awards recorded yet for this school year.</div>
                @endforelse
            </div>
        </div>
    @else
        <div class="p-12 text-center bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-zinc-400">
            No children are currently linked to your parent account. Please contact the administrator.
        </div>
    @endif
</div>
