<?php

namespace App\Livewire\Teacher;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Services\AcademicContextService;
use Livewire\Component;

class TeacherReportManagement extends Component
{
    public $reportType = 'form138'; // form138, summary_sheet
    public $selectedStudentId = '';

    public $showPreviewModal = false;
    public $reportData = null;

    public function mount()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        if ($teacher && $activeSy) {
            $advisedSection = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->first();

            if ($advisedSection) {
                $firstAdvisee = StudentEnrollment::where('school_year_section_id', $advisedSection->id)->first();
                $this->selectedStudentId = $firstAdvisee?->student_id ?? '';
            }
        }
    }

    public function generateReport()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $advisedSection = SchoolYearSection::where('school_year_id', $activeSy?->id)
            ->where('adviser_id', $teacher?->id)
            ->with(['section', 'yearLevel', 'schoolYear', 'adviser'])
            ->first();

        if (!$advisedSection) {
            session()->flash('error', 'You must be an assigned Class Adviser to generate official section reports.');
            return;
        }

        $data = [];

        if ($this->reportType === 'form138') {
            $this->validate(['selectedStudentId' => 'required|exists:students,id']);

            $student = Student::with(['user', 'enrollments.schoolYearSection.schoolYear', 'enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section', 'enrollments.schoolYearSection.adviser'])->findOrFail($this->selectedStudentId);
            $enrollment = $student->enrollments->where('school_year_section_id', $advisedSection->id)->first() ?? $student->enrollments->last();

            $grades = Grade::with(['sectionSubject.subject', 'quarter'])
                ->where('student_enrollment_id', $enrollment?->id)
                ->get();

            $sy = $advisedSection->schoolYear;
            $quarters = Quarter::where('school_year_id', $sy?->id)->orderBy('sort_order')->get();

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
                'type' => 'Form 138 (SF9 Report Card)',
                'student' => $student,
                'enrollment' => $enrollment,
                'section_instance' => $advisedSection,
                'core_subjects' => $coreSubjects,
                'other_subjects' => $otherSubjects,
                'quarters' => $quarters,
                'terms' => $quarters,
                'general_average' => $genAvg,
                'age' => $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->age : '',
                'sex' => $student->gender ?? '',
                'grade_level' => $advisedSection->yearLevel?->name ?? 'Grade 7',
                'section_name' => $advisedSection->section?->name ?? '',
                'school_year' => $sy?->school_year ?? '2026-2027',
                'adviser_name' => $advisedSection->adviser?->full_name ?? '',
                'principal_name' => \App\Models\SystemSetting::get('principal_name', 'LANY F. GAYUMA'),
                'principal_title' => \App\Models\SystemSetting::get('principal_title', 'Principal II'),
                'school_name' => \App\Models\SystemSetting::get('school_name', 'DON MARIANO MARCOS NATIONAL HIGH SCHOOL'),
                'school_address' => \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan'),
                'school_division' => \App\Models\SystemSetting::get('school_division', 'DIVISION OF CAGAYAN'),
                'school_region' => \App\Models\SystemSetting::get('school_region', 'REGION II-CAGAYAN VALLEY'),
                'school_logo' => \App\Models\SystemSetting::logoUrl(),
            ];
        } elseif ($this->reportType === 'form137') {
            $this->validate(['selectedStudentId' => 'required|exists:students,id']);

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

            foreach ($student->enrollments as $enr) {
                if (!in_array($enr->id, $usedEnrollmentIds)) {
                    $scholasticRecords[] = $buildEnrollmentRecord(
                        $enr,
                        $enr->schoolYearSection?->yearLevel?->name ?? 'JHS'
                    );
                }
            }

            $lastEnrollment = $student->enrollments->last();
            $lastSy = $lastEnrollment?->schoolYearSection?->schoolYear?->school_year ?? '2026-2027';

            $data = [
                'type' => 'Form 137 (Permanent Academic Record)',
                'student' => $student,
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
            // Summary Sheet of Section
            $enrollments = StudentEnrollment::where('school_year_section_id', $advisedSection->id)
                ->with(['student', 'grades.sectionSubject.subject'])
                ->get();

            $studentsList = [];
            foreach ($enrollments as $enr) {
                $stGrades = $enr->grades;
                $studentsList[] = [
                    'student_number' => $enr->student?->student_number,
                    'full_name' => $enr->student?->full_name,
                    'gender' => $enr->student?->gender,
                    'average' => $stGrades->count() > 0 ? $stGrades->avg('grade') : null,
                    'lowest' => $stGrades->count() > 0 ? $stGrades->min('grade') : null,
                    'remarks' => $stGrades->count() > 0 ? ($stGrades->avg('grade') >= 75 ? 'PASSED' : 'FAILED') : 'NO GRADES',
                ];
            }

            $data = [
                'type' => 'Section Grade Summary Sheet',
                'section_instance' => $advisedSection,
                'students' => $studentsList,
            ];
        }

        $this->reportData = $data;
        $this->showPreviewModal = true;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Generated Advisee Report',
            'subject_type' => Student::class,
            'subject_id' => $this->selectedStudentId ?: 0,
            'description' => "Adviser generated report {$data['type']} for {$advisedSection->yearLevel?->name} - {$advisedSection->section?->name}.",
        ]);
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $advisedSection = null;
        $advisees = collect();

        if ($teacher && $activeSy) {
            $advisedSection = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->with(['section', 'yearLevel'])
                ->first();

            if ($advisedSection) {
                $advisees = StudentEnrollment::where('school_year_section_id', $advisedSection->id)
                    ->with('student')
                    ->get()
                    ->pluck('student')
                    ->filter()
                    ->sortBy('last_name')
                    ->values();
            }
        }

        return view('livewire.teacher.teacher-report-management', [
            'advisedSection' => $advisedSection,
            'advisees' => $advisees,
            'activeSy' => $activeSy,
        ])->layout('layouts.teacher', [
            'title' => 'DepEd Reports (SF9 / SF10)',
            'subtitle' => 'Generate and print official learner progress report cards for your advisees'
        ]);
    }
}
