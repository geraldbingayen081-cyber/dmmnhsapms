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
            $student = Student::with(['user', 'enrollments.schoolYearSection.schoolYear', 'enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section', 'enrollments.schoolYearSection.adviser'])->findOrFail($this->selectedStudentId);
            
            $enrollment = $student->enrollments->where('school_year_section_id', function ($q) {
                // Return latest or selected SY
            })->first() ?? $student->enrollments->last();

            $sysId = $enrollment?->school_year_section_id;

            $grades = Grade::with(['sectionSubject.subject', 'quarter'])
                ->where('student_enrollment_id', $enrollment?->id)
                ->get();

            $subjects = Subject::all();
            $quarters = Quarter::where('school_year_id', $enrollment?->schoolYearSection?->school_year_id)->orderBy('sort_order')->get();

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

            $data = [
                'type' => 'Form 138 (Report Card)',
                'student' => $student,
                'enrollment' => $enrollment,
                'subjects' => $subjectGrades,
                'general_average' => count(array_filter(array_column($subjectGrades, 'final'))) > 0 ? array_sum(array_filter(array_column($subjectGrades, 'final'))) / count(array_filter(array_column($subjectGrades, 'final'))) : null,
            ];
        } elseif ($this->reportType === 'form137') {
            $student = Student::with(['user', 'enrollments.schoolYearSection.schoolYear', 'enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section', 'enrollments.grades.sectionSubject.subject'])->findOrFail($this->selectedStudentId);
            
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

            $data = [
                'type' => 'Form 137 (Permanent Academic Record)',
                'student' => $student,
                'history' => $history,
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
                'type' => 'Summary of Quarterly Grades',
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
