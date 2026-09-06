<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GradeManagement extends Component
{
    public $selectedSchoolYearSectionId = null;
    public $selectedSectionSubjectId = null;
    public $gradesData = [];

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $firstSys = SchoolYearSection::where('school_year_id', $activeSy->id)->first();
            if ($firstSys) {
                $this->selectedSchoolYearSectionId = $firstSys->id;
                $firstSs = SectionSubject::where('school_year_section_id', $firstSys->id)->first();
                $this->selectedSectionSubjectId = $firstSs ? $firstSs->id : null;
            }
        }
        $this->loadGrades();
    }

    public function updatedSelectedSchoolYearSectionId()
    {
        if ($this->selectedSchoolYearSectionId) {
            $firstSs = SectionSubject::where('school_year_section_id', $this->selectedSchoolYearSectionId)->first();
            $this->selectedSectionSubjectId = $firstSs ? $firstSs->id : null;
        }
        $this->loadGrades();
    }

    public function updatedSelectedSectionSubjectId()
    {
        $this->loadGrades();
    }

    public function loadGrades()
    {
        if (!$this->selectedSchoolYearSectionId || !$this->selectedSectionSubjectId) {
            return;
        }

        $enrollments = StudentEnrollment::where('school_year_section_id', $this->selectedSchoolYearSectionId)->get();
        $this->gradesData = [];

        foreach ($enrollments as $e) {
            $existing = Grade::where('student_enrollment_id', $e->id)
                ->where('section_subject_id', $this->selectedSectionSubjectId)
                ->get()
                ->keyBy('quarter_id');

            $this->gradesData[$e->id] = [];
            foreach ($existing as $qId => $g) {
                $this->gradesData[$e->id][$qId] = $g->grade;
            }
        }
    }

    public function saveGrades()
    {
        if (!$this->selectedSectionSubjectId) return;

        $user = Auth::user();

        // Validate that all entered grades do not exceed 99.00
        $hasErrors = false;
        foreach ($this->gradesData as $enrollmentId => $quarterGrades) {
            foreach ($quarterGrades as $quarterId => $gradeVal) {
                if ($gradeVal !== '' && $gradeVal !== null) {
                    if (!is_numeric($gradeVal) || (float)$gradeVal < 60.00 || (float)$gradeVal > 99.00) {
                        $this->addError("gradesData.{$enrollmentId}.{$quarterId}", 'Grade must be between 60.00 and 99.00 (maximum allowed is 99).');
                        $hasErrors = true;
                    }
                }
            }
        }

        if ($hasErrors) {
            session()->flash('error', 'Please correct the invalid grade entries. Grades must be between 60.00 and 99.00 (maximum allowed grade is 99).');
            return;
        }

        foreach ($this->gradesData as $enrollmentId => $quarterGrades) {
            foreach ($quarterGrades as $quarterId => $gradeVal) {
                if ($gradeVal !== '' && $gradeVal !== null && is_numeric($gradeVal)) {
                    $valFloat = round((float) $gradeVal, 2);
                    Grade::updateOrCreate(
                        [
                            'student_enrollment_id' => $enrollmentId,
                            'section_subject_id' => $this->selectedSectionSubjectId,
                            'quarter_id' => $quarterId,
                        ],
                        [
                            'grade' => $valFloat,
                            'remarks' => $valFloat >= 75.00 ? 'PASSED' : 'FAILED',
                            'status' => 'approved',
                            'created_by' => $user?->id,
                        ]
                    );
                }
            }
        }

        session()->flash('message', 'Subject Grades saved successfully! (Maximum grade validated: 99.00)');
    }

    public function render()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        
        $schoolYearSections = $activeSy ? SchoolYearSection::with(['section', 'yearLevel'])
            ->where('school_year_id', $activeSy->id)
            ->get() : collect();

        $sectionSubjects = $this->selectedSchoolYearSectionId ? SectionSubject::with('subject')
            ->where('school_year_section_id', $this->selectedSchoolYearSectionId)
            ->get() : collect();

        $enrollments = $this->selectedSchoolYearSectionId ? StudentEnrollment::with(['student.user'])
            ->where('school_year_section_id', $this->selectedSchoolYearSectionId)
            ->get() : collect();

        $quarters = $activeSy ? Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get() : collect();

        return view('livewire.grade-management', [
            'schoolYearSections' => $schoolYearSections,
            'sectionSubjects' => $sectionSubjects,
            'enrollments' => $enrollments,
            'quarters' => $quarters,
            'currentSy' => $activeSy,
        ])->layout('layouts.admin', ['title' => 'Grade Monitoring']);
    }
}
