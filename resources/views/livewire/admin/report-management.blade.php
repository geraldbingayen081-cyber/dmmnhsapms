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
        <div wire:click="$set('reportType', 'form138')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'form138' ? 'bg-[#DCFCE7]/30 border-[#166534] shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-[#166534] uppercase tracking-wider">SF9 / Form 138</span>
                <i class="fas fa-id-card text-xl text-[#166534]"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Student Progress Report Card</h4>
                <p class="text-[11px] text-zinc-500 mt-0.5">Quarterly core subject grades, final rating, and remarks per student.</p>
            </div>
        </div>

        <div wire:click="$set('reportType', 'form137')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'form137' ? 'bg-[#DCFCE7]/30 border-[#166534] shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-blue-700 uppercase tracking-wider">SF10 / Form 137</span>
                <i class="fas fa-clock-rotate-left text-xl text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Permanent Academic Record</h4>
                <p class="text-[11px] text-zinc-500 mt-0.5">Multi-year academic placement history and general averages across Grade 7 to 10.</p>
            </div>
        </div>

        <div wire:click="$set('reportType', 'summary_sheet')" class="p-5 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between space-y-3 {{ $reportType === 'summary_sheet' ? 'bg-[#DCFCE7]/30 border-[#166534] shadow-sm' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}">
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-amber-700 uppercase tracking-wider">Master Sheet</span>
                <i class="fas fa-[#166534] fa-table-list text-xl text-amber-600"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Summary of Quarterly Grades</h4>
                <p class="text-[11px] text-zinc-500 mt-0.5">Section master sheet listing all enrolled students and subject grade ratings.</p>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm" wire:click="$set('showPreviewModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col relative z-10 overflow-hidden text-xs">
                
                <!-- Modal Header -->
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/80">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-file-lines text-[#166534]"></i>
                        <span>{{ $reportData['type'] }} Preview</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white font-bold rounded-lg transition text-xs flex items-center gap-1.5">
                            <i class="fas fa-print"></i>
                            <span>Print / Save PDF</span>
                        </button>
                        <button wire:click="$set('showPreviewModal', false)" class="text-zinc-400 hover:text-zinc-600 p-1.5">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>
                </div>

                <!-- Document Printable Body -->
                <div class="p-8 overflow-y-auto space-y-6 font-serif text-zinc-900 bg-white" id="printable-document">
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

                    @if ($reportData['type'] === 'Form 138 (Report Card)')
                        <!-- Form 138 Content -->
                        <div class="grid grid-cols-2 gap-4 text-xs font-sans border-b border-zinc-200 pb-4">
                            <div>
                                <span class="text-zinc-500 block text-[10px] uppercase font-bold">Student Name:</span>
                                <span class="font-bold text-sm text-zinc-900">{{ $reportData['student']?->full_name }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-500 block text-[10px] uppercase font-bold">Student Number:</span>
                                <span class="font-mono font-bold text-sm text-[#166534]">{{ $reportData['student']?->student_number }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-500 block text-[10px] uppercase font-bold">Grade Level & Section:</span>
                                <span class="font-bold text-zinc-800">{{ $reportData['enrollment']?->schoolYearSection?->yearLevel?->name }} - {{ $reportData['enrollment']?->schoolYearSection?->section?->name }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-500 block text-[10px] uppercase font-bold">Class Adviser:</span>
                                <span class="font-bold text-zinc-800">{{ $reportData['enrollment']?->schoolYearSection?->adviser?->full_name ?? 'Unassigned' }}</span>
                            </div>
                        </div>

                        <table class="w-full text-xs font-sans text-start border-collapse border border-zinc-300">
                            <thead class="bg-zinc-100 text-zinc-700 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="border border-zinc-300 py-2 px-3 text-start">Learning Area / Core Subject</th>
                                    <th class="border border-zinc-300 py-2 px-2 text-center w-12">Q1</th>
                                    <th class="border border-zinc-300 py-2 px-2 text-center w-12">Q2</th>
                                    <th class="border border-zinc-300 py-2 px-2 text-center w-12">Q3</th>
                                    <th class="border border-zinc-300 py-2 px-3 text-center w-24">Final Grade</th>
                                    <th class="border border-zinc-300 py-2 px-3 text-center w-24">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($reportData['subjects'] as $sub)
                                    <tr>
                                        <td class="border border-zinc-300 py-2 px-3 font-bold">{{ $sub['code'] }} - {{ $sub['name'] }}</td>
                                        <td class="border border-zinc-300 py-2 px-2 text-center font-mono">{{ $sub['quarters']['Q1'] ?? '-' }}</td>
                                        <td class="border border-zinc-300 py-2 px-2 text-center font-mono">{{ $sub['quarters']['Q2'] ?? '-' }}</td>
                                        <td class="border border-zinc-300 py-2 px-2 text-center font-mono">{{ $sub['quarters']['Q3'] ?? '-' }}</td>
                                        <td class="border border-zinc-300 py-2 px-3 text-center font-mono font-bold text-[#166534]">
                                            {{ $sub['final'] ? number_format($sub['final'], 2) : '-' }}
                                        </td>
                                        <td class="border border-zinc-300 py-2 px-3 text-center font-bold text-[10px] {{ $sub['remarks'] === 'PASSED' ? 'text-emerald-700' : ($sub['remarks'] === 'FAILED' ? 'text-rose-700' : 'text-zinc-500') }}">
                                            {{ $sub['remarks'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="flex justify-between items-center pt-4 font-sans text-xs border-t border-zinc-200">
                            <div>
                                <span class="text-zinc-500 font-bold uppercase text-[10px] block">General Average:</span>
                                <span class="text-xl font-bold text-[#166534] font-mono">{{ $reportData['general_average'] ? number_format($reportData['general_average'], 2) : 'N/A' }}</span>
                            </div>
                            <div class="text-end">
                                <span class="text-zinc-500 text-[10px] uppercase font-bold block">Status Result:</span>
                                <span class="px-3 py-1 bg-[#DCFCE7] text-[#166534] font-bold text-xs rounded border border-emerald-300">
                                    {{ $reportData['general_average'] >= 75 ? 'PROMOTED TO NEXT GRADE LEVEL' : 'RETAINED' }}
                                </span>
                            </div>
                        </div>
                    @elseif ($reportData['type'] === 'Form 137 (Permanent Academic Record)')
                        <!-- Form 137 Content -->
                        <div class="space-y-4 font-sans text-xs">
                            <div class="border-b border-zinc-200 pb-3">
                                <h3 class="font-bold text-sm text-zinc-900">{{ $reportData['student']?->full_name }}</h3>
                                <span class="font-mono text-xs text-[#166534] font-bold">LRN / Student Number: {{ $reportData['student']?->student_number }}</span>
                            </div>

                            <div class="space-y-3">
                                @foreach ($reportData['history'] as $hist)
                                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200 flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-zinc-900 block">SY {{ $hist['school_year'] }} &bull; {{ $hist['year_level'] }} ({{ $hist['section'] }})</span>
                                            <span class="text-[10px] text-zinc-500">Placement Status: <strong class="uppercase text-emerald-700">{{ $hist['status'] }}</strong></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[10px] text-zinc-400 block uppercase font-bold">General Average</span>
                                            <span class="font-mono font-bold text-sm text-[#166534]">{{ $hist['general_average'] ? number_format($hist['general_average'], 2) : 'N/A' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
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
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
