<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>🌟 Academic Achievements & "Best in Subject" Manager</span>
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Log academic honor roll standings and award "Best in Subject" recognitions for Don Mariano Marcos National High School students.
            </p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Award Form -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4 text-xs">
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                <span>🏅 Award Recognition</span>
            </h2>
            <form wire:submit.prevent="awardAchievement" class="space-y-3">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Select Student</label>
                    <select wire:model="selectedStudentId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        <option value="">-- Choose Student --</option>
                        @foreach ($students as $st)
                            <option value="{{ $st->id }}">{{ $st->custom_student_id }} - {{ $st->full_name }} ({{ $st->section?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Award Category</label>
                    <select wire:model="achievementType" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-semibold">
                        <option value="Best in Subject">🥇 Best in Subject</option>
                        <option value="Honor Roll">🌟 Honor Roll Standing</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Award Title</label>
                    <input type="text" wire:model="title" placeholder="e.g. Best in Science, With Honors..." class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                    @error('title') <span class="text-rose-500 font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Grading Quarter</label>
                    <select wire:model="quarter" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        <option value="Q1">Quarter 1</option>
                        <option value="Q2">Quarter 2</option>
                        <option value="Q3">Quarter 3</option>
                        <option value="Final">Final Academic Year</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Remarks / Note</label>
                    <textarea wire:model="remarks" rows="2" placeholder="Optional commendation remarks..." class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-xl transition shadow-sm">
                    Award Recognition
                </button>
            </form>
        </div>

        <!-- Achievements Feed Table -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4 text-xs">
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                <span>🏆 Academic Achievement Registry (SY {{ $currentSy?->year }})</span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider">
                            <th class="py-2.5 px-3 text-start">Student</th>
                            <th class="py-2.5 px-3 text-start">Category</th>
                            <th class="py-2.5 px-3 text-start">Award Title</th>
                            <th class="py-2.5 px-3 text-center">Period</th>
                            <th class="py-2.5 px-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                        @forelse ($achievements as $ach)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $ach->student?->full_name }}</span>
                                    <span class="text-[10px] text-zinc-400 font-mono">{{ $ach->student?->custom_student_id }} • {{ $ach->student?->section?->name }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    @if ($ach->type === 'Honor Roll')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Honor Roll</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Best in Subject</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-bold text-amber-600 dark:text-amber-400">
                                    {{ $ach->title }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-zinc-600 dark:text-zinc-300">
                                    {{ $ach->quarter }}
                                </td>
                                <td class="py-3 px-3 text-end">
                                    <button wire:click="deleteAchievement({{ $ach->id }})" class="px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded text-[10px] font-bold">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-zinc-400 italic">No achievements recorded for the current school year.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
