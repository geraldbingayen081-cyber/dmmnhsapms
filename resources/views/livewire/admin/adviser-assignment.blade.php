<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chalkboard-user text-[#166534]"></i>
                <span>Class Adviser Assignment</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Assign official faculty advisers to school year section instances.</p>
        </div>
    </div>

    <!-- Target School Year Filter Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-3">
            <label class="font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Target School Year:</label>
            <select wire:model.live="selectedSchoolYearId" class="px-3.5 py-2 font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                @foreach ($schoolYears as $sy)
                    <option value="{{ $sy->id }}">SY {{ $sy->school_year }} {{ $sy->status === 'active' ? '(Active SY)' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Section Instance Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 text-start">Year Level</th>
                        <th class="py-3 px-4 text-start">Section Instance</th>
                        <th class="py-3 px-4 text-center">Enrolled Students</th>
                        <th class="py-3 px-4 text-start">Official Class Adviser</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($sections as $sys)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $sys->yearLevel?->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded font-bold text-[11px] bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                    {{ $sys->section?->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-700">
                                    {{ $sys->enrollments->count() }} Students
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($sys->adviser)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-[10px] text-[#166534] bg-emerald-50 dark:bg-emerald-950 px-1.5 py-0.5 rounded border border-emerald-200">
                                            {{ $sys->adviser->employee_number }}
                                        </span>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $sys->adviser->full_name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic text-[11px]">Unassigned Adviser</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end space-x-1">
                                <button wire:click="openAssignModal({{ $sys->id }})" title="Assign / Change Adviser" class="px-3 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-lg transition text-[11px]">
                                    <i class="fas fa-user-plus mr-1"></i>
                                    {{ $sys->adviser ? 'Change Adviser' : 'Assign Adviser' }}
                                </button>

                                @if ($sys->adviser)
                                    <button wire:click="removeAdviser({{ $sys->id }})" title="Remove Adviser" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="fas fa-user-minus text-xs"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-zinc-400 italic">
                                <i class="fas fa-chalkboard-user text-2xl mb-2 text-zinc-300 block"></i>
                                No section instances configured for the selected school year.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ASSIGN ADVISER MODAL -->
    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showAssignModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-user-shield text-[#166534]"></i>
                        <span>Assign Class Adviser</span>
                    </h3>
                    <button wire:click="$set('showAssignModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="assignAdviser" class="space-y-4">
                    @if ($targetSection)
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Target Section</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                                {{ $targetSection->yearLevel?->name }} - {{ $targetSection->section?->name }}
                            </span>
                        </div>
                    @endif

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Faculty Member *</label>
                        <select wire:model.defer="teacherId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Choose Teacher...</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->employee_number }} - {{ $t->full_name }}</option>
                            @endforeach
                        </select>
                        @error('teacherId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showAssignModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Save Adviser Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
