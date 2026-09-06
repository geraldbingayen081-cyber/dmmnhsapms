<div class="space-y-6 font-sans">
    @if (!$advisedSection)
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm space-y-3">
            <div class="size-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-2xl mx-auto">
                <i class="fas fa-file-pdf"></i>
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                Official Report Generation Restricted
            </h3>
            <p class="text-xs text-zinc-500 max-w-md mx-auto">
                DepEd Form 138 (SF9 Progress Report Card) and Section Grade Sheets are generated exclusively by official Class Advisers for their assigned advisory sections.
            </p>
        </div>
    @else
        <!-- Report Configuration Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-6">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-4">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-file-pdf text-[#166534]"></i>
                    <span>Generate Official DepEd Forms & Grade Reports</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Advisory Class: <strong>{{ $advisedSection->yearLevel?->name }} - {{ $advisedSection->section?->name }}</strong> • SY {{ $activeSy?->school_year }}
                </p>
            </div>

            <form wire:submit.prevent="generateReport" class="space-y-5 text-xs">
                <!-- Report Type Selector Cards -->
                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-2">Select Report Document *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative p-4 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $reportType === 'form138' ? 'border-[#166534] bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900' }}">
                            <input type="radio" wire:model.live="reportType" value="form138" class="mt-1 text-[#166534] focus:ring-[#166534]">
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-sm">DepEd Form 138 (SF9)</span>
                                <span class="text-zinc-500 text-[11px] block mt-0.5">Learner Progress Report Card with complete quarterly subject ratings, general average, and pass/fail standing.</span>
                            </div>
                        </label>

                        <label class="relative p-4 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $reportType === 'summary_sheet' ? 'border-[#166534] bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900' }}">
                            <input type="radio" wire:model.live="reportType" value="summary_sheet" class="mt-1 text-[#166534] focus:ring-[#166534]">
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-sm">Section Grade Summary Sheet</span>
                                <span class="text-zinc-500 text-[11px] block mt-0.5">Consolidated master sheet listing all {{ $advisees->count() }} enrolled advisees, average ratings, and promotion status.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Student Selector (For Form 138) -->
                @if ($reportType === 'form138')
                    <div class="max-w-md">
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            Select Advisee Student *
                        </label>
                        <select wire:model.defer="selectedStudentId" required class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-bold focus:ring-2 focus:ring-[#166534]">
                            <option value="">-- Choose Advisee --</option>
                            @foreach ($advisees as $st)
                                <option value="{{ $st->id }}">
                                    {{ $st->last_name }}, {{ $st->first_name }} ({{ $st->student_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedStudentId') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" class="px-6 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <span wire:loading.remove wire:target="generateReport">
                            <i class="fas fa-file-arrow-up"></i>
                        </span>
                        <span wire:loading wire:target="generateReport">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </span>
                        <span>Generate & Preview Report</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- REPORT PREVIEW MODAL -->
        @if ($showPreviewModal && $reportData)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm" wire:click="$set('showPreviewModal', false)"></div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col relative z-10 text-xs">
                    <!-- Modal Header -->
                    <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-pdf text-[#166534] text-base"></i>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $reportData['type'] }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="window.print()" class="px-3 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-lg transition flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-print"></i>
                                <span>Print Report</span>
                            </button>
                            <button wire:click="$set('showPreviewModal', false)" class="text-zinc-400 hover:text-zinc-600 p-1">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Printable Content Body -->
                    <div id="printable-report-area" class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 text-zinc-900 dark:text-zinc-100">
                        <!-- DepEd Official Header -->
                        <div class="flex items-center justify-between border-b-2 border-zinc-900 dark:border-zinc-100 pb-4">
                            @php $teacherDocLogo = \App\Models\SystemSetting::logoUrl(); @endphp
                            @if ($teacherDocLogo)
                                <div class="size-16 shrink-0">
                                    <img src="{{ $teacherDocLogo }}" alt="Logo" class="w-full h-full object-contain">
                                </div>
                            @else
                                <div class="size-16 rounded-2xl bg-[#166534] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                    DMMNHS
                                </div>
                            @endif
                            <div class="text-center space-y-1 flex-1 px-4">
                                <p class="text-[10px] uppercase tracking-widest text-zinc-500 font-bold">Republic of the Philippines</p>
                                <h2 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Department of Education</h2>
                                <p class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-300">Region II – Cagayan Valley • Schools Division of Cagayan</p>
                                <h1 class="text-base font-black uppercase text-[#166534] dark:text-emerald-400 mt-1">
                                    {{ \App\Models\SystemSetting::get('school_name', 'Don Mariano Marcos National High School') }}
                                </h1>
                                <p class="text-[10px] text-zinc-500 font-medium">{{ \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan') }}</p>
                                <div class="pt-1">
                                    <span class="inline-block px-3 py-1 rounded bg-zinc-100 dark:bg-zinc-800 font-black text-xs uppercase tracking-wide">
                                        {{ $reportData['type'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="size-16 shrink-0 flex items-center justify-center">
                                <div class="size-14 rounded-full border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-[9px] font-mono text-zinc-400">
                                    DEPED
                                </div>
                            </div>
                        </div>

                        <!-- FORM 138 (SF9) BODY -->
                        @if ($reportData['type'] === 'Form 138 (SF9 Report Card)')
                            @php
                                $st = $reportData['student'];
                                $secInst = $reportData['section_instance'];
                            @endphp
                            <!-- Learner Metadata Strip -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs font-medium">
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">Learner Name:</span>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $st->full_name }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">Learner Ref No. (LRN):</span>
                                    <span class="font-mono font-bold">{{ $st->lrn ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">Grade & Section:</span>
                                    <span class="font-bold text-[#166534] dark:text-emerald-400">{{ $secInst->yearLevel?->name }} - {{ $secInst->section?->name }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase block">School Year:</span>
                                    <span class="font-bold">{{ $activeSy?->school_year }}</span>
                                </div>
                            </div>

                            <!-- Core Subjects Table -->
                            <div class="overflow-x-auto border border-zinc-300 dark:border-zinc-700 rounded-xl">
                                <table class="w-full text-xs text-start">
                                    <thead class="bg-zinc-100 dark:bg-zinc-800 font-bold uppercase text-[10px] border-b border-zinc-300 dark:border-zinc-700">
                                        <tr>
                                            <th class="py-2.5 px-4 text-start">Learning Areas</th>
                                            @foreach ($reportData['quarters'] as $q)
                                                <th class="py-2.5 px-3 text-center">{{ $q->short_name }}</th>
                                            @endforeach
                                            <th class="py-2.5 px-3 text-center bg-emerald-50 dark:bg-emerald-950/60 font-black text-[#166534] dark:text-emerald-300">Final</th>
                                            <th class="py-2.5 px-3 text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                                        @foreach ($reportData['subjects'] as $sub)
                                            <tr>
                                                <td class="py-2 px-4 font-bold">
                                                    {{ $sub['name'] }}
                                                    <span class="text-[10px] text-zinc-400 font-normal">({{ $sub['code'] }})</span>
                                                </td>
                                                @foreach ($reportData['quarters'] as $q)
                                                    <td class="py-2 px-3 text-center font-mono">
                                                        {{ $sub['quarters'][$q->short_name] ? number_format($sub['quarters'][$q->short_name], 1) : '—' }}
                                                    </td>
                                                @endforeach
                                                <td class="py-2 px-3 text-center font-mono font-black bg-emerald-50/50 dark:bg-emerald-950/20 text-sm {{ ($sub['final'] ?? 0) >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600' }}">
                                                    {{ $sub['final'] ? number_format($sub['final'], 2) : '—' }}
                                                </td>
                                                <td class="py-2 px-3 text-center font-bold text-[10px]">
                                                    <span class="px-2 py-0.5 rounded {{ $sub['remarks'] === 'PASSED' ? 'bg-emerald-100 text-emerald-800' : ($sub['remarks'] === 'FAILED' ? 'bg-rose-100 text-rose-800' : 'bg-zinc-100 text-zinc-600') }}">
                                                        {{ $sub['remarks'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-black text-xs border-t-2 border-zinc-300 dark:border-zinc-600">
                                            <td class="py-3 px-4 uppercase text-start">General Average</td>
                                            <td colspan="{{ count($reportData['quarters']) }}"></td>
                                            <td class="py-3 px-3 text-center font-mono text-base text-[#166534] dark:text-emerald-400">
                                                {{ $reportData['general_average'] ? number_format($reportData['general_average'], 2) : '—' }}
                                            </td>
                                            <td class="py-3 px-3 text-center uppercase">
                                                {{ ($reportData['general_average'] ?? 0) >= 75 ? 'PASSED' : 'INCOMPLETE' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Adviser Signature Block -->
                            <div class="grid grid-cols-2 gap-8 pt-8 text-center text-xs">
                                <div>
                                    <div class="border-b border-zinc-900 dark:border-zinc-100 pb-1 font-bold">
                                        {{ $secInst->adviser?->full_name ?? auth()->user()->name }}
                                    </div>
                                    <span class="text-[10px] text-zinc-500 uppercase font-medium">Class Adviser</span>
                                </div>
                                <div>
                                    <div class="border-b border-zinc-900 dark:border-zinc-100 pb-1 font-bold">
                                        School Principal
                                    </div>
                                    <span class="text-[10px] text-zinc-500 uppercase font-medium">School Head / Principal</span>
                                </div>
                            </div>
                        @else
                            <!-- SECTION SUMMARY GRADE SHEET BODY -->
                            <div class="overflow-x-auto border border-zinc-300 dark:border-zinc-700 rounded-xl">
                                <table class="w-full text-xs text-start">
                                    <thead class="bg-zinc-100 dark:bg-zinc-800 font-bold uppercase text-[10px] border-b border-zinc-300 dark:border-zinc-700">
                                        <tr>
                                            <th class="py-2.5 px-3 text-center w-10">#</th>
                                            <th class="py-2.5 px-4 text-start">Student ID</th>
                                            <th class="py-2.5 px-4 text-start">Learner Name</th>
                                            <th class="py-2.5 px-3 text-center">Gender</th>
                                            <th class="py-2.5 px-3 text-center">Average</th>
                                            <th class="py-2.5 px-3 text-center">Lowest</th>
                                            <th class="py-2.5 px-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                                        @foreach ($reportData['students'] as $idx => $st)
                                            <tr>
                                                <td class="py-2 px-3 text-center text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                                                <td class="py-2 px-4 font-mono font-bold text-[#166534]">{{ $st['student_number'] }}</td>
                                                <td class="py-2 px-4 font-bold">{{ $st['full_name'] }}</td>
                                                <td class="py-2 px-3 text-center uppercase text-[11px] text-zinc-500">{{ $st['gender'] ?? '—' }}</td>
                                                <td class="py-2 px-3 text-center font-mono font-black text-sm {{ ($st['average'] ?? 0) >= 75 ? 'text-[#166534]' : 'text-rose-600' }}">
                                                    {{ $st['average'] ? number_format($st['average'], 2) : '—' }}
                                                </td>
                                                <td class="py-2 px-3 text-center font-mono text-zinc-600">
                                                    {{ $st['lowest'] ? number_format($st['lowest'], 1) : '—' }}
                                                </td>
                                                <td class="py-2 px-3 text-center font-bold text-[10px]">
                                                    <span class="px-2 py-0.5 rounded {{ $st['remarks'] === 'PASSED' ? 'bg-emerald-100 text-emerald-800' : ($st['remarks'] === 'FAILED' ? 'bg-rose-100 text-rose-800' : 'bg-zinc-100 text-zinc-600') }}">
                                                        {{ $st['remarks'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
