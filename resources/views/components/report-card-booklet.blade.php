@props(['reportData'])

@php
    $student = $reportData['student'] ?? null;
    $studentFullName = $student ? strtoupper(trim("{$student->last_name}, {$student->first_name} {$student->middle_name}" . ($student->suffix ? " {$student->suffix}" : ''))) : 'LEARNER NAME';
    $core = $reportData['core_subjects'] ?? [];
    $others = $reportData['other_subjects'] ?? [];
    $genAvg = $reportData['general_average'] ?? null;

    $defaultSubject = ['q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''];
    foreach (['FIL', 'ENG', 'MAT', 'SCI', 'AP', 'ESP', 'TLE', 'MAPEH'] as $code) {
        if (!isset($core[$code])) {
            $core[$code] = $defaultSubject;
        }
    }
@endphp

<!-- Dedicated DepEd SF9 Booklet Container -->
<div id="report-card-booklet-sheets" class="w-full flex flex-col items-center gap-8 text-[#002b55] select-text">

    <!-- ==========================================
         SHEET 1: OUTSIDE SPREAD (PAGE 4 & PAGE 1)
         When folded: Page 1 is Front Cover, Page 4 is Back Cover
         ========================================== -->
    <div x-show="activeSpread === 'all' || activeSpread === 'outside'"
         class="report-card-sheet print-sheet w-full max-w-[1020px] bg-white rounded-lg shadow-xl border border-zinc-200 relative p-6 sm:p-8 flex flex-col sm:flex-row gap-6 sm:gap-8 justify-between"
         style="box-sizing: border-box; min-height: 680px;"
         data-sheet="outside">

        <!-- Left Half: PAGE 4 (BACK COVER) -->
        <div class="report-page-half print-page-half w-full sm:w-1/2 flex flex-col justify-between pr-0 sm:pr-4">
            
            <!-- Attendance Section -->
            <div>
                <h3 class="text-center font-bold tracking-wider text-xs uppercase mb-2 font-serif" style="color: #002b55;">
                    ATTENDANCE RECORD
                </h3>
                
                <table class="w-full text-center border-collapse text-[9px]" style="border: 1.5px solid #002b55;">
                    <thead>
                        <tr class="h-14">
                            <th class="border border-[#002b55] p-0.5 text-start w-24"></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">JUNE</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">JULY</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">AUGUST</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">SEPTEMBER</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">OCTOBER</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">NOVEMBER</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">DECEMBER</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">JANUARY</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">FEBRUARY</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">MARCH</div></th>
                            <th class="border border-[#002b55] p-0.5"><div class="vertical-header">APRIL</div></th>
                            <th class="border border-[#002b55] p-0.5 font-bold"><div class="vertical-header">TOTAL</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="h-6">
                            <td class="border border-[#002b55] px-1 text-start font-bold text-[9.5px]">No. of School Days</td>
                            @for ($i = 0; $i < 12; $i++)
                                <td class="border border-[#002b55]"></td>
                            @endfor
                        </tr>
                        <tr class="h-6">
                            <td class="border border-[#002b55] px-1 text-start font-bold text-[9.5px]">No. of Days Present</td>
                            @for ($i = 0; $i < 12; $i++)
                                <td class="border border-[#002b55]"></td>
                            @endfor
                        </tr>
                        <tr class="h-6">
                            <td class="border border-[#002b55] px-1 text-start font-bold text-[9.5px]">No. of Days Absent</td>
                            @for ($i = 0; $i < 12; $i++)
                                <td class="border border-[#002b55]"></td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Parents' / Guardian's Signature Section -->
            <div class="mt-4">
                <h4 class="text-center font-bold text-[10.5px] uppercase tracking-wider mb-2 font-serif">
                    PARENTS' / GUARDIAN'S SIGNATURE
                </h4>
                <div class="space-y-2.5 text-[10.5px] px-2 font-medium">
                    <div class="flex items-end gap-2">
                        <span class="w-18 font-bold shrink-0">1st Term</span>
                        <div class="border-b border-[#002b55] flex-1 h-3.5"></div>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="w-18 font-bold shrink-0">2nd Term</span>
                        <div class="border-b border-[#002b55] flex-1 h-3.5"></div>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="w-18 font-bold shrink-0">3rd Term</span>
                        <div class="border-b border-[#002b55] flex-1 h-3.5"></div>
                    </div>
                </div>
            </div>

            <!-- Certificate of Transfer -->
            <div class="mt-4 pt-2">
                <h4 class="text-center font-bold text-[11.5px] font-serif mb-2">
                    Certificate of Transfer
                </h4>
                <div class="text-[10px] space-y-2 px-1 leading-normal font-medium">
                    <div class="flex items-end gap-1.5">
                        <span class="shrink-0 font-semibold">Admitted to Grade:</span>
                        <div class="border-b border-[#002b55] flex-1 h-3"></div>
                        <span class="shrink-0 ml-1 font-semibold">Section:</span>
                        <div class="border-b border-[#002b55] w-20 h-3"></div>
                    </div>
                    <div class="flex items-end gap-1.5">
                        <span class="shrink-0 font-semibold">Eligibility for Admission to Grade:</span>
                        <div class="border-b border-[#002b55] flex-1 h-3"></div>
                    </div>
                    <div class="pt-1 font-semibold">
                        <span>Approved:</span>
                    </div>
                    <div class="flex items-start justify-between pt-3 gap-4">
                        <div class="text-center flex-1">
                            <div class="font-bold uppercase tracking-wider text-[11px] border-b border-[#002b55] pb-0.5">
                                {{ $reportData['principal_name'] }}
                            </div>
                            <div class="italic text-[10px] font-medium">{{ $reportData['principal_title'] }}</div>
                        </div>
                        <div class="text-center flex-1">
                            <div class="border-b border-[#002b55] pb-0.5 text-[11px] font-bold">
                                {{ $reportData['adviser_name'] ?: '&nbsp;' }}
                            </div>
                            <div class="italic text-[10px] font-medium">Adviser</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation of Eligibility to Transfer -->
            <div class="mt-4 pt-2">
                <h4 class="text-center font-bold text-[11.5px] font-serif mb-2">
                    Cancellation of Eligibility to Transfer
                </h4>
                <div class="text-[10px] space-y-2 px-1 font-medium">
                    <div class="flex items-end gap-1.5">
                        <span class="shrink-0 font-semibold">Admitted in:</span>
                        <div class="border-b border-[#002b55] flex-1 h-3"></div>
                    </div>
                    <div class="flex items-end gap-1.5">
                        <span class="shrink-0 font-semibold">Date:</span>
                        <div class="border-b border-[#002b55] flex-1 h-3"></div>
                    </div>
                    <div class="flex justify-end pt-3">
                        <div class="text-center w-48">
                            <div class="font-bold uppercase tracking-wider text-[11px] border-b border-[#002b55] pb-0.5">
                                {{ $reportData['principal_name'] }}
                            </div>
                            <div class="italic text-[10px] font-medium">{{ $reportData['principal_title'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center Fold Guide (Preview Only, hidden in print) -->
        <div class="fold-guide hidden sm:block absolute inset-y-6 left-1/2 -translate-x-1/2 w-0 border-r border-dashed border-[#002b55]/20 pointer-events-none"></div>

        <!-- Right Half: PAGE 1 (FRONT COVER) -->
        <div class="report-page-half print-page-half w-full sm:w-1/2 flex flex-col justify-between pl-0 sm:pl-4">
            
            <!-- School Header -->
            <div class="text-center space-y-0.5">
                <div class="flex justify-center mb-1">
                    @php
                        $schoolLogo = $reportData['school_logo'] ?? \App\Models\SystemSetting::logoUrl();
                    @endphp
                    @if ($schoolLogo)
                        <img src="{{ $schoolLogo }}" alt="{{ $reportData['school_name'] ?? 'School Logo' }}" class="h-16 w-16 object-contain mx-auto" />
                    @else
                        <img src="/images/deped-logo.svg" alt="Department of Education" class="h-14 w-14 object-contain mx-auto" />
                    @endif
                </div>
                <p class="text-[10.5px] font-bold tracking-wide font-serif">Republic of the Philippines</p>
                <p class="text-[15px] font-bold tracking-wide" style="font-family: 'UnifrakturMaguntia', 'Old English Text MT', Georgia, serif;">
                    Department of Education
                </p>
                <p class="text-[9.5px] font-bold tracking-wider uppercase font-serif">{{ $reportData['school_region'] }}</p>
                <p class="text-[9.5px] font-bold tracking-wider uppercase font-serif">{{ $reportData['school_division'] }}</p>
                <p class="text-xs font-black tracking-wide uppercase font-serif pt-0.5">{{ $reportData['school_name'] }}</p>
                <p class="text-[9.5px] italic font-serif">{{ $reportData['school_address'] }}</p>
            </div>

            <!-- Title -->
            <div class="text-center my-2">
                <h2 class="font-black text-[13px] uppercase tracking-wider font-serif">
                    LEARNER'S PROGRESS REPORT CARD
                </h2>
            </div>

            <!-- Student Profile Fields -->
            <div class="text-[10.5px] space-y-2 px-2 font-medium">
                <div class="flex items-end gap-1.5">
                    <span class="font-bold shrink-0">Name:</span>
                    <div class="border-b border-[#002b55] flex-1 font-bold text-[11px] uppercase px-1 pb-0.5">
                        {{ $studentFullName }}
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="font-bold shrink-0">Learner's Reference Number:</span>
                    <div class="border-b border-[#002b55] flex-1 font-mono font-bold text-[11px] px-1 pb-0.5">
                        {{ $reportData['student']->student_number }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-end gap-1.5">
                        <span class="font-bold shrink-0">Age:</span>
                        <div class="border-b border-[#002b55] flex-1 font-bold px-1 pb-0.5">
                            {{ $reportData['age'] ?? '&nbsp;' }}
                        </div>
                    </div>
                    <div class="flex items-end gap-1.5">
                        <span class="font-bold shrink-0">Sex:</span>
                        <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1 pb-0.5">
                            {{ $reportData['sex'] ?? '&nbsp;' }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-end gap-1.5">
                        <span class="font-bold shrink-0">Grade:</span>
                        <div class="border-b border-[#002b55] flex-1 font-bold px-1 pb-0.5">
                            {{ $reportData['grade_level'] }}
                        </div>
                    </div>
                    <div class="flex items-end gap-1.5">
                        <span class="font-bold shrink-0">Section:</span>
                        <div class="border-b border-[#002b55] flex-1 font-bold px-1 pb-0.5">
                            {{ $reportData['section_name'] }}
                        </div>
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="font-bold shrink-0">School Year:</span>
                    <div class="border-b border-[#002b55] flex-1 font-bold px-1 pb-0.5">
                        {{ $reportData['school_year'] }}
                    </div>
                </div>
            </div>

            <!-- Dear Parent Letter -->
            <div class="mt-2 text-[10px] px-2 space-y-1 font-serif">
                <p class="font-bold">Dear Parent:</p>
                <p class="indent-5 text-justify leading-relaxed">
                    This report card shows the ability and progress your child has made in different learning areas as well as his/her core values.
                </p>
                <p class="indent-5 text-justify leading-relaxed">
                    The school welcomes you should you desire to know more about your child's progress.
                </p>
            </div>

            <!-- Signatures -->
            <div class="mt-4 px-2 space-y-2.5">
                <div class="flex justify-end">
                    <div class="text-center w-48">
                        <div class="border-b border-[#002b55] pb-0.5 text-[11px] font-bold">
                            {{ $reportData['adviser_name'] ?: '&nbsp;' }}
                        </div>
                        <div class="italic text-[10px] font-medium">Adviser</div>
                    </div>
                </div>
                <div class="flex justify-start">
                    <div class="text-center w-48">
                        <div class="font-bold uppercase tracking-wider text-[11px] border-b border-[#002b55] pb-0.5">
                            {{ $reportData['principal_name'] }}
                        </div>
                        <div class="italic text-[10px] font-medium">{{ $reportData['principal_title'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Marking / Non-Numerical Rating Guide -->
            <div class="mt-3 pt-1.5 border-t border-dashed border-[#002b55]/40 text-[9px] flex justify-end px-2">
                <div class="w-56 space-y-0.5">
                    <div class="flex font-bold justify-between border-b border-[#002b55]/40 pb-0.5 mb-0.5">
                        <span class="w-12">Marking</span>
                        <span class="flex-1 text-start">Non-Numerical Rating</span>
                    </div>
                    <div class="flex justify-between"><span class="w-12 font-bold">AO</span><span class="flex-1 text-start">Always Observed</span></div>
                    <div class="flex justify-between"><span class="w-12 font-bold">SO</span><span class="flex-1 text-start">Sometimes Observed</span></div>
                    <div class="flex justify-between"><span class="w-12 font-bold">RO</span><span class="flex-1 text-start">Rarely Observed</span></div>
                    <div class="flex justify-between"><span class="w-12 font-bold">NO</span><span class="flex-1 text-start">Not Observed</span></div>
                </div>
            </div>
        </div>
    </div>


    <!-- ==========================================
         SHEET 2: INSIDE SPREAD (PAGE 2 & PAGE 3)
         When open: Page 2 is on Left, Page 3 is on Right
         ========================================== -->
    <div x-show="activeSpread === 'all' || activeSpread === 'inside'"
         class="report-card-sheet print-sheet w-full max-w-[1020px] bg-white rounded-lg shadow-xl border border-zinc-200 relative p-6 sm:p-8 flex flex-col sm:flex-row gap-6 sm:gap-8 justify-between"
         style="box-sizing: border-box; min-height: 680px;"
         data-sheet="inside">

        <!-- Left Half: PAGE 2 (LEARNING PROGRESS AND ACHIEVEMENT) -->
        <div class="report-page-half print-page-half w-full sm:w-1/2 flex flex-col justify-between pr-0 sm:pr-4">
            
            <div>
                <h3 class="text-center font-bold tracking-wider text-[11.5px] uppercase mb-2 font-serif" style="color: #002b55;">
                    REPORT ON LEARNING PROGRESS AND ACHIEVEMENT
                </h3>

                <!-- Core Subjects Grades Table -->
                <table class="w-full text-center border-collapse text-[9.5px]" style="border: 1.5px solid #002b55;">
                    <thead>
                        <tr class="bg-zinc-50/50">
                            <th rowspan="2" class="border border-[#002b55] py-1.5 px-2 text-start font-bold w-[43%] text-[10px]">
                                Learning Areas
                            </th>
                            <th colspan="3" class="border border-[#002b55] py-0.5 font-bold text-[9.5px]">
                                Term
                            </th>
                            <th rowspan="2" class="border border-[#002b55] py-1.5 px-1 font-bold w-[14%] text-[9.5px]">
                                Final Rating
                            </th>
                            <th rowspan="2" class="border border-[#002b55] py-1.5 px-1 font-bold w-[16%] text-[9.5px]">
                                Remarks
                            </th>
                        </tr>
                        <tr class="bg-zinc-50/50">
                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">1</th>
                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">2</th>
                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">3</th>
                        </tr>
                    </thead>
                    <tbody class="font-medium">
                        <!-- 1. Filipino -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Filipino</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['FIL']['q1'] ? number_format($core['FIL']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['FIL']['q2'] ? number_format($core['FIL']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['FIL']['q3'] ? number_format($core['FIL']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['FIL']['final'] ? number_format($core['FIL']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['FIL']['remarks'] }}</td>
                        </tr>

                        <!-- 2. English -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">English</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ENG']['q1'] ? number_format($core['ENG']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ENG']['q2'] ? number_format($core['ENG']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ENG']['q3'] ? number_format($core['ENG']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['ENG']['final'] ? number_format($core['ENG']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['ENG']['remarks'] }}</td>
                        </tr>

                        <!-- 3. Mathematics -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Mathematics</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['MAT']['q1'] ? number_format($core['MAT']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['MAT']['q2'] ? number_format($core['MAT']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['MAT']['q3'] ? number_format($core['MAT']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['MAT']['final'] ? number_format($core['MAT']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['MAT']['remarks'] }}</td>
                        </tr>

                        <!-- 4. Science -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Science</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['SCI']['q1'] ? number_format($core['SCI']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['SCI']['q2'] ? number_format($core['SCI']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['SCI']['q3'] ? number_format($core['SCI']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['SCI']['final'] ? number_format($core['SCI']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['SCI']['remarks'] }}</td>
                        </tr>

                        <!-- 5. Araling Panlipunan (AP) -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Araling Panlipunan (AP)</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['AP']['q1'] ? number_format($core['AP']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['AP']['q2'] ? number_format($core['AP']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['AP']['q3'] ? number_format($core['AP']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['AP']['final'] ? number_format($core['AP']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['AP']['remarks'] }}</td>
                        </tr>

                        <!-- 6. Edukasyon sa Pagpapakatao (EsP) -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Edukasyon sa Pagpapakatao (EsP)</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ESP']['q1'] ? number_format($core['ESP']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ESP']['q2'] ? number_format($core['ESP']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['ESP']['q3'] ? number_format($core['ESP']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['ESP']['final'] ? number_format($core['ESP']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['ESP']['remarks'] }}</td>
                        </tr>

                        <!-- 7. Technology and Livelihood Education (TLE) -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-semibold">Technology and Livelihood Education (TLE)</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['TLE']['q1'] ? number_format($core['TLE']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['TLE']['q2'] ? number_format($core['TLE']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-semibold">{{ $core['TLE']['q3'] ? number_format($core['TLE']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['TLE']['final'] ? number_format($core['TLE']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['TLE']['remarks'] }}</td>
                        </tr>

                        <!-- 8. MAPEH -->
                        <tr class="h-5">
                            <td class="border border-[#002b55] px-2 text-start font-bold">MAPEH</td>
                            <td class="border border-[#002b55] font-bold">{{ $core['MAPEH']['q1'] ? number_format($core['MAPEH']['q1'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold">{{ $core['MAPEH']['q2'] ? number_format($core['MAPEH']['q2'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold">{{ $core['MAPEH']['q3'] ? number_format($core['MAPEH']['q3'], 0) : '' }}</td>
                            <td class="border border-[#002b55] font-bold text-[10px]">{{ $core['MAPEH']['final'] ? number_format($core['MAPEH']['final'], 0) : '' }}</td>
                            <td class="border border-[#002b55] text-[9px] font-bold">{{ $core['MAPEH']['remarks'] }}</td>
                        </tr>

                        <!-- MAPEH Component Rows (Indented) -->
                        <tr class="h-4.5 text-[9px]">
                            <td class="border border-[#002b55] pl-6 text-start italic">Music</td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                        </tr>
                        <tr class="h-4.5 text-[9px]">
                            <td class="border border-[#002b55] pl-6 text-start italic">Arts</td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                        </tr>
                        <tr class="h-4.5 text-[9px]">
                            <td class="border border-[#002b55] pl-6 text-start italic">Physical Education</td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                        </tr>
                        <tr class="h-4.5 text-[9px]">
                            <td class="border border-[#002b55] pl-6 text-start italic">Health</td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                            <td class="border border-[#002b55]"></td>
                        </tr>

                        <!-- Extra Subjects (e.g. Journalism) -->
                        @foreach ($others as $os)
                            <tr class="h-5">
                                <td class="border border-[#002b55] px-2 text-start font-semibold">{{ $os['name'] }}</td>
                                <td class="border border-[#002b55] font-semibold">{{ $os['q1'] ? number_format($os['q1'], 0) : '' }}</td>
                                <td class="border border-[#002b55] font-semibold">{{ $os['q2'] ? number_format($os['q2'], 0) : '' }}</td>
                                <td class="border border-[#002b55] font-semibold">{{ $os['q3'] ? number_format($os['q3'], 0) : '' }}</td>
                                <td class="border border-[#002b55] font-bold text-[10px]">{{ $os['final'] ? number_format($os['final'], 0) : '' }}</td>
                                <td class="border border-[#002b55] text-[9px] font-bold">{{ $os['remarks'] }}</td>
                            </tr>
                        @endforeach

                        @if (count($others) === 0)
                            <tr class="h-5">
                                <td class="border border-[#002b55] px-2 text-start">&nbsp;</td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                            </tr>
                        @endif

                        <!-- General Average -->
                        <tr class="h-6.5 bg-zinc-50/50">
                            <td colspan="4" class="border border-[#002b55] px-2 text-center font-bold text-[10.5px] uppercase tracking-wider">
                                General Average
                            </td>
                            <td class="border border-[#002b55] font-black text-[11px]">
                                {{ $genAvg ? number_format($genAvg, 0) : '' }}
                            </td>
                            <td class="border border-[#002b55] font-bold text-[9.5px] uppercase">
                                {{ $genAvg ? ($genAvg >= 75 ? 'Passed' : 'Failed') : '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Learning Modality Table -->
                <div class="mt-3">
                    <table class="w-full text-center border-collapse text-[9px]" style="border: 1.5px solid #002b55;">
                        <thead>
                            <tr class="h-5.5 bg-zinc-50/50">
                                <th class="border border-[#002b55] px-2 text-start font-bold w-[42%] text-[9.5px]">LEARNING DELIVERY/ MODALITY</th>
                                <th class="border border-[#002b55] font-bold w-[19%]">Term 1</th>
                                <th class="border border-[#002b55] font-bold w-[19%]">Term 2</th>
                                <th class="border border-[#002b55] font-bold w-[20%]">Term 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="h-5">
                                <td class="border border-[#002b55] px-2 text-start font-medium">&nbsp;</td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                                <td class="border border-[#002b55]"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grading Scale & Descriptors -->
            <div class="mt-3 pt-1.5 border-t border-dashed border-[#002b55]/40 text-[9px] leading-snug font-medium">
                <div class="grid grid-cols-3 gap-2 px-1">
                    <div>
                        <span class="font-bold block mb-1 text-[9.5px]">Descriptors</span>
                        <div class="space-y-0.5">
                            <div>Outstanding</div>
                            <div>Very Satisfactory</div>
                            <div>Satisfactory</div>
                            <div>Fairly Satisfactory</div>
                            <div>Did Not Meet Expectations</div>
                        </div>
                    </div>
                    <div class="text-center">
                        <span class="font-bold block mb-1 text-[9.5px]">Grading Scale</span>
                        <div class="space-y-0.5 font-semibold">
                            <div>90-100</div>
                            <div>85-89</div>
                            <div>80-84</div>
                            <div>75-79</div>
                            <div>Below 75</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="font-bold block mb-1 text-[9.5px]">Remarks</span>
                        <div class="space-y-0.5 font-bold">
                            <div>Passed</div>
                            <div>Passed</div>
                            <div>Passed</div>
                            <div>Passed</div>
                            <div>Failed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center Fold Guide (Preview Only, hidden in print) -->
        <div class="fold-guide hidden sm:block absolute inset-y-6 left-1/2 -translate-x-1/2 w-0 border-r border-dashed border-[#002b55]/20 pointer-events-none"></div>

        <!-- Right Half: PAGE 3 (REPORT ON LEARNER'S OBSERVED VALUES) -->
        <div class="report-page-half print-page-half w-full sm:w-1/2 flex flex-col justify-between pl-0 sm:pl-4">
            
            <div>
                <h3 class="text-center font-bold tracking-wider text-[11.5px] uppercase mb-2 font-serif" style="color: #002b55;">
                    REPORT ON LEARNER'S OBSERVED VALUES
                </h3>

                <!-- Core Values Table -->
                <table class="w-full text-start border-collapse text-[9px] leading-tight" style="border: 1.5px solid #002b55;">
                    <thead>
                        <tr class="bg-zinc-50/50">
                            <th rowspan="2" class="border border-[#002b55] py-1.5 px-2 font-bold w-[25%] text-[9.5px] text-center">
                                Core Values
                            </th>
                            <th rowspan="2" class="border border-[#002b55] py-1.5 px-2 font-bold w-[51%] text-[9.5px] text-center">
                                Behavior Statements
                            </th>
                            <th colspan="3" class="border border-[#002b55] py-0.5 font-bold text-center text-[9.5px]">
                                Term
                            </th>
                        </tr>
                        <tr class="bg-zinc-50/50 text-center">
                            <th class="border border-[#002b55] py-0.5 w-[8%] font-bold">1</th>
                            <th class="border border-[#002b55] py-0.5 w-[8%] font-bold">2</th>
                            <th class="border border-[#002b55] py-0.5 w-[8%] font-bold">3</th>
                        </tr>
                    </thead>
                    <tbody class="font-medium">
                        <!-- 1. Maka-Diyos -->
                        <tr>
                            <td rowspan="2" class="border border-[#002b55] p-2 font-bold align-top text-[9.5px]">
                                1. Maka-Diyos
                            </td>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>
                        <tr>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Shows adherence to ethical principles by upholding truth in all undertakings.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>

                        <!-- 2. Makatao -->
                        <tr>
                            <td class="border border-[#002b55] p-2 font-bold align-top text-[9.5px]">
                                2. Makatao
                            </td>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Is sensitive to individual, social, and cultural differences. Demonstrates contributions towards solidarity.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>

                        <!-- 3. Makakalikasan -->
                        <tr>
                            <td class="border border-[#002b55] p-2 font-bold align-top text-[9.5px]">
                                3. Makakalikasan
                            </td>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Cares for environment and utilizes resources wisely, judiciously and economically.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>

                        <!-- 4. Makabansa -->
                        <tr>
                            <td rowspan="2" class="border border-[#002b55] p-2 font-bold align-top text-[9.5px]">
                                4. Makabansa
                            </td>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>
                        <tr>
                            <td class="border border-[#002b55] p-2 align-top leading-snug">
                                Demonstrates appropriate behavior in carrying out activities in school, community and country.
                            </td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                            <td class="border border-[#002b55] text-center font-bold"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Observed Values Marking Legend -->
            <div class="mt-4 pt-2 border-t border-dashed border-[#002b55]/40 text-[9.5px] flex justify-center">
                <div class="w-64 space-y-1">
                    <div class="flex font-bold justify-between border-b border-[#002b55]/40 pb-0.5">
                        <span class="w-14">Marking</span>
                        <span class="flex-1 text-start">Non-Numerical Rating</span>
                    </div>
                    <div class="flex justify-between"><span class="w-14 font-bold">AO</span><span class="flex-1 text-start">Always Observed</span></div>
                    <div class="flex justify-between"><span class="w-14 font-bold">SO</span><span class="flex-1 text-start">Sometimes Observed</span></div>
                    <div class="flex justify-between"><span class="w-14 font-bold">RO</span><span class="flex-1 text-start">Rarely Observed</span></div>
                    <div class="flex justify-between"><span class="w-14 font-bold">NO</span><span class="flex-1 text-start">Not Observed</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ========================================================
       CRITICAL READABILITY RULES (SCREEN & PRINT)
       Guarantee crisp, high-contrast DepEd Navy Blue text
       and borders even when the application is in dark mode.
       ======================================================== */
    #report-card-booklet-sheets,
    #report-card-booklet-sheets *,
    .report-card-sheet,
    .report-card-sheet * {
        color: #002b55 !important;
        border-color: #002b55 !important;
        -webkit-text-fill-color: #002b55 !important;
    }

    .report-card-sheet {
        background-color: #ffffff !important;
        color: #002b55 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }

    .report-card-sheet table,
    .report-card-sheet th,
    .report-card-sheet td {
        border-color: #002b55 !important;
        color: #002b55 !important;
    }

    .report-card-sheet table {
        border-collapse: collapse !important;
        border: 1.5px solid #002b55 !important;
    }

    .report-card-sheet th,
    .report-card-sheet td {
        border: 1px solid #002b55 !important;
    }

    .report-card-sheet .border-b,
    .report-card-sheet [class*="border-b"] {
        border-bottom-color: #002b55 !important;
    }

    .report-card-sheet .border-t,
    .report-card-sheet [class*="border-t"] {
        border-top-color: #002b55 !important;
    }

    /* Vertical Header text for Months in Attendance Table */
    .vertical-header {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        text-orientation: mixed;
        white-space: nowrap;
        font-size: 8px;
        font-weight: 800;
        height: 52px;
        line-height: 1;
        display: inline-block;
        padding-top: 3px;
        letter-spacing: 0.05em;
        color: #002b55 !important;
    }

    /* Print media rules for fallback native printing */
    @media print {
        @page {
            size: landscape;
            margin: 4mm 6mm;
        }
        header, nav, aside, #admin-mobile-drawer, #teacher-mobile-drawer, .no-print, button {
            display: none !important;
        }
        html, body {
            background: #ffffff !important;
            color: #002b55 !important;
            height: auto !important;
            min-height: 100% !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .fixed.inset-0,
        .max-h-\[96vh\],
        #report-card-viewport {
            position: static !important;
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
        }
        #report-card-booklet-sheets {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #002b55 !important;
        }
        #report-card-booklet-sheets .report-card-sheet {
            display: flex !important;
            visibility: visible !important;
            width: 100% !important;
            height: 98vh !important;
            min-height: 98vh !important;
            max-width: 100% !important;
            page-break-after: always !important;
            break-after: page !important;
            box-shadow: none !important;
            border: none !important;
            padding: 4mm 2mm !important;
            box-sizing: border-box !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            background: #ffffff !important;
        }
        #report-card-booklet-sheets .report-card-sheet:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
        }
        .print-page-half {
            width: 50% !important;
            height: 100% !important;
            box-sizing: border-box !important;
            padding: 0 4mm !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .fold-guide,
        .no-print {
            display: none !important;
        }
    }
</style>
