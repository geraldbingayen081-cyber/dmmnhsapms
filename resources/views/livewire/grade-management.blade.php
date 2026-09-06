<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto font-sans">
    <!-- Header & Selection Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold mb-2">
                <span>📝 Subject Grade Encoding</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                Quarterly Subject Grade Entry Grid
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">
                Input quarterly subject grades for Junior High School students.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider px-1">Section Instance</span>
                <select wire:model.live="selectedSchoolYearSectionId" class="px-3.5 py-2 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    @foreach ($schoolYearSections as $sys)
                        <option value="{{ $sys->id }}">{{ $sys->yearLevel?->name }} - {{ $sys->section?->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider px-1">Subject</span>
                <select wire:model.live="selectedSectionSubjectId" class="px-3.5 py-2 text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    @foreach ($sectionSubjects as $ss)
                        <option value="{{ $ss->id }}">{{ $ss->subject?->subject_code }} - {{ $ss->subject?->subject_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-bold">
            ✅ {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-bold">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Active Quarter Status Banner -->
    <div class="p-4 rounded-2xl bg-zinc-900 text-white flex items-center justify-between text-xs shadow-md">
        <div class="flex items-center gap-3">
            <span class="font-bold text-zinc-300">Active SY: {{ $currentSy?->school_year }}</span>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                Status: {{ strtoupper($currentSy?->status ?? 'DRAFT') }}
            </span>
        </div>
        <span class="text-zinc-400 text-[11px] font-medium hidden sm:inline">DepEd Standard: Passing &ge; 75.00% • Max Allowed: 99.00</span>
    </div>

    <!-- Clean Grade Spreadsheet Grid -->
    <div class="bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-6">
        <form wire:submit.prevent="saveGrades" class="space-y-6">
            <div class="overflow-x-auto rounded-2xl border border-zinc-200/80 dark:border-zinc-800">
                <table class="w-full text-xs text-start">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 text-start">Student Number</th>
                            <th class="py-3.5 px-4 text-start">Student Name</th>
                            @foreach ($quarters as $q)
                                <th class="py-3.5 px-4 text-center w-32">{{ $q->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                        @forelse ($enrollments as $enr)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                <td class="py-3 px-4 font-mono font-bold text-sky-600 dark:text-sky-400">
                                    {{ $enr->student?->student_number }}
                                </td>
                                <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $enr->student?->full_name }}
                                </td>
                                @foreach ($quarters as $q)
                                    <td class="py-3 px-4 text-center">
                                        <input type="number" step="0.01" min="60" max="99" wire:model.defer="gradesData.{{ $enr->id }}.{{ $q->id }}" class="w-24 text-center px-3 py-1.5 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 font-bold text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-sky-500">
                                        @error("gradesData.{$enr->id}.{$q->id}")
                                            <span class="text-rose-600 text-[9px] font-bold block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + count($quarters) }}" class="p-8 text-center text-zinc-400 italic">No enrolled students found in the selected section instance.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="py-3 px-6 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-2">
                    <span>💾 Save Subject Grades</span>
                </button>
            </div>
        </form>
    </div>
</div>
