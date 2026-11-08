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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative p-4 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $reportType === 'form138' ? 'border-[#166534] dark:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900' }}">
                            <input type="radio" wire:model.live="reportType" value="form138" class="mt-1 text-[#166534] focus:ring-[#166534]">
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-sm">DepEd Form 138 (SF9)</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px] block mt-0.5">Learner Progress Report Card with complete term subject ratings, general average, and pass/fail standing.</span>
                            </div>
                        </label>

                        <label class="relative p-4 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $reportType === 'form137' ? 'border-[#166534] dark:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900' }}">
                            <input type="radio" wire:model.live="reportType" value="form137" class="mt-1 text-[#166534] focus:ring-[#166534]">
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-sm">DepEd Form 137 (SF10)</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px] block mt-0.5">Learner's Permanent Academic Record across Junior High School (Grades 7 to 10) with official certification.</span>
                            </div>
                        </label>

                        <label class="relative p-4 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $reportType === 'summary_sheet' ? 'border-[#166534] dark:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900' }}">
                            <input type="radio" wire:model.live="reportType" value="summary_sheet" class="mt-1 text-[#166534] focus:ring-[#166534]">
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-sm">Section Grade Summary Sheet</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px] block mt-0.5">Consolidated master sheet listing all {{ $advisees->count() }} enrolled advisees, average ratings, and promotion status.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Student Selector (For Form 138 & Form 137) -->
                @if ($reportType === 'form138' || $reportType === 'form137')
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
            <div class="fixed inset-0 z-50 flex flex-col bg-zinc-950/80 backdrop-blur-md overflow-hidden p-2 sm:p-4" 
                 x-data="{ activeSpread: 'all' }"
                 x-init="document.body.style.overflow = 'hidden'"
                 x-effect="() => { return () => { document.body.style.overflow = ''; } }"
                 x-on:keydown.escape.window="$wire.set('showPreviewModal', false)">
                
                <!-- Backdrop Click Area -->
                <div class="fixed inset-0 -z-10" wire:click="$set('showPreviewModal', false)"></div>
                
                <!-- Modal Window -->
                <div class="relative w-full {{ (str_contains($reportData['type'] ?? '', 'Form 138') || str_contains($reportData['type'] ?? '', 'Form 137')) ? 'max-w-7xl' : 'max-w-4xl' }} mx-auto flex-1 min-h-0 flex flex-col bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden text-xs">
                    <!-- Modal Header -->
                    <div class="p-3.5 sm:p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3 bg-zinc-50 dark:bg-zinc-800/90 shrink-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <i class="fas {{ str_contains($reportData['type'] ?? '', 'Form 138') ? 'fa-id-card text-[#166534]' : (str_contains($reportData['type'] ?? '', 'Form 137') ? 'fa-clock-rotate-left text-[#166534]' : 'fa-file-pdf text-[#166534]') }} text-base"></i>
                                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                    @if (str_contains($reportData['type'] ?? '', 'Form 138'))
                                        DepEd Form 138-JHS (SF9) &bull; Learner's Progress Report Card
                                    @elseif (str_contains($reportData['type'] ?? '', 'Form 137'))
                                        DepEd Form 137-JHS (SF10) &bull; Learner's Permanent Academic Record
                                    @else
                                        {{ $reportData['type'] }}
                                    @endif
                                </h3>
                            </div>
                            @if (str_contains($reportData['type'] ?? '', 'Form 138'))
                                <p class="text-[11px] text-zinc-500 mt-0.5">
                                    {{ $reportData['student']?->full_name }} &bull; {{ $reportData['grade_level'] }} - {{ $reportData['section_name'] }} (SY {{ $reportData['school_year'] }})
                                </p>
                            @elseif (str_contains($reportData['type'] ?? '', 'Form 137'))
                                <p class="text-[11px] text-zinc-500 mt-0.5">
                                    {{ $reportData['student']?->full_name }} &bull; LRN: <span class="font-mono font-bold">{{ $reportData['student']?->student_number }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center flex-wrap gap-2.5">
                            @if (str_contains($reportData['type'] ?? '', 'Form 138'))
                                <div class="inline-flex rounded-lg bg-zinc-200 dark:bg-zinc-700/60 p-0.5 text-[11px] font-bold">
                                    <button type="button" 
                                            @click="activeSpread = 'all'" 
                                            :class="activeSpread === 'all' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Full Booklet
                                    </button>
                                    <button type="button" 
                                            @click="activeSpread = 'outside'" 
                                            :class="activeSpread === 'outside' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Sheet 1: Outside
                                    </button>
                                    <button type="button" 
                                            @click="activeSpread = 'inside'" 
                                            :class="activeSpread === 'inside' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Sheet 2: Inside
                                    </button>
                                </div>

                                <!-- Back to Back Print Button (PROMINENT) -->
                                <button type="button" 
                                        onclick="window.printReportCardBooklet ? window.printReportCardBooklet() : printReportCardBooklet()" 
                                        class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold rounded-xl transition text-xs flex items-center gap-2 shadow-md hover:shadow-lg cursor-pointer ring-2 ring-emerald-500/30">
                                    <i class="fas fa-print text-sm"></i>
                                    <span>Print Back-to-Back</span>
                                </button>
                            @elseif (str_contains($reportData['type'] ?? '', 'Form 137'))
                                <div class="inline-flex rounded-lg bg-zinc-200 dark:bg-zinc-700/60 p-0.5 text-[11px] font-bold">
                                    <button type="button" 
                                            @click="activeSpread = 'all'" 
                                            :class="activeSpread === 'all' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Full Record (Both Pages)
                                    </button>
                                    <button type="button" 
                                            @click="activeSpread = 'outside'" 
                                            :class="activeSpread === 'outside' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Page 1: Front
                                    </button>
                                    <button type="button" 
                                            @click="activeSpread = 'inside'" 
                                            :class="activeSpread === 'inside' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                            class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                        Page 2: Back
                                    </button>
                                </div>

                                <button type="button" 
                                        onclick="window.printSf10Record ? window.printSf10Record() : window.print()" 
                                        class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold rounded-xl transition text-xs flex items-center gap-2 shadow-md hover:shadow-lg cursor-pointer ring-2 ring-emerald-500/30">
                                    <i class="fas fa-print text-sm"></i>
                                    <span>Print Back-to-Back</span>
                                </button>
                            @else
                                <button type="button" 
                                        onclick="window.printReportDocument ? window.printReportDocument('printable-report-area', '{{ $reportData['type'] ?? 'Section Summary' }}') : window.print()" 
                                        class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold rounded-xl transition text-xs flex items-center gap-2 shadow-md cursor-pointer">
                                    <i class="fas fa-print"></i>
                                    <span>Print Report</span>
                                </button>
                            @endif

                            <button wire:click="$set('showPreviewModal', false)" 
                                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                                    title="Close Preview">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    @if (str_contains($reportData['type'] ?? '', 'Form 138'))
                        <!-- Duplex printing instructions -->
                        <div class="bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/60 px-4 sm:px-6 py-2.5 flex items-center justify-between text-xs text-emerald-900 dark:text-emerald-200 shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-circle-info text-[#166534] dark:text-emerald-400 text-sm"></i>
                                <span><strong>Duplex (Back-to-Back) Print Instructions:</strong> In your browser print dialog, set Orientation to <strong>Landscape</strong> and select <strong>Print on both sides (Flip on short edge)</strong>. Then fold down the center crease to form the official 4-page report card booklet.</span>
                            </div>
                        </div>

                        <!-- Printable Content Body with Smooth Scrolling -->
                        <div class="flex-1 min-h-0 w-full overflow-y-auto overflow-x-auto p-4 sm:p-6 bg-zinc-200/80 dark:bg-zinc-950 flex flex-col items-center relative" 
                             id="report-card-viewport" 
                             style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch; scrollbar-gutter: stable;">
                            <div class="w-full flex justify-center py-2">
                                <x-report-card-booklet :reportData="$reportData" />
                            </div>

                            <!-- Floating Quick-Action Bar for Printing from anywhere in the scroll viewport -->
                            <div class="sticky bottom-4 right-4 self-end z-20 flex items-center gap-2 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md p-1.5 rounded-xl border border-zinc-300 dark:border-zinc-700 shadow-xl">
                                <button type="button" 
                                        onclick="window.printReportCardBooklet ? window.printReportCardBooklet() : printReportCardBooklet()" 
                                        class="px-3.5 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold text-xs rounded-lg transition flex items-center gap-2 shadow-sm cursor-pointer">
                                    <i class="fas fa-print"></i>
                                    <span>Print Back-to-Back</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('showPreviewModal', false)" 
                                        class="px-2.5 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs rounded-lg transition cursor-pointer">
                                    Close
                                </button>
                            </div>
                        </div>
                    @elseif (str_contains($reportData['type'] ?? '', 'Form 137'))
                        <!-- SF10 Printing Guide Alert Banner -->
                        <div class="bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/60 px-4 sm:px-6 py-2.5 flex items-center justify-between text-xs text-emerald-900 dark:text-emerald-200 shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-circle-info text-[#166534] dark:text-emerald-400 text-sm"></i>
                                <span><strong>SF10-JHS Duplex Print Instructions:</strong> Set Paper Size to <strong>A4 / Folio</strong> and select <strong>Print on both sides (Flip on long edge)</strong> to print Page 1 (Front: Grades 7–8) and Page 2 (Back: Grades 9–10 &amp; Certification) on a single permanent record sheet.</span>
                            </div>
                        </div>

                        <!-- SF10 Printable Body with Smooth Scrolling -->
                        <div class="flex-1 min-h-0 w-full overflow-y-auto overflow-x-auto p-4 sm:p-6 bg-zinc-200/80 dark:bg-zinc-950 flex flex-col items-center relative" 
                             id="sf10-record-viewport" 
                             style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch; scrollbar-gutter: stable;">
                            <div class="w-full flex justify-center py-2">
                                <x-sf10-permanent-record :reportData="$reportData" />
                            </div>

                            <!-- Floating Quick-Action Bar -->
                            <div class="sticky bottom-4 right-4 self-end z-20 flex items-center gap-2 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md p-1.5 rounded-xl border border-zinc-300 dark:border-zinc-700 shadow-xl">
                                <button type="button" 
                                        onclick="window.printSf10Record ? window.printSf10Record() : window.print()" 
                                        class="px-3.5 py-1.5 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold text-xs rounded-lg transition flex items-center gap-2 shadow-sm cursor-pointer">
                                    <i class="fas fa-print"></i>
                                    <span>Print Back-to-Back</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('showPreviewModal', false)" 
                                        class="px-2.5 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs rounded-lg transition cursor-pointer">
                                    Close
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Printable Content Body for Section Summary -->
                        <div id="printable-report-area" class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 space-y-6 text-zinc-900 dark:text-zinc-100 bg-white">
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
        @endif
    @endif
    <!-- Script initialization inside root element to ensure availability in SPA/Livewire morphs -->
    <script>
        if (typeof window.printReportCardBooklet === 'undefined') {
            window.printReportCardBooklet = function() {
                const sheetContainer = document.getElementById('report-card-booklet-sheets');
                if (!sheetContainer) {
                    window.print();
                    return;
                }
                const clone = sheetContainer.cloneNode(true);
                clone.querySelectorAll('.report-card-sheet').forEach(sheet => {
                    sheet.style.removeProperty('display');
                    sheet.style.setProperty('display', 'flex', 'important');
                    sheet.style.setProperty('visibility', 'visible', 'important');
                    sheet.removeAttribute('x-show');
                });
                clone.querySelectorAll('.fold-guide, .no-print').forEach(el => el.remove());
                const printableSheetsHtml = clone.innerHTML;
                const parentStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
                    .map(el => el.outerHTML)
                    .join('\n');

                const old = document.getElementById('report-card-print-frame');
                if (old) old.remove();

                const iframe = document.createElement('iframe');
                iframe.id = 'report-card-print-frame';
                iframe.style.position = 'fixed';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.width = '100vw';
                iframe.style.height = '100vh';
                iframe.style.border = 'none';
                iframe.style.opacity = '0.001';
                iframe.style.pointerEvents = 'none';
                iframe.style.zIndex = '-99999';
                document.body.appendChild(iframe);

                const iframeDoc = iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write('<!' + 'DOCTYPE html><' + 'html lang="en"><' + 'head><' + 'meta charset="utf-8"><' + '/head><' + 'body><' + '/body><' + '/html>');
                iframeDoc.close();

                iframeDoc.title = "DepEd Form 138-JHS • Learner's Progress Report Card";
                iframeDoc.head.innerHTML = parentStyles;

                const printStyle = iframeDoc.createElement('style');
                printStyle.textContent = [
                    '@page { size: landscape; margin: 4mm 6mm; }',
                    '* { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }',
                    'html, body { background: #ffffff !important; color: #002b55 !important; font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif; margin: 0 !important; padding: 0 !important; width: 100% !important; }',
                    '.report-card-sheet { width: 100% !important; height: 98vh !important; min-height: 98vh !important; box-sizing: border-box !important; padding: 4mm 4mm !important; display: flex !important; flex-direction: row !important; justify-content: space-between !important; page-break-after: always !important; break-after: page !important; background: #ffffff !important; border: none !important; box-shadow: none !important; }',
                    '.report-card-sheet:last-child { page-break-after: avoid !important; break-after: avoid !important; }',
                    '.report-page-half { width: 50% !important; height: 100% !important; box-sizing: border-box !important; padding: 0 4mm !important; display: flex !important; flex-direction: column !important; justify-content: space-between !important; }',
                    '.vertical-header { writing-mode: vertical-rl; transform: rotate(180deg); text-orientation: mixed; white-space: nowrap; font-size: 8px; font-weight: 800; height: 52px; line-height: 1; display: inline-block; padding-top: 3px; letter-spacing: 0.05em; color: #002b55 !important; }',
                    'table { border-collapse: collapse !important; border: 1.5px solid #002b55 !important; width: 100%; }',
                    'th, td { border: 1px solid #002b55 !important; color: #002b55 !important; }',
                    '.fold-guide, .no-print { display: none !important; }'
                ].join('\n');
                iframeDoc.head.appendChild(printStyle);

                iframeDoc.body.className = 'bg-white text-[#002b55]';
                const wrapper = iframeDoc.createElement('div');
                wrapper.style.width = '100%';
                wrapper.style.display = 'flex';
                wrapper.style.flexDirection = 'column';
                wrapper.innerHTML = printableSheetsHtml;
                iframeDoc.body.appendChild(wrapper);

                function doPrint() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }

                const imgs = iframeDoc.images;
                let pending = 0;
                for (let i = 0; i < imgs.length; i++) {
                    if (!imgs[i].complete) {
                        pending++;
                        imgs[i].onload = () => { pending--; if (pending === 0) doPrint(); };
                        imgs[i].onerror = () => { pending--; if (pending === 0) doPrint(); };
                    }
                }
                if (pending === 0) doPrint();
                else setTimeout(doPrint, 250);
            };
        }

        if (typeof window.printSf10Record === 'undefined') {
            window.printSf10Record = function() {
                const sheetContainer = document.getElementById('sf10-record-sheets');
                if (!sheetContainer) {
                    window.print();
                    return;
                }
                const clone = sheetContainer.cloneNode(true);
                clone.querySelectorAll('.sf10-sheet').forEach(sheet => {
                    sheet.style.removeProperty('display');
                    sheet.style.setProperty('display', 'flex', 'important');
                    sheet.style.setProperty('visibility', 'visible', 'important');
                    sheet.removeAttribute('x-show');
                });
                clone.querySelectorAll('.no-print').forEach(el => el.remove());
                const printableSheetsHtml = clone.innerHTML;
                const parentStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
                    .map(el => el.outerHTML)
                    .join('\n');

                const old = document.getElementById('report-card-print-frame');
                if (old) old.remove();

                const iframe = document.createElement('iframe');
                iframe.id = 'report-card-print-frame';
                iframe.style.position = 'fixed';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.width = '100vw';
                iframe.style.height = '100vh';
                iframe.style.border = 'none';
                iframe.style.opacity = '0.001';
                iframe.style.pointerEvents = 'none';
                iframe.style.zIndex = '-99999';
                document.body.appendChild(iframe);

                const iframeDoc = iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write('<!' + 'DOCTYPE html><' + 'html lang="en"><' + 'head><' + 'meta charset="utf-8"><' + '/head><' + 'body><' + '/body><' + '/html>');
                iframeDoc.close();

                iframeDoc.title = "DepEd Form 137-JHS (SF10) • Learner's Permanent Academic Record";
                iframeDoc.head.innerHTML = parentStyles;

                const printStyle = iframeDoc.createElement('style');
                printStyle.textContent = [
                    '@page { size: auto; margin: 5mm 6mm; }',
                    '* { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }',
                    'html, body { background: #ffffff !important; color: #002b55 !important; font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif; margin: 0 !important; padding: 0 !important; width: 100% !important; }',
                    '.sf10-sheet { width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; padding: 3mm 3mm !important; display: flex !important; flex-direction: column !important; justify-content: space-between !important; page-break-after: always !important; break-after: page !important; background: #ffffff !important; border: none !important; box-shadow: none !important; }',
                    '.sf10-sheet:last-child { page-break-after: avoid !important; break-after: avoid !important; }',
                    '.sf10-scholastic-grid { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 3mm !important; }',
                    'table { border-collapse: collapse !important; border: 1.5px solid #002b55 !important; width: 100%; }',
                    'th, td { border: 1px solid #002b55 !important; color: #002b55 !important; }',
                    '.no-print { display: none !important; }'
                ].join('\n');
                iframeDoc.head.appendChild(printStyle);

                iframeDoc.body.className = 'bg-white text-[#002b55]';
                const wrapper = iframeDoc.createElement('div');
                wrapper.style.width = '100%';
                wrapper.style.display = 'flex';
                wrapper.style.flexDirection = 'column';
                wrapper.innerHTML = printableSheetsHtml;
                iframeDoc.body.appendChild(wrapper);

                function doPrint() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }

                const imgs = iframeDoc.images;
                let pending = 0;
                for (let i = 0; i < imgs.length; i++) {
                    if (!imgs[i].complete) {
                        pending++;
                        imgs[i].onload = () => { pending--; if (pending === 0) doPrint(); };
                        imgs[i].onerror = () => { pending--; if (pending === 0) doPrint(); };
                    }
                }
                if (pending === 0) doPrint();
                else setTimeout(doPrint, 250);
            };
        }

        if (typeof window.printReportDocument === 'undefined') {
            window.printReportDocument = function(elementId, title) {
                const target = document.getElementById(elementId);
                if (!target) {
                    window.print();
                    return;
                }
                const clone = target.cloneNode(true);
                clone.querySelectorAll('.no-print').forEach(el => el.remove());
                const contentHtml = clone.innerHTML;
                const parentStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
                    .map(el => el.outerHTML)
                    .join('\n');

                const old = document.getElementById('report-card-print-frame');
                if (old) old.remove();

                const iframe = document.createElement('iframe');
                iframe.id = 'report-card-print-frame';
                iframe.style.position = 'fixed';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.width = '100vw';
                iframe.style.height = '100vh';
                iframe.style.border = 'none';
                iframe.style.opacity = '0.001';
                iframe.style.pointerEvents = 'none';
                iframe.style.zIndex = '-99999';
                document.body.appendChild(iframe);

                const iframeDoc = iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write('<!' + 'DOCTYPE html><' + 'html lang="en"><' + 'head><' + 'meta charset="utf-8"><' + '/head><' + 'body><' + '/body><' + '/html>');
                iframeDoc.close();

                iframeDoc.title = title || 'DepEd Academic Report';
                iframeDoc.head.innerHTML = parentStyles;

                const printStyle = iframeDoc.createElement('style');
                printStyle.textContent = [
                    '@page { size: auto; margin: 8mm 10mm; }',
                    '* { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }',
                    'html, body { background: #ffffff !important; color: #18181b !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }',
                    '.no-print { display: none !important; }'
                ].join('\n');
                iframeDoc.head.appendChild(printStyle);

                iframeDoc.body.className = 'bg-white text-zinc-900 p-6';
                iframeDoc.body.innerHTML = contentHtml;

                function doPrint() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }

                const imgs = iframeDoc.images;
                let pending = 0;
                for (let i = 0; i < imgs.length; i++) {
                    if (!imgs[i].complete) {
                        pending++;
                        imgs[i].onload = () => { pending--; if (pending === 0) doPrint(); };
                        imgs[i].onerror = () => { pending--; if (pending === 0) doPrint(); };
                    }
                }
                if (pending === 0) doPrint();
                else setTimeout(doPrint, 250);
            };
        }
    </script>
</div>
