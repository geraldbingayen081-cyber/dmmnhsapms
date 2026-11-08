<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Livewire\Component;

class ReportManagement extends Component
{
    public $reportType = 'form138'; // form138, form137, summary_sheet

    public $selectedSchoolYearId = 'all';
    public $selectedQuarterId = 'all';
    public $selectedYearLevelId = 'all';
    public $selectedSectionId = 'all';
    public $selectedStudentId = '';

    public $showPreviewModal = false;
    public $reportData = null;

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedSchoolYearId = $activeSy->id;
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->selectedQuarterId = $currentQuarter->id;
            }
        }
    }

    public function updatedSelectedYearLevelId()
    {
        $this->selectedSectionId = 'all';
    }

    public function updatedSelectedSchoolYearId()
    {
        $this->selectedSectionId = 'all';
    }

    public function generateReport()
    {
        $this->validate([
            'reportType' => 'required|in:form138,form137,summary_sheet',
        ]);

        if ($this->reportType === 'form138' || $this->reportType === 'form137') {
            $this->validate(['selectedStudentId' => 'required|exists:students,id']);
        }

        if ($this->reportType === 'summary_sheet') {
            $this->validate(['selectedSectionId' => 'required']);
        }

        $data = [];

        if ($this->reportType === 'form138') {
            $student = Student::with([
                'user',
                'enrollments.schoolYearSection.schoolYear',
                'enrollments.schoolYearSection.yearLevel',
                'enrollments.schoolYearSection.section',
                'enrollments.schoolYearSection.adviser'
            ])->findOrFail($this->selectedStudentId);
            
            $enrollment = null;
            if ($this->selectedSchoolYearId !== 'all') {
                $enrollment = $student->enrollments->first(function ($enr) {
                    return $enr->schoolYearSection?->school_year_id == $this->selectedSchoolYearId;
                });
            }
            if (!$enrollment) {
                $enrollment = $student->enrollments->filter(fn($e) => $e->grades()->exists())->last() 
                           ?? $student->enrollments->last();
            }

            $sy = $enrollment?->schoolYearSection?->schoolYear;
            $quarters = Quarter::where('school_year_id', $sy?->id)->orderBy('sort_order')->get();

            $grades = Grade::with(['sectionSubject.subject', 'quarter'])
                ->where('student_enrollment_id', $enrollment?->id)
                ->get();

            // DepEd Junior High School standard subjects
            $standardCodes = ['FIL', 'ENG', 'MAT', 'SCI', 'AP', 'ESP', 'TLE', 'MAPEH'];
            $allSubjects = Subject::all();

            $builtSubjects = [];
            foreach ($allSubjects as $sub) {
                $qGrades = [1 => null, 2 => null, 3 => null, 4 => null];
                $marks = [];
                foreach ($quarters as $q) {
                    $g = $grades->where('sectionSubject.subject_id', $sub->id)->where('quarter_id', $q->id)->first();
                    $val = $g ? (float) $g->grade : null;
                    $qGrades[$q->sort_order] = $val;
                    if ($val !== null) {
                        $marks[] = $val;
                    }
                }
                $final = count($marks) > 0 ? (array_sum($marks) / count($marks)) : null;

                $builtSubjects[$sub->subject_code] = [
                    'id' => $sub->id,
                    'code' => $sub->subject_code,
                    'name' => $sub->subject_name,
                    'q1' => $qGrades[1] ?? null,
                    'q2' => $qGrades[2] ?? null,
                    'q3' => $qGrades[3] ?? null,
                    'q4' => $qGrades[4] ?? null,
                    'final' => $final,
                    'remarks' => $final !== null ? ($final >= 75 ? 'Passed' : 'Failed') : '',
                ];
            }

            $coreSubjects = [
                'FIL' => $builtSubjects['FIL'] ?? ['code' => 'FIL', 'name' => 'Filipino', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'ENG' => $builtSubjects['ENG'] ?? ['code' => 'ENG', 'name' => 'English', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'MAT' => $builtSubjects['MAT'] ?? ['code' => 'MAT', 'name' => 'Mathematics', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'SCI' => $builtSubjects['SCI'] ?? ['code' => 'SCI', 'name' => 'Science', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'AP' => $builtSubjects['AP'] ?? ['code' => 'AP', 'name' => 'Araling Panlipunan (AP)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'ESP' => $builtSubjects['ESP'] ?? ['code' => 'ESP', 'name' => 'Edukasyon sa Pagpapakatao (EsP)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'TLE' => $builtSubjects['TLE'] ?? ['code' => 'TLE', 'name' => 'Technology and Livelihood Education (TLE)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                'MAPEH' => $builtSubjects['MAPEH'] ?? ['code' => 'MAPEH', 'name' => 'MAPEH', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
            ];

            $otherSubjects = [];
            foreach ($builtSubjects as $code => $subjData) {
                if (!in_array($code, $standardCodes)) {
                    $otherSubjects[] = $subjData;
                }
            }

            // General Average
            $finals = [];
            foreach ($coreSubjects as $cs) {
                if ($cs['final'] !== null) {
                    $finals[] = $cs['final'];
                }
            }
            foreach ($otherSubjects as $os) {
                if ($os['final'] !== null) {
                    $finals[] = $os['final'];
                }
            }
            $genAvg = count($finals) > 0 ? (array_sum($finals) / count($finals)) : null;

            $data = [
                'type' => 'Form 138 (Report Card)',
                'student' => $student,
                'enrollment' => $enrollment,
                'quarters' => $quarters,
                'core_subjects' => $coreSubjects,
                'other_subjects' => $otherSubjects,
                'general_average' => $genAvg,
                'age' => $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->age : '',
                'sex' => $student->gender ?? '',
                'grade_level' => $enrollment?->schoolYearSection?->yearLevel?->name ?? 'Grade 7',
                'section_name' => $enrollment?->schoolYearSection?->section?->name ?? '',
                'school_year' => $sy?->school_year ?? '2026-2027',
                'adviser_name' => $enrollment?->schoolYearSection?->adviser?->full_name ?? '',
                'principal_name' => \App\Models\SystemSetting::get('principal_name', 'LANY F. GAYUMA'),
                'principal_title' => \App\Models\SystemSetting::get('principal_title', 'Principal II'),
                'school_name' => \App\Models\SystemSetting::get('school_name', 'DON MARIANO MARCOS NATIONAL HIGH SCHOOL'),
                'school_address' => \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan'),
                'school_division' => \App\Models\SystemSetting::get('school_division', 'DIVISION OF CAGAYAN'),
                'school_region' => \App\Models\SystemSetting::get('school_region', 'REGION II-CAGAYAN VALLEY'),
                'school_logo' => \App\Models\SystemSetting::logoUrl(),
            ];
        } elseif ($this->reportType === 'form137') {
            $student = Student::with([
                'user',
                'enrollments.schoolYearSection.schoolYear',
                'enrollments.schoolYearSection.yearLevel',
                'enrollments.schoolYearSection.section',
                'enrollments.schoolYearSection.adviser',
                'enrollments.grades.sectionSubject.subject',
                'enrollments.grades.quarter',
            ])->findOrFail($this->selectedStudentId);

            $standardCodes = ['FIL', 'ENG', 'MAT', 'SCI', 'AP', 'ESP', 'TLE', 'MAPEH'];
            $allSubjects = Subject::all();
            $schoolName = \App\Models\SystemSetting::get('school_name', 'DON MARIANO MARCOS NATIONAL HIGH SCHOOL');
            $schoolId = \App\Models\SystemSetting::get('school_id', '300445');

            $buildEnrollmentRecord = function (?StudentEnrollment $enr, string $defaultGradeLabel) use ($allSubjects, $standardCodes, $schoolName, $schoolId) {
                $sy = $enr?->schoolYearSection?->schoolYear;
                $quarters = $sy
                    ? Quarter::where('school_year_id', $sy->id)->orderBy('sort_order')->get()
                    : collect();
                $grades = $enr ? $enr->grades : collect();

                $builtSubjects = [];
                foreach ($allSubjects as $sub) {
                    $qGrades = [1 => null, 2 => null, 3 => null, 4 => null];
                    $marks = [];
                    if ($enr) {
                        foreach ($quarters as $q) {
                            $g = $grades->where('sectionSubject.subject_id', $sub->id)->where('quarter_id', $q->id)->first();
                            $val = $g ? (float) $g->grade : null;
                            $qGrades[$q->sort_order] = $val;
                            if ($val !== null) {
                                $marks[] = $val;
                            }
                        }
                    }
                    $final = count($marks) > 0 ? (array_sum($marks) / count($marks)) : null;

                    $builtSubjects[$sub->subject_code] = [
                        'id' => $sub->id,
                        'code' => $sub->subject_code,
                        'name' => $sub->subject_name,
                        'q1' => $qGrades[1] ?? null,
                        'q2' => $qGrades[2] ?? null,
                        'q3' => $qGrades[3] ?? null,
                        'q4' => $qGrades[4] ?? null,
                        'final' => $final,
                        'remarks' => $final !== null ? ($final >= 75 ? 'Passed' : 'Failed') : '',
                    ];
                }

                $coreSubjects = [
                    'FIL' => $builtSubjects['FIL'] ?? ['code' => 'FIL', 'name' => 'Filipino', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'ENG' => $builtSubjects['ENG'] ?? ['code' => 'ENG', 'name' => 'English', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'MAT' => $builtSubjects['MAT'] ?? ['code' => 'MAT', 'name' => 'Mathematics', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'SCI' => $builtSubjects['SCI'] ?? ['code' => 'SCI', 'name' => 'Science', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'AP' => $builtSubjects['AP'] ?? ['code' => 'AP', 'name' => 'Araling Panlipunan (AP)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'ESP' => $builtSubjects['ESP'] ?? ['code' => 'ESP', 'name' => 'Edukasyon sa Pagpapakatao (EsP)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'TLE' => $builtSubjects['TLE'] ?? ['code' => 'TLE', 'name' => 'Technology and Livelihood Education (TLE)', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                    'MAPEH' => $builtSubjects['MAPEH'] ?? ['code' => 'MAPEH', 'name' => 'MAPEH', 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null, 'remarks' => ''],
                ];

                $otherSubjects = [];
                foreach ($builtSubjects as $code => $subjData) {
                    if (!in_array($code, $standardCodes)) {
                        $otherSubjects[] = $subjData;
                    }
                }

                $finals = [];
                foreach ($coreSubjects as $cs) {
                    if ($cs['final'] !== null) {
                        $finals[] = $cs['final'];
                    }
                }
                foreach ($otherSubjects as $os) {
                    if ($os['final'] !== null) {
                        $finals[] = $os['final'];
                    }
                }
                $genAvg = count($finals) > 0 ? (array_sum($finals) / count($finals)) : null;

                return [
                    'has_enrollment' => (bool) $enr,
                    'school_name' => $schoolName,
                    'school_id' => $schoolId,
                    'school_year' => $sy?->school_year ?? '',
                    'grade_level' => $enr?->schoolYearSection?->yearLevel?->name ?? $defaultGradeLabel,
                    'section_name' => $enr?->schoolYearSection?->section?->name ?? '',
                    'adviser_name' => $enr?->schoolYearSection?->adviser?->full_name ?? '',
                    'status' => $enr?->status ?? '',
                    'core_subjects' => $coreSubjects,
                    'other_subjects' => $otherSubjects,
                    'general_average' => $genAvg,
                ];
            };

            $targetLevels = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
            $scholasticRecords = [];
            $usedEnrollmentIds = [];

            foreach ($targetLevels as $levelName) {
                $matchingEnrollment = $student->enrollments->first(function ($enr) use ($levelName, $usedEnrollmentIds) {
                    return !in_array($enr->id, $usedEnrollmentIds)
                        && strcasecmp($enr->schoolYearSection?->yearLevel?->name ?? '', $levelName) === 0;
                });

                if ($matchingEnrollment) {
                    $usedEnrollmentIds[] = $matchingEnrollment->id;
                }

                $scholasticRecords[] = $buildEnrollmentRecord($matchingEnrollment, $levelName);
            }

            // Include any additional enrollments not matched above
            foreach ($student->enrollments as $enr) {
                if (!in_array($enr->id, $usedEnrollmentIds)) {
                    $scholasticRecords[] = $buildEnrollmentRecord(
                        $enr,
                        $enr->schoolYearSection?->yearLevel?->name ?? 'JHS'
                    );
                }
            }

            $history = [];
            foreach ($student->enrollments as $enr) {
                $grades = $enr->grades;
                $history[] = [
                    'school_year' => $enr->schoolYearSection?->schoolYear?->school_year,
                    'year_level' => $enr->schoolYearSection?->yearLevel?->name,
                    'section' => $enr->schoolYearSection?->section?->name,
                    'status' => $enr->status,
                    'general_average' => $grades->count() > 0 ? $grades->avg('grade') : null,
                    'grades_count' => $grades->count(),
                ];
            }

            $lastEnrollment = $student->enrollments->last();
            $lastSy = $lastEnrollment?->schoolYearSection?->schoolYear?->school_year ?? '2026-2027';

            $data = [
                'type' => 'Form 137 (Permanent Academic Record)',
                'student' => $student,
                'history' => $history,
                'scholastic_records' => $scholasticRecords,
                'last_school_year' => $lastSy,
                'principal_name' => \App\Models\SystemSetting::get('principal_name', 'LANY F. GAYUMA'),
                'principal_title' => \App\Models\SystemSetting::get('principal_title', 'Principal II'),
                'school_name' => $schoolName,
                'school_id' => $schoolId,
                'school_address' => \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan'),
                'school_division' => \App\Models\SystemSetting::get('school_division', 'CAGAYAN'),
                'school_region' => \App\Models\SystemSetting::get('school_region', 'REGION II-CAGAYAN VALLEY'),
                'school_logo' => \App\Models\SystemSetting::logoUrl(),
            ];
        } else {
            $sys = SchoolYearSection::with(['schoolYear', 'yearLevel', 'section', 'adviser', 'enrollments.student', 'enrollments.grades.sectionSubject.subject'])->where('section_id', $this->selectedSectionId)->first();

            $studentsList = [];
            if ($sys) {
                foreach ($sys->enrollments as $enr) {
                    $stGrades = $enr->grades;
                    $studentsList[] = [
                        'student_number' => $enr->student?->student_number,
                        'full_name' => $enr->student?->full_name,
                        'average' => $stGrades->count() > 0 ? $stGrades->avg('grade') : null,
                        'lowest' => $stGrades->count() > 0 ? $stGrades->min('grade') : null,
                        'remarks' => $stGrades->count() > 0 ? ($stGrades->avg('grade') >= 75 ? 'PASSED' : 'FAILED') : 'NO GRADES',
                    ];
                }
            }

            $data = [
                'type' => 'Summary of Term Grades',
                'section_instance' => $sys,
                'students' => $studentsList,
            ];
        }

        $this->reportData = $data;
        $this->showPreviewModal = true;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Generated Official Report',
            'subject_type' => Student::class,
            'subject_id' => $this->selectedStudentId ?: 0,
            'description' => "Generated official academic report: {$data['type']}.",
        ]);
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = Quarter::orderBy('sort_order')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = \App\Models\SchoolYearSection::query()
            ->when($this->selectedSchoolYearId !== 'all', fn($q) => $q->where('school_year_id', $this->selectedSchoolYearId))
            ->when($this->selectedYearLevelId !== 'all', fn($q) => $q->where('year_level_id', $this->selectedYearLevelId))
            ->with('section')
            ->get()
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
        $students = Student::orderBy('last_name')->get();

        return view('livewire.admin.report-management', [
            'schoolYears' => $schoolYears,
            'quarters' => $quarters,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'students' => $students,
        ])->layout('layouts.admin', ['title' => 'Official Academic Reports & Form Generation', 'subtitle' => 'Generate and preview Form 138 (SF9), Form 137 (SF10), and Section Grade Summary Sheets']);
    }
}
