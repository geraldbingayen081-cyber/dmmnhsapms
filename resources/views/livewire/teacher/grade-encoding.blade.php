<div class="space-y-6 font-sans">

    {{-- ===== Header & Selectors ===== --}}
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold mb-2">
                    <i class="fas fa-file-pen"></i>
                    <span>Official Grade Sheet — {{ $activeSy?->school_year }}</span>
                </div>
                <h1 class="text-base font-black text-zinc-900 dark:text-zinc-100">
                    Quarterly Subject Grade Encoding
                </h1>
                <p class="text-[11px] text-zinc-500 mt-0.5 font-medium">
                    All existing grades (Q1–Q3) are shown per student. The <strong>active quarter's column</strong> is editable when encoding is open. Changes are saved to the shared database and immediately visible to the admin.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if ($isEncodingActive)
                    <button wire:click="saveGrades"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-[#166534] hover:bg-emerald-800 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveGrades"><i class="fas fa-save"></i></span>
                        <span wire:loading wire:target="saveGrades"><i class="fas fa-circle-notch fa-spin"></i></span>
                        <span>Save All Grades</span>
                    </button>
                @else
                    <button disabled
                            class="px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-400 font-bold text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center gap-2 cursor-not-allowed"
                            title="Encoding is locked by the administration for this quarter.">
                        <i class="fas fa-lock text-xs"></i>
                        <span>Read-Only Mode</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Dropdowns --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-600 dark:text-zinc-300 mb-1.5 uppercase tracking-wide text-[10px]">
                    Subject Class
                </label>
                <select wire:model.live="selectedClassId"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-bold focus:ring-2 focus:ring-[#166534]">
                    @forelse ($myClasses as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->schoolYearSection?->yearLevel?->name }} ·
                            {{ $c->schoolYearSection?->section?->name }} ·
                            {{ $c->subject?->subject_name }} ({{ $c->subject?->subject_code }})
                        </option>
                    @empty
                        <option value="">— No assigned classes —</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="block font-bold text-zinc-600 dark:text-zinc-300 mb-1.5 uppercase tracking-wide text-[10px]">
                    View / Encode Quarter
                </label>
                <select wire:model.live="selectedQuarterId"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-bold focus:ring-2 focus:ring-[#166534]">
                    @foreach ($quarters as $q)
                        <option value="{{ $q->id }}">
                            {{ $q->name }} ({{ $q->short_name }})
                            @if ($q->status === 'active') — 🔓 Open
                            @elseif ($q->status === 'completed') — ✅ Completed
                            @else — ⏳ Upcoming
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-1 flex flex-col justify-end">
                <div class="p-3 rounded-xl border text-[11px] font-medium
                    {{ $isEncodingActive
                        ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-700'
                        : 'bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:border-amber-700' }}">
                    <span class="block text-[10px] font-bold uppercase tracking-wide mb-1
                        {{ $isEncodingActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        Admin Grading Window
                    </span>
                    @if ($isEncodingActive)
                        <span class="text-emerald-700 dark:text-emerald-300 font-bold flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            ACTIVE — {{ $selectedQuarter?->name }} is open for encoding.
                        </span>
                    @else
                        <span class="text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1.5">
                            <i class="fas fa-lock text-[10px]"></i>
                            LOCKED — {{ $selectedQuarter?->name ?? 'Quarter' }} is
                            <strong>{{ strtoupper($selectedQuarter?->status ?? 'CLOSED') }}</strong>.
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-50 text-[#166534] border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="fas fa-circle-check text-base"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 text-rose-700 border border-rose-300 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800 text-xs font-bold flex items-center gap-2">
            <i class="fas fa-lock text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Status Banner --}}
    @if ($isEncodingActive)
        <div class="p-3.5 rounded-xl bg-[#DCFCE7] text-[#166534] border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i class="fas fa-lock-open"></i>
                Grade Encoding is <strong>OPEN</strong> for <strong>{{ $selectedQuarter?->name }}</strong>.
                Type marks into the highlighted column and click <strong>Save All Grades</strong>.
            </span>
            <span class="hidden md:block font-mono text-[11px]">DepEd Passing: 75.00</span>
        </div>
    @else
        <div class="p-3.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i class="fas fa-lock text-amber-600"></i>
                Encoding is <strong>LOCKED</strong> — <strong>{{ $selectedQuarter?->name }}</strong>
                is {{ strtoupper($selectedQuarter?->status ?? 'CLOSED') }}.
                Existing grades are shown in <strong>read-only</strong> mode.
                Admin changes to this quarter are also visible below.
            </span>
            <span class="hidden md:block shrink-0 px-2.5 py-0.5 rounded bg-amber-200/70 dark:bg-amber-900 text-[10px] font-mono uppercase">Read-Only</span>
        </div>
    @endif

    {{-- Stats Strip --}}
    @if ($studentsList->isNotEmpty() && $stats['encodedCount'] > 0)
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <span class="text-[10px] font-bold text-zinc-400 uppercase block">Quarter Avg</span>
                <span class="text-base font-black {{ ($stats['average'] ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                    {{ $stats['average'] ? number_format($stats['average'], 2) : '—' }}
                </span>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <span class="text-[10px] font-bold text-zinc-400 uppercase block">Passing Rate</span>
                <span class="text-base font-black text-blue-600 dark:text-blue-400">{{ $stats['passingRate'] }}%</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <span class="text-[10px] font-bold text-zinc-400 uppercase block">Highest</span>
                <span class="text-base font-black text-amber-600 dark:text-amber-400">
                    {{ $stats['highest'] ? number_format($stats['highest'], 2) : '—' }}
                </span>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <span class="text-[10px] font-bold text-zinc-400 uppercase block">Lowest</span>
                <span class="text-base font-black text-zinc-700 dark:text-zinc-300">
                    {{ $stats['lowest'] ? number_format($stats['lowest'], 2) : '—' }}
                </span>
            </div>
            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <span class="text-[10px] font-bold text-zinc-400 uppercase block">Encoded</span>
                <span class="text-base font-black text-zinc-900 dark:text-zinc-100">
                    {{ $stats['encodedCount'] }} / {{ $stats['totalStudents'] }}
                </span>
            </div>
        </div>
    @endif

    {{-- ===== Unified Grade Matrix Table ===== --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">

        {{-- Legend --}}
        <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center gap-4 text-[11px] text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50">
            <span class="font-bold text-zinc-700 dark:text-zinc-300">Grade Sheet:</span>
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded bg-zinc-200 dark:bg-zinc-700"></span> Existing (Read-Only)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded bg-emerald-200 dark:bg-emerald-800"></span>
                @if ($isEncodingActive) Selected Quarter — Editable
                @else Selected Quarter — Locked
                @endif
            </span>
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded bg-rose-200 dark:bg-rose-900"></span> Below Passing (< 75)
            </span>
            <span class="ml-auto text-zinc-400 text-[10px] italic">Admin changes also reflected here (shared database)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-start">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 text-[10px] font-bold uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="py-3 px-3 text-center w-10">#</th>
                        <th class="py-3 px-4 text-start">Student ID</th>
                        <th class="py-3 px-4 text-start">Learner Name</th>

                        {{-- One column per quarter --}}
                        @foreach ($quarters as $q)
                            <th class="py-3 px-3 text-center min-w-[110px]
                                {{ (int)$q->id === (int)$selectedQuarterId
                                    ? 'bg-emerald-100/70 dark:bg-emerald-950/70 text-[#166534] dark:text-emerald-300'
                                    : '' }}">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-black">{{ $q->short_name }}</span>
                                    <span class="text-[8px] font-normal opacity-70">
                                        @if ($q->status === 'active') 🔓 Active
                                        @elseif ($q->status === 'completed') ✅ Done
                                        @else ⏳ Soon
                                        @endif
                                    </span>
                                </div>
                            </th>
                        @endforeach

                        <th class="py-3 px-3 text-center min-w-[90px] bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                            Gen. Avg
                        </th>
                        <th class="py-3 px-3 text-center w-24">Status</th>
                        @if ($isEncodingActive)
                            <th class="py-3 px-4 text-start min-w-[130px]">Remarks</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($studentsList as $idx => $enr)
                        @php
                            $st       = $enr->student;
                            $inputVal = $grades[$enr->id] ?? '';
                            $inputNum = is_numeric($inputVal) ? (float)$inputVal : null;

                            // Read gradeMatrix using string-cast keys (must match how render() built the array)
                            $eid       = (string)$enr->id;
                            $rowVals   = $gradeMatrix[$eid] ?? [];
                            $matrixVals = array_filter($rowVals, fn($v) => $v !== null);
                            $genAvg = count($matrixVals) > 0
                                ? round(array_sum($matrixVals) / count($matrixVals), 2)
                                : null;
                        @endphp
                        <tr wire:key="row-{{ $enr->id }}-{{ $selectedClassId }}-{{ $selectedQuarterId }}"
                            class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">

                            <td class="py-3 px-3 text-center text-zinc-400 font-bold">{{ $idx + 1 }}</td>

                            <td class="py-3 px-4 font-mono font-bold text-[#166534] dark:text-emerald-400 whitespace-nowrap">
                                {{ $st?->student_number }}
                            </td>

                            <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                {{ $st?->last_name }}, {{ $st?->first_name }}
                            </td>

                            {{-- Quarter columns: read-only for past/upcoming, editable for the active selected quarter --}}
                            @foreach ($quarters as $q)
                                @php
                                    $qid          = (string)$q->id;
                                    $isThisSelected = (int)$q->id === (int)$selectedQuarterId;
                                    // Use string-cast key to read matrix (matches render() build)
                                    $cellGrade = ($gradeMatrix[$eid] ?? [])[$qid] ?? null;
                                    $cellNum   = $cellGrade !== null ? (float)$cellGrade : null;
                                @endphp
                                <td class="py-2 px-2 text-center
                                    {{ $isThisSelected ? 'bg-emerald-50/50 dark:bg-emerald-950/20' : '' }}">

                                    @if ($isThisSelected && $isEncodingActive)
                                        {{-- ✏️ Editable input for the active selected quarter --}}
                                        <div class="max-w-[100px] mx-auto">
                                            <input
                                                type="number"
                                                step="0.01" min="60" max="99"
                                                wire:model.live.debounce.300ms="grades.{{ $enr->id }}"
                                                placeholder="—"
                                                class="w-full text-center px-2 py-1.5 font-bold font-mono text-sm rounded-lg border-2 transition
                                                    {{ $inputNum !== null
                                                        ? ($inputNum >= 75
                                                            ? 'border-emerald-400 bg-emerald-50 text-[#166534] dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-600'
                                                            : 'border-rose-400 bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-700')
                                                        : 'border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100' }}
                                                    focus:ring-2 focus:ring-[#166534] focus:outline-none">
                                        </div>
                                        @error("grades.{$enr->id}")
                                            <span class="text-rose-600 text-[9px] font-bold block mt-0.5">{{ $message }}</span>
                                        @enderror

                                    @elseif ($isThisSelected && !$isEncodingActive)
                                        {{-- 🔒 Locked: show existing grade from DB (read-only) for selected but locked quarter --}}
                                        <div class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg font-mono font-bold text-xs border
                                            {{ $cellNum !== null
                                                ? ($cellNum >= 75
                                                    ? 'bg-emerald-50 text-[#166534] border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800'
                                                    : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800')
                                                : 'bg-zinc-50 text-zinc-400 border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700' }}">
                                            <i class="fas fa-lock text-[8px] opacity-60"></i>
                                            <span>{{ $cellNum !== null ? number_format($cellNum, 2) : 'None' }}</span>
                                        </div>

                                    @else
                                        {{-- 📋 Other quarters — show existing DB grade, read-only --}}
                                        @if ($cellNum !== null)
                                            <span class="font-mono font-bold text-xs
                                                {{ $cellNum >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ number_format($cellNum, 2) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-300 dark:text-zinc-600 text-[11px] font-mono">—</span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            {{-- General Average column --}}
                            <td class="py-3 px-3 text-center bg-zinc-50/60 dark:bg-zinc-800/30">
                                @if ($genAvg !== null)
                                    <span class="font-mono font-black text-xs
                                        {{ $genAvg >= 75 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600' }}">
                                        {{ number_format($genAvg, 2) }}
                                    </span>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600 text-[11px]">—</span>
                                @endif
                            </td>

                            {{-- Pass/Fail status (based on selected quarter cell) --}}
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @php
                                    $selectedQid = (string)$selectedQuarterId;
                                    $statusNum = $inputNum ?? (($gradeMatrix[$eid] ?? [])[$selectedQid] ?? null);
                                @endphp
                                @if ($statusNum !== null)
                                    @if ($statusNum >= 75)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-[#166534] border border-emerald-200">PASSED</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">FAILED</span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400">PENDING</span>
                                @endif
                            </td>

                            {{-- Remarks (only when encoding open) --}}
                            @if ($isEncodingActive)
                                <td class="py-2 px-4">
                                    <input type="text"
                                           wire:model.defer="remarks.{{ $enr->id }}"
                                           placeholder="Optional feedback..."
                                           class="w-full px-2.5 py-1 text-[11px] rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($quarters) + ($isEncodingActive ? 5 : 4) }}"
                                class="p-12 text-center text-zinc-400 italic">
                                <i class="fas fa-users text-3xl mb-3 text-zinc-300 block"></i>
                                Select a subject class above to load the student grade sheet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($studentsList->isNotEmpty())
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/80 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                <span class="text-zinc-500 font-medium">
                    {{ $studentsList->count() }} enrolled student(s) •
                    {{ $stats['encodedCount'] }} grade(s) ready for
                    {{ $selectedQuarter?->name }}
                </span>

                @if ($isEncodingActive)
                    <button wire:click="saveGrades"
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveGrades"><i class="fas fa-check"></i></span>
                        <span wire:loading wire:target="saveGrades"><i class="fas fa-spinner fa-spin"></i></span>
                        <span>Save & Sync to Admin</span>
                    </button>
                @else
                    <span class="text-zinc-400 italic text-[11px] flex items-center gap-1.5">
                        <i class="fas fa-lock"></i>
                        Encoding locked — activate this quarter in Admin → Quarter Management.
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>
