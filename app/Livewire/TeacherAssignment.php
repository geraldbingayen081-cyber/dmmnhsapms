<?php

namespace App\Livewire;

use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AcademicContextService;
use Livewire\Component;

class TeacherAssignment extends Component
{
    public $selectedTeacherId = null;
    public $schoolYearSectionId = null;
    
    public $loadTeacherId = null;
    public $loadSectionSubjectId = null;

    public function assignAdviser()
    {
        $this->validate([
            'selectedTeacherId' => 'required|exists:teachers,id',
            'schoolYearSectionId' => 'required|exists:school_year_sections,id',
        ]);

        $sys = SchoolYearSection::find($this->schoolYearSectionId);
        $sys->update(['adviser_id' => $this->selectedTeacherId]);

        session()->flash('message', 'Teacher successfully assigned as Class Adviser!');
    }

    public function assignSubjectTeacher()
    {
        $this->validate([
            'loadTeacherId' => 'required|exists:teachers,id',
            'loadSectionSubjectId' => 'required|exists:section_subjects,id',
        ]);

        $ss = SectionSubject::find($this->loadSectionSubjectId);
        $ss->update(['teacher_id' => $this->loadTeacherId]);

        session()->flash('message', 'Subject teacher assigned successfully!');
    }

    public function render()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $sections = $activeSy ? SchoolYearSection::with(['section', 'yearLevel', 'adviser.user'])
            ->where('school_year_id', $activeSy->id)
            ->get() : collect();

        $sectionSubjects = $activeSy ? SectionSubject::with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject', 'teacher.user'])
            ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
            ->get() : collect();

        return view('livewire.teacher-assignment', [
            'teachers' => Teacher::with('user')->get(),
            'sections' => $sections,
            'sectionSubjects' => $sectionSubjects,
        ])->layout('layouts.admin', ['title' => 'Teacher Workload & Adviser Assignment']);
    }
}
