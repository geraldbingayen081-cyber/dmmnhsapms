@props(['reportData'])

@php
    $student = $reportData['student'] ?? null;
    $lastName = strtoupper($student?->last_name ?? '');
    $firstName = strtoupper($student?->first_name ?? '');
    $middleName = strtoupper($student?->middle_name ?? '');
    $suffix = strtoupper($student?->suffix ?? '');
    $studentFullName = $student
        ? strtoupper(trim("{$student->last_name}, {$student->first_name} {$student->middle_name}" . ($student->suffix ? " {$student->suffix}" : '')))
        : 'LEARNER NAME';
    $lrn = $student?->student_number ?? '';
    $birthdate = $student?->birthdate ? \Carbon\Carbon::parse($student->birthdate)->format('m/d/Y') : '';
    $sex = strtoupper($student?->gender ?? '');

    $schoolName = $reportData['school_name'] ?? 'DON MARIANO MARCOS NATIONAL HIGH SCHOOL';
    $schoolId = $reportData['school_id'] ?? '300445';
    $schoolDistrict = $reportData['school_district'] ?? 'Gattaran';
    $schoolDivision = $reportData['school_division'] ?? 'CAGAYAN';
    $schoolRegion = $reportData['school_region'] ?? 'II - CAGAYAN VALLEY';
    $schoolAddress = $reportData['school_address'] ?? 'Dummun, Gattaran, Cagayan';
    $schoolLogo = $reportData['school_logo'] ?? \App\Models\SystemSetting::logoUrl();
    $principalName = $reportData['principal_name'] ?? 'LANY F. GAYUMA';
    $principalTitle = $reportData['principal_title'] ?? 'Principal II';

    $records = $reportData['scholastic_records'] ?? [];
    $page1Records = array_slice($records, 0, 2);
    $page2Records = array_slice($records, 2);

    $lastActiveSchoolYear = $reportData['last_school_year'] ?? ($reportData['school_year'] ?? '2026-2027');
    $eligibleForGrade = $reportData['eligible_for_grade'] ?? '';
@endphp

