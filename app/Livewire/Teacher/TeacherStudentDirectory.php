<?php

namespace App\Livewire\Teacher;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherStudentDirectory extends Component
{
    use WithPagination;

    public $search = '';
    public $filterYearLevelId = 'all';
    public $selectedStudentId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showStudentDetail($id)
    {
        $this->selectedStudentId = $id;
    }

    public function closeStudentDetail()
    {
        $this->selectedStudentId = null;
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $quarters = $activeSy ? Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get() : collect();

        // Get all school_year_section_ids where teacher either advises or teaches a subject
        $relevantSysIds = collect();

        if ($teacher && $activeSy) {
            // Advised sections
            $advisedSys = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->pluck('id');

            // Taught sections
            $taughtSys = SectionSubject::where('teacher_id', $teacher->id)
                ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                ->pluck('school_year_section_id');

            $relevantSysIds = $advisedSys->merge($taughtSys)->unique();
        }

        $query = StudentEnrollment::whereIn('school_year_section_id', $relevantSysIds)
            ->with([
                'student.user',
                'student.parents',
                'schoolYearSection.section',
                'schoolYearSection.yearLevel',
                'schoolYearSection.adviser.user',
                'grades.sectionSubject.subject',
                'grades.quarter'
            ]);

        if ($this->filterYearLevelId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('year_level_id', $this->filterYearLevelId));
        }

        if (!empty($this->search)) {
            $query->whereHas('student', function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('lrn', 'LIKE', '%' . $this->search . '%');
            });
        }

        $selectedEnrollment = $this->selectedStudentId ? StudentEnrollment::with([
            'student.user',
            'student.parents',
            'schoolYearSection.section',
            'schoolYearSection.yearLevel',
            'schoolYearSection.adviser.user',
            'grades.sectionSubject.subject',
            'grades.quarter'
        ])->where('student_id', $this->selectedStudentId)
          ->whereIn('school_year_section_id', $relevantSysIds)
          ->first() : null;

        return view('livewire.teacher.teacher-student-directory', [
            'enrollments' => $query->paginate(12),
            'yearLevels' => YearLevel::orderBy('sort_order')->get(),
            'selectedEnrollment' => $selectedEnrollment,
            'quarters' => $quarters,
            'activeSy' => $activeSy,
        ])->layout('layouts.teacher', [
            'title' => 'Student Directory',
            'subtitle' => 'Directory of learners enrolled in your subject classes and advisory section'
        ]);
    }
}
