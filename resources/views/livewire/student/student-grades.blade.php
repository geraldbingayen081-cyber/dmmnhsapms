<div class="space-y-6 font-sans">
    <!-- Header Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold mb-2">
                    <i class="fas fa-file-lines"></i>
                    <span>DepEd Form 138-JHS • School Year {{ $activeSy?->school_year ?? '2026-2027' }}</span>
                </div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    Official Progress Report Card (SF9)
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Consolidated Quarterly Grades for 1st Quarter to 3rd Quarter (Passing mark: 75.00).
                </p>
            </div>

            <!--<button onclick="window.print()" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print Report Card</span>
            </button>-->
        </div>

        <!-- Student Meta Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Learner Name</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block truncate">{{ $student?->full_name }}</span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Student LRN / ID</span>
                <span class="font-mono font-bold text-[#166534] dark:text-emerald-400 text-sm block">{{ $student?->student_number }}</span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Grade & Section</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block">{{ $activeEnrollment?->schoolYearSection?->yearLevel?->name }} - {{ $activeEnrollment?->schoolYearSection?->section?->name }}</span>
            </div>
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-100 dark:border-zinc-700/60">
                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Class Adviser</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm block truncate">{{ $activeEnrollment?->schoolYearSection?->adviser?->full_name ?? 'Faculty Assigned' }}</span>
            </div>
        </div>
    </div>

    <!-- Grades Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 text-[10px] font-bold uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-3 text-center w-10">#</th>
                        <th class="py-3 px-4 text-start">Learning Areas</th>
                        <th class="py-3 px-4 text-start">Faculty Instructor</th>
                        @foreach ($quarters as $q)
                            <th class="py-3 px-3 text-center min-w-[70px]">
                                {{ $q->short_name }}
                            </th>
                        @endforeach
                        <th class="py-3 px-3 text-center min-w-[85px] bg-emerald-50/50 dark:bg-emerald-950/20 text-[#166534] dark:text-emerald-300 font-black">
                            Final Grade
                        </th>
                        <th class="py-3 px-3 text-center w-28">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($subjectRows as $idx => $row)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3 px-3 text-center text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $row['name'] }}</span>
                                <span class="font-mono text-[10px] text-[#166534] dark:text-emerald-400 font-bold">{{ $row['code'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                                {{ $row['teacher'] }}
                            </td>
                            @foreach ($quarters as $q)
                                @php $val = $row['q_marks'][$q->id] ?? null; @endphp
                                <td class="py-3 px-3 text-center font-mono font-bold">
                                    @if ($val !== null)
                                        <span class="{{ $val >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600 font-black' }}">
                                            {{ number_format($val, 1) }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="py-3 px-3 text-center bg-emerald-50/50 dark:bg-emerald-950/20 font-mono font-black text-sm">
                                @if ($row['final_avg'] !== null)
                                    <span class="{{ $row['final_avg'] >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                                        {{ number_format($row['final_avg'], 2) }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500 font-bold" title="Pending complete grades from 1st to 3rd quarter">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if ($row['remarks'] === 'PASSED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                        PASSED
                                    </span>
                                @elseif ($row['remarks'] === 'FAILED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                        FAILED
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400" title="Complete grades from 1st to 3rd quarter required">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($quarters) + 5 }}" class="p-8 text-center text-zinc-400 italic">
                                No subject records available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer General Average Row -->
                <tfoot class="bg-zinc-50 dark:bg-zinc-800/80 border-t-2 border-zinc-200 dark:border-zinc-700 font-bold text-xs">
                    <tr>
                        <td colspan="3" class="py-3.5 px-4 text-end uppercase text-zinc-600 dark:text-zinc-300 tracking-wider">
                            General Weighted Average (GWA):
                        </td>
                        <td colspan="{{ count($quarters) }}" class="text-center font-normal text-zinc-400 text-[10px]">
                            {{ $gwa !== null ? 'Cumulative Average (Q1–Q3)' : 'Requires Complete Q1 to Q3' }}
                        </td>
                        <td class="py-3.5 px-3 text-center bg-emerald-100/60 dark:bg-emerald-950/60 font-mono font-black text-base text-[#166534] dark:text-emerald-300">
                            {{ $gwa !== null ? number_format($gwa, 2) : '—' }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if ($gwa !== null)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $gwa >= 75 ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $gwa >= 75 ? 'PASSED' : 'FAILED' }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                    PENDING
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