<!-- Dedicated DepEd SF10-JHS Permanent Academic Record Container -->
<div id="sf10-record-sheets" class="w-full flex flex-col items-center gap-8 text-[#002b55] select-text">

    <!-- ==========================================
         PAGE 1: FRONT SHEET
         Learner's Personal Information, Eligibility,
         and Scholastic Record (Grade 7 & Grade 8)
         ========================================== -->
    <div x-show="activeSpread === 'all' || activeSpread === 'outside'"
         class="sf10-sheet print-sf10-sheet w-full max-w-[1020px] bg-white rounded-lg shadow-xl border border-zinc-200 relative p-6 sm:p-8 flex flex-col justify-between gap-4"
         style="box-sizing: border-box; min-height: 720px;"
         data-sheet="page1">

        <div class="space-y-3">
            <!-- Top Form Code & Official DepEd Header -->
            <div class="flex items-start justify-between gap-4 border-b-2 border-[#002b55] pb-3">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-[10px] font-black tracking-wider uppercase font-mono border border-[#002b55] px-2 py-0.5">
                        SF10-JHS
                    </div>
                    @if ($schoolLogo)
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-14 w-14 object-contain" />
                    @else
                        <img src="/images/deped-logo.svg" alt="Department of Education" class="h-14 w-14 object-contain" />
                    @endif
                </div>

                <div class="text-center flex-1 space-y-0.5">
                    <p class="text-[10.5px] font-bold tracking-wide font-serif">Republic of the Philippines</p>
                    <p class="text-[16px] font-bold tracking-wide" style="font-family: 'UnifrakturMaguntia', 'Old English Text MT', Georgia, serif;">
                        Department of Education
                    </p>
                    <h2 class="font-black text-[12.5px] uppercase tracking-wider font-serif pt-0.5">
                        LEARNER'S PERMANENT ACADEMIC RECORD FOR JUNIOR HIGH SCHOOL (SF10-JHS)
                    </h2>
                    <p class="text-[9px] italic font-serif">(Formerly Form 137)</p>
                </div>

                <div class="shrink-0 flex flex-col items-end justify-center">
                    <div class="h-14 w-14 rounded-full border-[1.5px] border-[#002b55] flex flex-col items-center justify-center text-center p-1">
                        <span class="text-[8px] font-black tracking-tighter uppercase font-serif leading-none">DEPED</span>
                        <span class="text-[7px] font-semibold uppercase font-serif leading-tight mt-0.5">Official Record</span>
                    </div>
                </div>
            </div>

            <!-- SECTION I: LEARNER'S PERSONAL INFORMATION -->
            <div>
                <div class="bg-[#002b55]/10 border-[1.5px] border-[#002b55] py-1 px-2 text-center font-bold text-[10px] uppercase tracking-wider font-serif">
                    LEARNER'S PERSONAL INFORMATION
                </div>
                <div class="border-x-[1.5px] border-b-[1.5px] border-[#002b55] p-2.5 space-y-2 text-[9.5px] font-medium">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0 uppercase text-[9px]">Last Name:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10.5px] uppercase px-1 pb-0.5">
                                {{ $lastName ?: '&nbsp;' }}
                            </div>
                        </div>
                        <div class="sm:col-span-4 flex items-end gap-1">
                            <span class="font-bold shrink-0 uppercase text-[9px]">First Name:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10.5px] uppercase px-1 pb-0.5">
                                {{ $firstName ?: '&nbsp;' }}
                            </div>
                        </div>
                        <div class="sm:col-span-2 flex items-end gap-1">
                            <span class="font-bold shrink-0 uppercase text-[8.5px]">Name Ext. (Jr, I, II):</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10px] uppercase px-1 pb-0.5 text-center">
                                {{ $suffix ?: '&nbsp;' }}
                            </div>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0 uppercase text-[9px]">Middle Name:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10.5px] uppercase px-1 pb-0.5">
                                {{ $middleName ?: '&nbsp;' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-0.5">
                        <div class="sm:col-span-5 flex items-end gap-1">
                            <span class="font-bold shrink-0 text-[9px]">Learner Reference Number (LRN):</span>
                            <div class="border-b border-[#002b55] flex-1 font-mono font-bold text-[10.5px] px-1 pb-0.5 tracking-wider">
                                {{ $lrn ?: '&nbsp;' }}
                            </div>
                        </div>
                        <div class="sm:col-span-4 flex items-end gap-1">
                            <span class="font-bold shrink-0 text-[9px]">Birthdate (mm/dd/yyyy):</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10px] px-1 pb-0.5 text-center">
                                {{ $birthdate ?: '&nbsp;' }}
                            </div>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0 text-[9px]">Sex:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold text-[10px] uppercase px-1 pb-0.5 text-center">
                                {{ $sex ?: '&nbsp;' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION II: ELIGIBILITY FOR JHS ENROLLMENT -->
            <div>
                <div class="bg-[#002b55]/10 border-[1.5px] border-[#002b55] py-1 px-2 text-center font-bold text-[10px] uppercase tracking-wider font-serif">
                    ELIGIBILITY FOR JHS ENROLLMENT
                </div>
                <div class="border-x-[1.5px] border-b-[1.5px] border-[#002b55] p-2.5 space-y-1.5 text-[9px] font-medium">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-4 flex items-center gap-1.5">
                            <span class="inline-flex items-center justify-center w-3 h-3 border border-[#002b55] font-bold text-[8px] leading-none">&#10003;</span>
                            <span class="font-bold">Elementary School Completer</span>
                        </div>
                        <div class="sm:col-span-4 flex items-end gap-1">
                            <span class="font-bold shrink-0">General Average:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center">&nbsp;</div>
                        </div>
                        <div class="sm:col-span-4 flex items-end gap-1">
                            <span class="font-bold shrink-0">Citation (If Any):</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold px-1">&nbsp;</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5 flex items-end gap-1">
                            <span class="font-bold shrink-0">Name of Elementary School:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold px-1">&nbsp;</div>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0">School ID:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center">&nbsp;</div>
                        </div>
                        <div class="sm:col-span-4 flex items-end gap-1">
                            <span class="font-bold shrink-0">Address of School:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold px-1">&nbsp;</div>
                        </div>
                    </div>

                    <div class="pt-1 border-t border-dashed border-[#002b55]/40 grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
                        <div class="sm:col-span-3 font-bold">Other Credential Presented:</div>
                        <div class="sm:col-span-3 flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 border border-[#002b55]"></span>
                            <span>PEPT Passer &bull; Rating: ______</span>
                        </div>
                        <div class="sm:col-span-3 flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 border border-[#002b55]"></span>
                            <span>ALS A&amp;E Passer &bull; Rating: ______</span>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="inline-block w-3 h-3 border border-[#002b55] shrink-0"></span>
                            <span class="shrink-0">Others:</span>
                            <div class="border-b border-[#002b55] flex-1">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION III: SCHOLASTIC RECORD (PAGE 1: GRADE 7 & GRADE 8) -->
            <div>
                <div class="bg-[#002b55]/10 border-[1.5px] border-[#002b55] py-1 px-2 text-center font-bold text-[10px] uppercase tracking-wider font-serif mb-2.5">
                    SCHOLASTIC RECORD
                </div>

                <div class="sf10-scholastic-grid grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($page1Records as $rec)
                        @php
                            $core = $rec['core_subjects'] ?? [];
                            $others = $rec['other_subjects'] ?? [];
                            $genAvg = $rec['general_average'] ?? null;
                            $hasEnrollment = $rec['has_enrollment'] ?? false;
                        @endphp
                        <div class="sf10-scholastic-block border-[1.5px] border-[#002b55] p-2.5 flex flex-col justify-between space-y-2">
                            <div class="space-y-1.5">
                                <!-- School & Classification Header -->
                                <div class="text-[8.5px] space-y-1 font-medium">
                                    <div class="flex items-end gap-1">
                                        <span class="font-bold shrink-0">School:</span>
                                        <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1 truncate">
                                            {{ $hasEnrollment ? ($rec['school_name'] ?? $schoolName) : '&nbsp;' }}
                                        </div>
                                        <span class="font-bold shrink-0 ml-1">School ID:</span>
                                        <div class="border-b border-[#002b55] w-14 font-mono font-bold text-center">
                                            {{ $hasEnrollment ? ($rec['school_id'] ?? $schoolId) : '&nbsp;' }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">District:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold px-1 truncate">
                                                {{ $hasEnrollment ? $schoolDistrict : '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Division:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold uppercase px-1 truncate">
                                                {{ $hasEnrollment ? $schoolDivision : '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Region:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold uppercase px-1 truncate">
                                                {{ $hasEnrollment ? 'II' : '&nbsp;' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Classified as:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center">
                                                {{ $rec['grade_level'] }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Section:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center truncate">
                                                {{ $rec['section_name'] ?: '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">SY:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-mono font-bold px-1 text-center">
                                                {{ $rec['school_year'] ?: '&nbsp;' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-1">
                                        <span class="font-bold shrink-0">Name of Adviser/Teacher:</span>
                                        <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1">
                                            {{ $rec['adviser_name'] ?: '&nbsp;' }}
                                        </div>
                                        <span class="font-bold shrink-0 ml-1">Signature:</span>
                                        <div class="border-b border-[#002b55] w-16">&nbsp;</div>
                                    </div>
                                </div>

                                <!-- Grades Table (Matching SF9 Booklet Style) -->
                                <table class="w-full text-center border-collapse text-[8.5px]" style="border: 1.5px solid #002b55;">
                                    <thead>
                                        <tr class="bg-zinc-50/50">
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1.5 text-start font-bold w-[44%] text-[9px]">
                                                LEARNING AREAS
                                            </th>
                                            <th colspan="3" class="border border-[#002b55] py-0.5 font-bold text-[8.5px]">
                                                Term
                                            </th>
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1 font-bold w-[14%] text-[8.5px]">
                                                FINAL RATING
                                            </th>
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1 font-bold w-[15%] text-[8.5px]">
                                                REMARKS
                                            </th>
                                        </tr>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">1</th>
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">2</th>
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">3</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-medium">
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Filipino</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q1']) ? number_format($core['FIL']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q2']) ? number_format($core['FIL']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q3']) ? number_format($core['FIL']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['FIL']['final']) ? number_format($core['FIL']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['FIL']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">English</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q1']) ? number_format($core['ENG']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q2']) ? number_format($core['ENG']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q3']) ? number_format($core['ENG']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['ENG']['final']) ? number_format($core['ENG']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['ENG']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Mathematics</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q1']) ? number_format($core['MAT']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q2']) ? number_format($core['MAT']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q3']) ? number_format($core['MAT']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['MAT']['final']) ? number_format($core['MAT']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['MAT']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Science</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q1']) ? number_format($core['SCI']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q2']) ? number_format($core['SCI']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q3']) ? number_format($core['SCI']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['SCI']['final']) ? number_format($core['SCI']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['SCI']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Araling Panlipunan (AP)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q1']) ? number_format($core['AP']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q2']) ? number_format($core['AP']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q3']) ? number_format($core['AP']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['AP']['final']) ? number_format($core['AP']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['AP']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Edukasyon sa Pagpapakatao (EsP)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q1']) ? number_format($core['ESP']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q2']) ? number_format($core['ESP']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q3']) ? number_format($core['ESP']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['ESP']['final']) ? number_format($core['ESP']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['ESP']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Technology and Livelihood Education (TLE)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q1']) ? number_format($core['TLE']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q2']) ? number_format($core['TLE']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q3']) ? number_format($core['TLE']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['TLE']['final']) ? number_format($core['TLE']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['TLE']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-bold">MAPEH</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q1']) ? number_format($core['MAPEH']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q2']) ? number_format($core['MAPEH']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q3']) ? number_format($core['MAPEH']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['MAPEH']['final']) ? number_format($core['MAPEH']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['MAPEH']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Music</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Arts</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Physical Education</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Health</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>

                                        @foreach ($others as $os)
                                            <tr class="h-4.5">
                                                <td class="border border-[#002b55] px-1.5 text-start font-semibold">{{ $os['name'] }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q1']) ? number_format($os['q1'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q2']) ? number_format($os['q2'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q3']) ? number_format($os['q3'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($os['final']) ? number_format($os['final'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $os['remarks'] ?? '' }}</td>
                                            </tr>
                                        @endforeach

                                        @if (count($others) === 0)
                                            <tr class="h-4.5">
                                                <td class="border border-[#002b55] px-1.5 text-start">&nbsp;</td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                            </tr>
                                        @endif

                                        <tr class="h-5.5 bg-zinc-50/50">
                                            <td colspan="4" class="border border-[#002b55] px-1.5 text-center font-bold text-[9px] uppercase tracking-wider">
                                                General Average
                                            </td>
                                            <td class="border border-[#002b55] font-black text-[10px]">
                                                {{ $genAvg ? number_format($genAvg, 0) : '' }}
                                            </td>
                                            <td class="border border-[#002b55] font-bold text-[8.5px] uppercase">
                                                {{ $genAvg ? ($genAvg >= 75 ? 'PROMOTED' : 'RETAINED') : '' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Remedial Classes Sub-table -->
                            <div class="pt-1">
                                <table class="w-full text-center border-collapse text-[8px]" style="border: 1.5px solid #002b55;">
                                    <thead>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 px-1 text-start font-bold w-[30%]">Remedial Classes</th>
                                            <th colspan="4" class="border border-[#002b55] py-0.5 px-1 text-start font-semibold">
                                                Conducted from (mm/dd/yyyy): _______ to (mm/dd/yyyy): _______
                                            </th>
                                        </tr>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Learning Areas</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Final Rating</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Remedial Class Mark</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Recomputed Final Grade</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="h-4">
                                            <td class="border border-[#002b55]">&nbsp;</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-[8.5px] italic flex items-center justify-between pt-1 border-t border-dashed border-[#002b55]/40">
            <span>SF10-JHS • Learner's Permanent Academic Record for Junior High School</span>
            <span class="font-bold not-italic">Page 1 of 2</span>
        </div>
    </div>

    <!-- ==========================================
         PAGE 2: BACK SHEET
         Scholastic Record (Grade 9 & Grade 10)
         and Official DepEd Certification Block
         ========================================== -->
    <div x-show="activeSpread === 'all' || activeSpread === 'inside'"
         class="sf10-sheet print-sf10-sheet w-full max-w-[1020px] bg-white rounded-lg shadow-xl border border-zinc-200 relative p-6 sm:p-8 flex flex-col justify-between gap-4"
         style="box-sizing: border-box; min-height: 720px;"
         data-sheet="page2">

        <div class="space-y-3">
            <!-- Top Learner Reference Strip for Page 2 -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b-2 border-[#002b55] pb-2 text-[9.5px]">
                <div class="flex items-center gap-2">
                    <span class="font-black uppercase font-mono border border-[#002b55] px-1.5 py-0.5 text-[8.5px]">SF10-JHS (Page 2)</span>
                    <span class="font-bold">LEARNER:</span>
                    <span class="font-black uppercase tracking-wide underline">{{ $studentFullName }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold">LRN:</span>
                    <span class="font-mono font-black tracking-wider underline">{{ $lrn }}</span>
                </div>
            </div>

            <!-- SECTION III (CONTINUED): SCHOLASTIC RECORD (GRADE 9 & GRADE 10) -->
            <div>
                <div class="bg-[#002b55]/10 border-[1.5px] border-[#002b55] py-1 px-2 text-center font-bold text-[10px] uppercase tracking-wider font-serif mb-2.5">
                    SCHOLASTIC RECORD (CONTINUED)
                </div>

                <div class="sf10-scholastic-grid grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($page2Records as $rec)
                        @php
                            $core = $rec['core_subjects'] ?? [];
                            $others = $rec['other_subjects'] ?? [];
                            $genAvg = $rec['general_average'] ?? null;
                            $hasEnrollment = $rec['has_enrollment'] ?? false;
                        @endphp
                        <div class="sf10-scholastic-block border-[1.5px] border-[#002b55] p-2.5 flex flex-col justify-between space-y-2">
                            <div class="space-y-1.5">
                                <!-- School & Classification Header -->
                                <div class="text-[8.5px] space-y-1 font-medium">
                                    <div class="flex items-end gap-1">
                                        <span class="font-bold shrink-0">School:</span>
                                        <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1 truncate">
                                            {{ $hasEnrollment ? ($rec['school_name'] ?? $schoolName) : '&nbsp;' }}
                                        </div>
                                        <span class="font-bold shrink-0 ml-1">School ID:</span>
                                        <div class="border-b border-[#002b55] w-14 font-mono font-bold text-center">
                                            {{ $hasEnrollment ? ($rec['school_id'] ?? $schoolId) : '&nbsp;' }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">District:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold px-1 truncate">
                                                {{ $hasEnrollment ? $schoolDistrict : '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Division:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold uppercase px-1 truncate">
                                                {{ $hasEnrollment ? $schoolDivision : '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Region:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-semibold uppercase px-1 truncate">
                                                {{ $hasEnrollment ? 'II' : '&nbsp;' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Classified as:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center">
                                                {{ $rec['grade_level'] }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">Section:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-bold px-1 text-center truncate">
                                                {{ $rec['section_name'] ?: '&nbsp;' }}
                                            </div>
                                        </div>
                                        <div class="flex items-end gap-1">
                                            <span class="font-bold shrink-0">SY:</span>
                                            <div class="border-b border-[#002b55] flex-1 font-mono font-bold px-1 text-center">
                                                {{ $rec['school_year'] ?: '&nbsp;' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-1">
                                        <span class="font-bold shrink-0">Name of Adviser/Teacher:</span>
                                        <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1">
                                            {{ $rec['adviser_name'] ?: '&nbsp;' }}
                                        </div>
                                        <span class="font-bold shrink-0 ml-1">Signature:</span>
                                        <div class="border-b border-[#002b55] w-16">&nbsp;</div>
                                    </div>
                                </div>

                                <!-- Grades Table -->
                                <table class="w-full text-center border-collapse text-[8.5px]" style="border: 1.5px solid #002b55;">
                                    <thead>
                                        <tr class="bg-zinc-50/50">
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1.5 text-start font-bold w-[44%] text-[9px]">
                                                LEARNING AREAS
                                            </th>
                                            <th colspan="3" class="border border-[#002b55] py-0.5 font-bold text-[8.5px]">
                                                Term
                                            </th>
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1 font-bold w-[14%] text-[8.5px]">
                                                FINAL RATING
                                            </th>
                                            <th rowspan="2" class="border border-[#002b55] py-1 px-1 font-bold w-[15%] text-[8.5px]">
                                                REMARKS
                                            </th>
                                        </tr>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">1</th>
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">2</th>
                                            <th class="border border-[#002b55] py-0.5 w-[9%] font-bold">3</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-medium">
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Filipino</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q1']) ? number_format($core['FIL']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q2']) ? number_format($core['FIL']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['FIL']['q3']) ? number_format($core['FIL']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['FIL']['final']) ? number_format($core['FIL']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['FIL']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">English</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q1']) ? number_format($core['ENG']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q2']) ? number_format($core['ENG']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ENG']['q3']) ? number_format($core['ENG']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['ENG']['final']) ? number_format($core['ENG']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['ENG']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Mathematics</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q1']) ? number_format($core['MAT']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q2']) ? number_format($core['MAT']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['MAT']['q3']) ? number_format($core['MAT']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['MAT']['final']) ? number_format($core['MAT']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['MAT']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Science</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q1']) ? number_format($core['SCI']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q2']) ? number_format($core['SCI']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['SCI']['q3']) ? number_format($core['SCI']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['SCI']['final']) ? number_format($core['SCI']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['SCI']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Araling Panlipunan (AP)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q1']) ? number_format($core['AP']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q2']) ? number_format($core['AP']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['AP']['q3']) ? number_format($core['AP']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['AP']['final']) ? number_format($core['AP']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['AP']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Edukasyon sa Pagpapakatao (EsP)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q1']) ? number_format($core['ESP']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q2']) ? number_format($core['ESP']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['ESP']['q3']) ? number_format($core['ESP']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['ESP']['final']) ? number_format($core['ESP']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['ESP']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-semibold">Technology and Livelihood Education (TLE)</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q1']) ? number_format($core['TLE']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q2']) ? number_format($core['TLE']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-semibold">{{ !empty($core['TLE']['q3']) ? number_format($core['TLE']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['TLE']['final']) ? number_format($core['TLE']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['TLE']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4.5">
                                            <td class="border border-[#002b55] px-1.5 text-start font-bold">MAPEH</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q1']) ? number_format($core['MAPEH']['q1'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q2']) ? number_format($core['MAPEH']['q2'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold">{{ !empty($core['MAPEH']['q3']) ? number_format($core['MAPEH']['q3'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($core['MAPEH']['final']) ? number_format($core['MAPEH']['final'], 0) : '' }}</td>
                                            <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $core['MAPEH']['remarks'] ?? '' }}</td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Music</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Arts</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Physical Education</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                        <tr class="h-4 text-[8px]">
                                            <td class="border border-[#002b55] pl-5 text-start italic">Health</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>

                                        @foreach ($others as $os)
                                            <tr class="h-4.5">
                                                <td class="border border-[#002b55] px-1.5 text-start font-semibold">{{ $os['name'] }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q1']) ? number_format($os['q1'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q2']) ? number_format($os['q2'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-semibold">{{ !empty($os['q3']) ? number_format($os['q3'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] font-bold text-[9px]">{{ !empty($os['final']) ? number_format($os['final'], 0) : '' }}</td>
                                                <td class="border border-[#002b55] text-[8px] font-bold uppercase">{{ $os['remarks'] ?? '' }}</td>
                                            </tr>
                                        @endforeach

                                        @if (count($others) === 0)
                                            <tr class="h-4.5">
                                                <td class="border border-[#002b55] px-1.5 text-start">&nbsp;</td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                                <td class="border border-[#002b55]"></td>
                                            </tr>
                                        @endif

                                        <tr class="h-5.5 bg-zinc-50/50">
                                            <td colspan="4" class="border border-[#002b55] px-1.5 text-center font-bold text-[9px] uppercase tracking-wider">
                                                General Average
                                            </td>
                                            <td class="border border-[#002b55] font-black text-[10px]">
                                                {{ $genAvg ? number_format($genAvg, 0) : '' }}
                                            </td>
                                            <td class="border border-[#002b55] font-bold text-[8.5px] uppercase">
                                                {{ $genAvg ? ($genAvg >= 75 ? 'PROMOTED' : 'RETAINED') : '' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Remedial Classes Sub-table -->
                            <div class="pt-1">
                                <table class="w-full text-center border-collapse text-[8px]" style="border: 1.5px solid #002b55;">
                                    <thead>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 px-1 text-start font-bold w-[30%]">Remedial Classes</th>
                                            <th colspan="4" class="border border-[#002b55] py-0.5 px-1 text-start font-semibold">
                                                Conducted from (mm/dd/yyyy): _______ to (mm/dd/yyyy): _______
                                            </th>
                                        </tr>
                                        <tr class="bg-zinc-50/50">
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Learning Areas</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Final Rating</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Remedial Class Mark</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Recomputed Final Grade</th>
                                            <th class="border border-[#002b55] py-0.5 px-1 font-bold">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="h-4">
                                            <td class="border border-[#002b55]">&nbsp;</td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                            <td class="border border-[#002b55]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SECTION IV: CERTIFICATION -->
            <div>
                <div class="bg-[#002b55]/10 border-[1.5px] border-[#002b55] py-1 px-2 text-center font-bold text-[10px] uppercase tracking-wider font-serif">
                    CERTIFICATION
                </div>
                <div class="border-x-[1.5px] border-b-[1.5px] border-[#002b55] p-3 space-y-2.5 text-[9.5px] font-medium">
                    <p class="leading-relaxed text-justify font-serif">
                        I CERTIFY that this is a true record of
                        <span class="inline-block border-b border-[#002b55] font-sans font-bold uppercase px-2 min-w-[200px] text-center">{{ $studentFullName }}</span>
                        with LRN
                        <span class="inline-block border-b border-[#002b55] font-mono font-bold px-2 min-w-[110px] text-center">{{ $lrn }}</span>
                        and that he/she is eligible for admission to Grade
                        <span class="inline-block border-b border-[#002b55] font-sans font-bold px-2 min-w-[80px] text-center">{{ $eligibleForGrade ?: '&nbsp;' }}</span>.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-1">
                        <div class="sm:col-span-6 flex items-end gap-1">
                            <span class="font-bold shrink-0">Name of School:</span>
                            <div class="border-b border-[#002b55] flex-1 font-bold uppercase px-1">{{ $schoolName }}</div>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0">School ID:</span>
                            <div class="border-b border-[#002b55] flex-1 font-mono font-bold text-center px-1">{{ $schoolId }}</div>
                        </div>
                        <div class="sm:col-span-3 flex items-end gap-1">
                            <span class="font-bold shrink-0">Last SY Attended:</span>
                            <div class="border-b border-[#002b55] flex-1 font-mono font-bold text-center px-1">{{ $lastActiveSchoolYear }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end pt-4">
                        <div class="sm:col-span-3 text-center">
                            <div class="border-b border-[#002b55] pb-0.5 font-bold text-[10px]">
                                {{ now()->format('F d, Y') }}
                            </div>
                            <div class="italic text-[8.5px] font-medium mt-0.5">Date</div>
                        </div>
                        <div class="sm:col-span-6 text-center">
                            <div class="border-b border-[#002b55] pb-0.5 font-bold uppercase tracking-wider text-[10.5px]">
                                {{ $principalName }}
                            </div>
                            <div class="italic text-[8.5px] font-medium mt-0.5">
                                {{ $principalTitle }} &bull; Signature of Principal/School Head over Printed Name
                            </div>
                        </div>
                        <div class="sm:col-span-3 flex justify-center">
                            <div class="w-full max-w-[140px] h-12 border border-dashed border-[#002b55]/60 flex items-center justify-center text-[8px] italic text-center px-2">
                                (Affix School Seal here)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-[8.5px] italic flex items-center justify-between pt-1 border-t border-dashed border-[#002b55]/40">
            <span>May be reproduced for official evaluation • Any erasure or alteration invalidates this document unless initialed by the School Head.</span>
            <span class="font-bold not-italic">Page 2 of 2</span>
        </div>
    </div>
</div>

<style>
    /* ========================================================
       CRITICAL READABILITY RULES (SCREEN & PRINT)
       Guarantee crisp, high-contrast DepEd Navy Blue text
       and borders even when the application is in dark mode.
       ======================================================== */
    #sf10-record-sheets,
    #sf10-record-sheets *,
    .sf10-sheet,
    .sf10-sheet * {
        color: #002b55 !important;
        border-color: #002b55 !important;
        -webkit-text-fill-color: #002b55 !important;
    }

    .sf10-sheet {
        background-color: #ffffff !important;
        color: #002b55 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }

    .sf10-sheet table,
    .sf10-sheet th,
    .sf10-sheet td {
        border-color: #002b55 !important;
        color: #002b55 !important;
    }

    .sf10-sheet table {
        border-collapse: collapse !important;
        border: 1.5px solid #002b55 !important;
    }

    .sf10-sheet th,
    .sf10-sheet td {
        border: 1px solid #002b55 !important;
    }
</style>

