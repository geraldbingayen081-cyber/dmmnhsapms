<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Livewire\Component;
use Livewire\WithPagination;

class StudentDirectory extends Component
{
    use WithPagination;

    public $search = '';
    public $gradeLevelId = 'all';
    public $selectedStudentId = null;

    public function showStudentDetail($id)
    {
        $this->selectedStudentId = $id;
    }

    public function closeStudentDetail()
    {
        $this->selectedStudentId = null;
    }

    public function updatedGradeLevelId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $sy = AcademicContextService::getActiveSchoolYear();
        
        $query = StudentEnrollment::with([
            'student.user',
            'schoolYearSection.section',
            'schoolYearSection.yearLevel',
            'schoolYearSection.adviser.user',
            'grades.sectionSubject.subject',
            'grades.quarter'
        ]);

        if ($sy) {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sy->id));
        }

        if ($this->gradeLevelId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('year_level_id', $this->gradeLevelId));
        }

        if (!empty($this->search)) {
            $query->whereHas('student', function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        $selectedEnrollment = $this->selectedStudentId ? StudentEnrollment::with([
            'student.user',
            'student.parents.user',
            'schoolYearSection.section',
            'schoolYearSection.yearLevel',
            'schoolYearSection.adviser.user',
            'grades.sectionSubject.subject',
            'grades.quarter'
        ])->where('student_id', $this->selectedStudentId)->latest()->first() : null;

        return view('livewire.student-directory', [
            'enrollments'        => $query->paginate(12),
            'yearLevels'         => YearLevel::orderBy('sort_order')->get(),
            'selectedEnrollment' => $selectedEnrollment,
            'currentSy'          => $sy,
        ])->layout('layouts.admin', ['title' => 'Student Directory & Performance Cards']);
    }
}
