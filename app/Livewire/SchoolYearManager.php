<?php

namespace App\Livewire;

use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Rules\SequentialSchoolYear;
use App\Services\AcademicContextService;
use App\Services\SchoolYearService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SchoolYearManager extends Component
{
    public $newYear = '';
    public $startDate = '';
    public $endDate = '';
    public $selectedYearId = null;

    public function mount()
    {
        $active = AcademicContextService::getActiveSchoolYear();
        if ($active) {
            $this->selectedYearId = $active->id;
        }

        $this->newYear = SchoolYearService::getNextAvailableSchoolYear();
        $suggestedDates = SchoolYearService::getSuggestedDates($this->newYear);
        $this->startDate = $suggestedDates['start_date'];
        $this->endDate = $suggestedDates['end_date'];
    }

    public function createSchoolYear()
    {
        $this->validate([
            'newYear' => ['required', 'string', 'unique:school_years,school_year', new SequentialSchoolYear()],
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ]);

        DB::transaction(function () {
            $sy = SchoolYear::create([
                'school_year' => $this->newYear,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'status' => 'draft',
            ]);

            \App\Services\SchoolYearRotationService::generateStructureForSchoolYear($sy);
        });

        $this->newYear = SchoolYearService::getNextAvailableSchoolYear();
        $suggestedDates = SchoolYearService::getSuggestedDates($this->newYear);
        $this->startDate = $suggestedDates['start_date'];
        $this->endDate = $suggestedDates['end_date'];

        session()->flash('message', 'New School Year, Grade Levels, and Rotated Sections successfully created!');
    }

    public function setActiveYear($id)
    {
        DB::transaction(function () use ($id) {
            SchoolYear::query()->update(['status' => 'completed']);
            SchoolYear::where('id', $id)->update(['status' => 'active']);
        });

        session()->flash('message', 'Active School Year updated!');
    }

    public function render()
    {
        return view('livewire.school-year-manager', [
            'schoolYears' => SchoolYear::with('quarters')->orderBy('school_year', 'desc')->get(),
        ])->layout('layouts.admin', ['title' => 'School Year Setup']);
    }
}
