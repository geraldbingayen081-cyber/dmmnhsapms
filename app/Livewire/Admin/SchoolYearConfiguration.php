<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\YearLevel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SchoolYearConfiguration extends Component
{
    public $schoolYearId;
    public $schoolYearModel;

    public $showAddSectionModal = false;
    public $year_level_id = '';
    public $section_id = '';
    public $adviser_id = '';

    public function mount($schoolYear)
    {
        $this->schoolYearModel = is_numeric($schoolYear) 
            ? SchoolYear::findOrFail($schoolYear) 
            : SchoolYear::where('school_year', $schoolYear)->firstOrFail();
            
        $this->schoolYearId = $this->schoolYearModel->id;
    }

    public function openAddSectionModal()
    {
        $this->resetValidation();
        $this->reset(['year_level_id', 'section_id', 'adviser_id']);
        $this->showAddSectionModal = true;
    }

    public function addSectionInstance()
    {
        $this->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
            'adviser_id' => 'nullable|exists:teachers,id',
        ]);

        $exists = SchoolYearSection::where('school_year_id', $this->schoolYearId)
            ->where('section_id', $this->section_id)
            ->exists();

        if ($exists) {
            $this->addError('section_id', 'This section master name is already assigned to this school year.');
            return;
        }

        DB::transaction(function () {
            $sys = SchoolYearSection::create([
                'school_year_id' => $this->schoolYearId,
                'year_level_id' => $this->year_level_id,
                'section_id' => $this->section_id,
                'adviser_id' => $this->adviser_id ?: null,
                'status' => 'active',
            ]);

            $secName = Section::find($this->section_id)?->name;
            $ylName = YearLevel::find($this->year_level_id)?->name;

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Added School Year Section Instance',
                'subject_type' => SchoolYearSection::class,
                'subject_id' => $sys->id,
                'description' => "Added section {$ylName} - {$secName} to SY {$this->schoolYearModel->school_year}.",
            ]);
        });

        $this->showAddSectionModal = false;
        session()->flash('message', "Section instance added to SY {$this->schoolYearModel->school_year} successfully!");
    }

    public function removeSectionInstance($id)
    {
        $sys = SchoolYearSection::withCount('enrollments')->findOrFail($id);

        if ($sys->enrollments_count > 0) {
            session()->flash('error', "Cannot remove section instance: {$sys->enrollments_count} enrolled students are attached to it.");
            return;
        }

        DB::transaction(function () use ($sys) {
            $secName = $sys->section?->name;
            $sys->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Removed School Year Section Instance',
                'subject_type' => SchoolYearSection::class,
                'subject_id' => $id,
                'description' => "Removed section instance {$secName} from SY {$this->schoolYearModel->school_year}.",
            ]);
        });

        session()->flash('message', "Section instance removed.");
    }

    public function render()
    {
        $quarters = Quarter::where('school_year_id', $this->schoolYearId)->orderBy('sort_order')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = Section::all();
        $teachers = Teacher::with('user')->get();

        $configuredSections = SchoolYearSection::with(['yearLevel', 'section', 'adviser.user', 'sectionSubjects.subject'])
            ->where('school_year_id', $this->schoolYearId)
            ->get();

        $sectionsByGradeLevel = [];
        foreach ($yearLevels as $yl) {
            $sectionsByGradeLevel[$yl->id] = [
                'yearLevel' => $yl,
                'sections' => $configuredSections->where('year_level_id', $yl->id),
            ];
        }

        return view('livewire.admin.school-year-configuration', [
            'schoolYear' => $this->schoolYearModel,
            'quarters' => $quarters,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'teachers' => $teachers,
            'sectionsByGradeLevel' => $sectionsByGradeLevel,
        ])->layout('layouts.admin', ['title' => "SY {$this->schoolYearModel->school_year} Configuration", 'subtitle' => 'Configure academic quarters, year level sections, and advisers']);
    }
}
