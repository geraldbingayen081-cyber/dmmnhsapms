<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>🗓️ Academic School Year Configuration</span>
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Manage academic periods, toggle active school year context, control quarter grade encoding locks, and trigger batch student promotions.
            </p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create School Year Form -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>➕ Add School Year</span>
            </h2>
            <form wire:submit.prevent="createSchoolYear" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">School Year Format (YYYY-YYYY)</label>
                    <input type="text" wire:model="newYear" placeholder="e.g. 2026-2027" class="w-full px-3 py-2 text-sm rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-sky-500">
                    @error('newYear') <span class="text-xs text-rose-500 font-medium">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                    Create Academic Year
                </button>
            </form>
        </div>

        <!-- School Years Listing & Status Controls -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                <span>📚 Academic Year Records</span>
                <span class="text-xs font-normal text-zinc-500">Don Mariano Marcos National High School</span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider">
                            <th class="py-2.5 px-3 text-start">School Year</th>
                            <th class="py-2.5 px-3 text-start">Active Status</th>
                            <th class="py-2.5 px-3 text-start">Grading Quarter Status</th>
                            <th class="py-2.5 px-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                        @foreach ($academicYears as $sy)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                                <td class="py-3 px-3 font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                    SY {{ $sy->year }}
                                </td>
                                <td class="py-3 px-3">
                                    @if ($sy->is_current)
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300">
                                            🌟 CURRENT ACTIVE
                                        </span>
                                    @else
                                        <button wire:click="setActiveYear({{ $sy->id }})" class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-600 hover:bg-sky-100 hover:text-sky-700 dark:bg-zinc-700 dark:text-zinc-300 transition">
                                            Set Active
                                        </button>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <select wire:change="updateStatus({{ $sy->id }}, $event.target.value)" class="px-2 py-1 text-xs rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 font-semibold text-zinc-800 dark:text-zinc-200">
                                        <option value="Q1_OPEN" @selected($sy->status === 'Q1_OPEN')>🟢 Quarter 1 Open</option>
                                        <option value="Q2_OPEN" @selected($sy->status === 'Q2_OPEN')>🟢 Quarter 2 Open</option>
                                        <option value="Q3_OPEN" @selected($sy->status === 'Q3_OPEN')>🟢 Quarter 3 Open</option>
                                        <option value="LOCKED" @selected($sy->status === 'LOCKED')>🔒 Grading Closed / Locked</option>
                                    </select>
                                </td>
                                <td class="py-3 px-3 text-end">
                                    <button 
                                        wire:click="runBatchPromotion({{ $sy->id }})"
                                        wire:confirm="Are you sure you want to run batch year-end promotion for SY {{ $sy->year }}? This will evaluate grades and promote passing students."
                                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-lg text-[11px] transition shadow-sm"
                                    >
                                        🎓 Run Promotion
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
