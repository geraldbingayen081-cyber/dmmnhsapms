<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Teacher;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdviserAssignment extends Component
{
    public $selectedSchoolYearId = null;

    public $showAssignModal = false;
    public $sectionInstanceId = null;
    public $teacherId = '';

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedSchoolYearId = $activeSy->id;
        } else {
            $firstSy = SchoolYear::orderBy('school_year', 'desc')->first();
            $this->selectedSchoolYearId = $firstSy?->id;
        }
    }

    public function openAssignModal($sectionInstanceId)
    {
        $this->resetValidation();
        $sys = SchoolYearSection::findOrFail($sectionInstanceId);
        $this->sectionInstanceId = $sys->id;
        $this->teacherId = $sys->adviser_id ?: '';
        $this->showAssignModal = true;
    }

    public function assignAdviser()
    {
        $this->validate([
            'sectionInstanceId' => 'required|exists:school_year_sections,id',
            'teacherId' => 'required|exists:teachers,id',
        ]);

        $sys = SchoolYearSection::with(['section', 'yearLevel', 'schoolYear'])->findOrFail($this->sectionInstanceId);
        $teacher = Teacher::findOrFail($this->teacherId);

        DB::transaction(function () use ($sys, $teacher) {
            $sys->update(['adviser_id' => $teacher->id]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Assigned Class Adviser',
                'subject_type' => SchoolYearSection::class,
                'subject_id' => $sys->id,
                'description' => "Assigned faculty member {$teacher->full_name} ({$teacher->employee_number}) as Class Adviser for {$sys->yearLevel?->name} - {$sys->section?->name} (SY {$sys->schoolYear?->school_year}).",
            ]);
        });

        $this->showAssignModal = false;
        session()->flash('message', "Faculty member {$teacher->full_name} successfully assigned as Class Adviser!");
    }

    public function removeAdviser($sectionInstanceId)
    {
        $sys = SchoolYearSection::with(['section', 'yearLevel', 'adviser'])->findOrFail($sectionInstanceId);
        $adviserName = $sys->adviser?->full_name;

        DB::transaction(function () use ($sys, $adviserName) {
            $sys->update(['adviser_id' => null]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Removed Class Adviser',
                'subject_type' => SchoolYearSection::class,
                'subject_id' => $sys->id,
                'description' => "Removed Class Adviser {$adviserName} from section {$sys->yearLevel?->name} - {$sys->section?->name}.",
            ]);
        });

        session()->flash('message', "Class Adviser assignment removed.");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        
        $sections = $this->selectedSchoolYearId 
            ? SchoolYearSection::with(['yearLevel', 'section', 'adviser.user', 'enrollments'])
                ->where('school_year_id', $this->selectedSchoolYearId)
                ->get() 
            : collect();

        $teachers = Teacher::with('user')->get();

        $targetSection = $this->sectionInstanceId ? SchoolYearSection::with(['yearLevel', 'section'])->find($this->sectionInstanceId) : null;

        return view('livewire.admin.adviser-assignment', [
            'schoolYears' => $schoolYears,
            'sections' => $sections,
            'teachers' => $teachers,
            'targetSection' => $targetSection,
        ])->layout('layouts.admin', ['title' => 'Class Adviser Assignment', 'subtitle' => 'Assign official Class Advisers to school year section instances']);
    }
}
