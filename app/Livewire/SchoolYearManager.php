<?php

namespace App\Livewire;

use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Services\AcademicContextService;
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
    }

    public function createSchoolYear()
    {
        $this->validate([
            'newYear' => 'required|string|unique:school_years,school_year|regex:/^\d{4}-\d{4}$/',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ], [
            'newYear.regex' => 'The format must be YYYY-YYYY (e.g. 2026-2027).',
        ]);

        DB::transaction(function () {
            $sy = SchoolYear::create([
                'school_year' => $this->newYear,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'status' => 'draft',
            ]);

            $quarters = [
                ['name' => '1st Quarter', 'short_name' => 'Q1', 'sort_order' => 1],
                ['name' => '2nd Quarter', 'short_name' => 'Q2', 'sort_order' => 2],
                ['name' => '3rd Quarter', 'short_name' => 'Q3', 'sort_order' => 3],
            ];

            foreach ($quarters as $q) {
                Quarter::create([
                    'school_year_id' => $sy->id,
                    'name' => $q['name'],
                    'short_name' => $q['short_name'],
                    'sort_order' => $q['sort_order'],
                    'status' => $q['sort_order'] === 1 ? 'active' : 'upcoming',
                ]);
            }
        });

        $this->reset(['newYear', 'startDate', 'endDate']);
        session()->flash('message', 'New School Year and Quarters successfully created!');
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
