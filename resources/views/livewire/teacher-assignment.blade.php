<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>👨‍🏫 Teacher Workload & Adviser Assignment</span>
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Assign section adviserships for section monitoring and allocate subject teaching loads per grade level.
            </p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Forms Column -->
        <div class="space-y-6">
            <!-- Assign Section Adviser -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4 text-xs">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                    <span>🌟 Assign Section Adviser</span>
                </h2>
                <form wire:submit.prevent="assignAdviser" class="space-y-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Select Section</label>
                        <select wire:model="adviseeSectionId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Section --</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }} (Current: {{ $sec->adviser ? $sec->adviser->name : 'None' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Select Teacher (Adviser)</label>
                        <select wire:model="selectedTeacherId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Teacher --</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user_id }} - {{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-xl transition shadow-sm">
                        Assign Section Adviser
                    </button>
                </form>
            </div>

            <!-- Assign Subject Load -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4 text-xs">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                    <span>📖 Assign Subject Teaching Load</span>
                </h2>
                <form wire:submit.prevent="assignSubjectLoad" class="space-y-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Teacher</label>
                        <select wire:model="loadTeacherId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Teacher --</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user_id }} - {{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Section</label>
                        <select wire:model="loadSectionId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Section --</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Subject</label>
                        <select wire:model="loadSubjectId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Subject --</option>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->code }} - {{ $sub->name }} ({{ $sub->gradeLevel->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl transition shadow-sm">
                        Add Subject Load
                    </button>
                </form>
            </div>
        </div>

        <!-- Matrix Tables -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Section Advisers Summary -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">🏫 Section Advisers Matrix</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-start">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider">
                                <th class="py-2 px-3 text-start">Grade Level</th>
                                <th class="py-2 px-3 text-start">Section</th>
                                <th class="py-2 px-3 text-start">Assigned Adviser</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                            @foreach ($sections as $sec)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">{{ $sec->gradeLevel->name }}</td>
                                    <td class="py-2.5 px-3 font-bold text-sky-600 dark:text-sky-400">{{ $sec->name }}</td>
                                    <td class="py-2.5 px-3">
                                        @if ($sec->adviser)
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">👨‍🏫 {{ $sec->adviser->name }} ({{ $sec->adviser->user_id }})</span>
                                        @else
                                            <span class="text-rose-500 italic font-normal">Unassigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Subject Teaching Load List -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">📚 Subject Teaching Load Allocation</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-start">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider">
                                <th class="py-2 px-3 text-start">Teacher</th>
                                <th class="py-2 px-3 text-start">Section</th>
                                <th class="py-2 px-3 text-start">Subject</th>
                                <th class="py-2 px-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                            @foreach ($assignedLoads as $load)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="py-2.5 px-3 font-bold text-zinc-900 dark:text-zinc-100">{{ $load->teacher->name }}</td>
                                    <td class="py-2.5 px-3 text-sky-600 font-semibold">{{ $load->section->name }}</td>
                                    <td class="py-2.5 px-3 font-bold text-emerald-600">{{ $load->subject->name }}</td>
                                    <td class="py-2.5 px-3 text-end">
                                        <button wire:click="removeLoad({{ $load->id }})" class="px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded text-[10px] font-bold">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
