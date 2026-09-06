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

            $subjects = Subject::all();
            $quarters = Quarter::where('school_year_id', $activeSy?->id)->orderBy('sort_order')->get();

            $subjectGrades = [];
            foreach ($subjects as $sub) {
                $qGrades = [];
                foreach ($quarters as $q) {
                    $g = $grades->where('sectionSubject.subject_id', $sub->id)->where('quarter_id', $q->id)->first();
                    $qGrades[$q->short_name] = $g ? $g->grade : null;
                }
                $validGrades = array_filter($qGrades);
                $finalGrade = count($validGrades) > 0 ? array_sum($validGrades) / count($validGrades) : null;

                $subjectGrades[] = [
                    'code' => $sub->subject_code,
                    'name' => $sub->subject_name,
                    'quarters' => $qGrades,
                    'final' => $finalGrade,
                    'remarks' => $finalGrade !== null ? ($finalGrade >= 75 ? 'PASSED' : 'FAILED') : 'PENDING',
                ];
            }

            $finals = array_filter(array_column($subjectGrades, 'final'));
            $genAvg = count($finals) > 0 ? array_sum($finals) / count($finals) : null;

            $data = [
                'type' => 'Form 138 (SF9 Report Card)',
                'student' => $student,
                'enrollment' => $enrollment,
                'section_instance' => $advisedSection,
                'subjects' => $subjectGrades,
                'quarters' => $quarters,
                'general_average' => $genAvg,
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
