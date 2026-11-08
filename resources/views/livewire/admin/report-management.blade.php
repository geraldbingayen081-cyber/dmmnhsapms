<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-file-pdf text-[#166534]"></i>
                <span>Official Academic Reports & Form Generation</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Generate and preview Form 138 (SF9), Form 137 (SF10), and Section Grade Summary Sheets for DMMNHS.</p>
        </div>
    </div>

    <!-- Report Type Selection Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div wire:click="$set('reportType', 'form138')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'form138' ? 'bg-[#DCFCE7]/30 dark:bg-emerald-950/40 border-[#166534] dark:border-emerald-500 shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-[#166534] dark:text-emerald-400 uppercase tracking-wider">SF9 / Form 138</span>
                <i class="fas fa-id-card text-xl text-[#166534] dark:text-emerald-400"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Student Progress Report Card</h4>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Term core subject grades, final rating, and remarks per student.</p>
            </div>
        </div>

        <div wire:click="$set('reportType', 'form137')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'form137' ? 'bg-[#DCFCE7]/30 dark:bg-emerald-950/40 border-[#166534] dark:border-emerald-500 shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-blue-700 dark:text-blue-400 uppercase tracking-wider">SF10 / Form 137</span>
                <i class="fas fa-clock-rotate-left text-xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Permanent Academic Record</h4>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Multi-year academic placement history and general averages across Grade 7 to 10.</p>
            </div>
        </div>

        <div wire:click="$set('reportType', 'summary_sheet')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'summary_sheet' ? 'bg-[#DCFCE7]/30 dark:bg-emerald-950/40 border-[#166534] dark:border-emerald-500 shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-amber-700 dark:text-amber-400 uppercase tracking-wider">Master Sheet</span>
                <i class="fas fa-table-list text-xl text-amber-600 dark:text-amber-400"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Summary of Term Grades</h4>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Section master sheet listing all enrolled students and subject grade ratings.</p>
            </div>
        </div>
    </div>

    <!-- Parameter Configurator Form Card -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-5 text-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <i class="fas fa-sliders text-[#166534]"></i>
            <span>Report Generation Parameters</span>
        </h3>

        <form wire:submit.prevent="generateReport" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target School Year</label>
                    <select wire:model.defer="selectedSchoolYearId" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <option value="all">All School Years</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy->id }}">SY {{ $sy->school_year }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($reportType === 'form138' || $reportType === 'form137')
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Student *</label>
                        <select wire:model.defer="selectedStudentId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Choose Student...</option>
                            @foreach ($students as $st)
                                <option value="{{ $st->id }}">{{ $st->student_number }} - {{ $st->full_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedStudentId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if ($reportType === 'summary_sheet')
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Section Master *</label>
                        <select wire:model.defer="selectedSectionId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Choose Section...</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedSectionId') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-file-invoice"></i>
                    <span>Generate & Preview Document</span>
                </button>
            </div>
        </form>
    </div>

    <!-- INTERACTIVE REPORT PREVIEW MODAL -->
    @if ($showPreviewModal && $reportData)
        <div class="fixed inset-0 z-50 flex flex-col bg-zinc-950/80 backdrop-blur-md overflow-hidden p-2 sm:p-4" 
             x-data="{ activeSpread: 'all' }"
             x-init="document.body.style.overflow = 'hidden'"
             x-effect="() => { return () => { document.body.style.overflow = ''; } }"
             x-on:keydown.escape.window="$wire.set('showPreviewModal', false)">
            
            <!-- Backdrop Click Area -->
            <div class="fixed inset-0 -z-10" wire:click="$set('showPreviewModal', false)"></div>

            <!-- Modal Window -->
            <div class="relative w-full {{ in_array($reportData['type'], ['Form 138 (Report Card)', 'Form 137 (Permanent Academic Record)']) ? 'max-w-7xl' : 'max-w-4xl' }} mx-auto flex-1 min-h-0 flex flex-col bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden text-xs">
                
                <!-- Modal Header -->
                <div class="p-3.5 sm:p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3 bg-zinc-50 dark:bg-zinc-800/90 shrink-0">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas {{ $reportData['type'] === 'Form 138 (Report Card)' ? 'fa-id-card text-[#166534]' : ($reportData['type'] === 'Form 137 (Permanent Academic Record)' ? 'fa-clock-rotate-left text-[#166534]' : 'fa-file-lines text-[#166534]') }}"></i>
                            <span>
                                @if ($reportData['type'] === 'Form 138 (Report Card)')
                                    DepEd Form 138-JHS (SF9) &bull; Learner's Progress Report Card
                                @elseif ($reportData['type'] === 'Form 137 (Permanent Academic Record)')
                                    DepEd Form 137-JHS (SF10) &bull; Learner's Permanent Academic Record
                                @else
                                    {{ $reportData['type'] }} Preview
                                @endif
                            </span>
                        </h3>
                        @if ($reportData['type'] === 'Form 138 (Report Card)')
                            <p class="text-[11px] text-zinc-500 mt-0.5">
                                {{ $reportData['student']?->full_name }} &bull; {{ $reportData['grade_level'] }} - {{ $reportData['section_name'] }} (SY {{ $reportData['school_year'] }})
                            </p>
                        @elseif ($reportData['type'] === 'Form 137 (Permanent Academic Record)')
                            <p class="text-[11px] text-zinc-500 mt-0.5">
                                {{ $reportData['student']?->full_name }} &bull; LRN: <span class="font-mono font-bold">{{ $reportData['student']?->student_number }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center flex-wrap gap-2.5">
                        @if ($reportData['type'] === 'Form 138 (Report Card)')
                            <!-- Spread Selector Tabs -->
                            <div class="inline-flex rounded-lg bg-zinc-200 dark:bg-zinc-700/60 p-0.5 text-[11px] font-bold">
                                <button type="button" 
                                        @click="activeSpread = 'all'" 
                                        :class="activeSpread === 'all' ? 'bg-[#166534] text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                                        class="px-2.5 py-1 rounded-md transition cursor-pointer">
                                    Full Booklet (Both Spreads)
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
                        @elseif ($reportData['type'] === 'Form 137 (Permanent Academic Record)')
                            <!-- SF10 Page Selector Tabs -->
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

                            <!-- SF10 Print Button -->
                            <button type="button" 
                                    onclick="window.printSf10Record ? window.printSf10Record() : window.print()" 
                                    class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold rounded-xl transition text-xs flex items-center gap-2 shadow-md hover:shadow-lg cursor-pointer ring-2 ring-emerald-500/30">
                                <i class="fas fa-print text-sm"></i>
                                <span>Print Back-to-Back</span>
                            </button>
                        @else
                            <button type="button" 
                                     onclick="window.printReportDocument ? window.printReportDocument('printable-document', '{{ $reportData['type'] ?? 'DepEd Academic Report' }}') : window.print()" 
                                    class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-extrabold rounded-xl transition text-xs flex items-center gap-2 shadow-md cursor-pointer">
                                <i class="fas fa-print"></i>
                                <span>Print / Save PDF</span>
                            </button>
                        @endif

                        <button wire:click="$set('showPreviewModal', false)" 
                                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                                title="Close Preview">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>
                </div>

                @if ($reportData['type'] === 'Form 138 (Report Card)')
                    <!-- Back to Back Printing Guide Alert Banner -->
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/60 px-4 sm:px-6 py-2.5 flex items-center justify-between text-xs text-emerald-900 dark:text-emerald-200 shrink-0">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-circle-info text-[#166534] dark:text-emerald-400 text-sm"></i>
                            <span><strong>Duplex (Back-to-Back) Print Instructions:</strong> In your browser print dialog, set Orientation to <strong>Landscape</strong> and select <strong>Print on both sides (Flip on short edge)</strong>. Then fold down the center crease to form the official 4-page report card booklet.</span>
                        </div>
                    </div>

                    <!-- Booklet Printable Body with Smooth Scrolling -->
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
                @elseif ($reportData['type'] === 'Form 137 (Permanent Academic Record)')
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
                    <!-- Document Printable Body for Summary Sheet -->
                    <div class="flex-1 min-h-0 overflow-y-auto p-8 space-y-6 font-serif text-zinc-900 bg-white" id="printable-document">
                        <!-- School Header -->
                        <div class="flex items-center justify-between border-b-2 border-zinc-900 pb-4">
                            @php $adminDocLogo = \App\Models\SystemSetting::logoUrl(); @endphp
                            @if ($adminDocLogo)
                                <div class="size-16 shrink-0">
                                    <img src="{{ $adminDocLogo }}" alt="Logo" class="w-full h-full object-contain">
                                </div>
                            @else
                                <div class="size-16 rounded-2xl bg-[#166534] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                    DMMNHS
                                </div>
                            @endif
                            <div class="text-center space-y-1 flex-1 px-4">
                                <span class="text-[10px] uppercase font-mono tracking-widest text-zinc-500 block">Republic of the Philippines &bull; Department of Education</span>
                                <h1 class="text-base font-bold uppercase tracking-wider font-sans text-zinc-900">{{ \App\Models\SystemSetting::get('school_name', 'Don Mariano Marcos National High School') }}</h1>
                                <p class="text-xs text-zinc-600 font-sans">{{ \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan, Philippines') }} &bull; Junior High School Division</p>
                                <h2 class="text-sm font-bold uppercase tracking-widest text-[#166534] pt-1 font-sans">{{ $reportData['type'] }}</h2>
                            </div>
                            <div class="size-16 shrink-0 flex items-center justify-center">
                                <div class="size-14 rounded-full border border-zinc-300 flex items-center justify-center text-[9px] font-mono text-zinc-400">
                                    DEPED
                                </div>
                            </div>
                        </div>

                        <!-- Summary Sheet Content -->
                        <div class="space-y-4 font-sans text-xs">
                            <div class="border-b border-zinc-200 pb-3">
                                <h3 class="font-bold text-sm text-zinc-900">Section Master Grade Sheet</h3>
                                <span class="text-xs text-zinc-600 font-medium">Class Adviser: {{ $reportData['section_instance']?->adviser?->full_name ?? 'Unassigned' }}</span>
                            </div>

                            <table class="w-full text-xs text-start border-collapse border border-zinc-300">
                                <thead class="bg-zinc-100 text-zinc-700 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="border border-zinc-300 py-2 px-3 text-start">Student Number</th>
                                        <th class="border border-zinc-300 py-2 px-3 text-start">Student Name</th>
                                        <th class="border border-zinc-300 py-2 px-3 text-center">General Average</th>
                                        <th class="border border-zinc-300 py-2 px-3 text-center">Lowest Grade</th>
                                        <th class="border border-zinc-300 py-2 px-3 text-center">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach ($reportData['students'] as $st)
                                        <tr>
                                            <td class="border border-zinc-300 py-2 px-3 font-mono font-bold text-[#166534]">{{ $st['student_number'] }}</td>
                                            <td class="border border-zinc-300 py-2 px-3 font-bold">{{ $st['full_name'] }}</td>
                                            <td class="border border-zinc-300 py-2 px-3 text-center font-mono font-bold">{{ $st['average'] ? number_format($st['average'], 2) : '-' }}</td>
                                            <td class="border border-zinc-300 py-2 px-3 text-center font-mono">{{ $st['lowest'] ? number_format($st['lowest'], 2) : '-' }}</td>
                                            <td class="border border-zinc-300 py-2 px-3 text-center font-bold text-[10px] {{ $st['remarks'] === 'PASSED' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $st['remarks'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
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
