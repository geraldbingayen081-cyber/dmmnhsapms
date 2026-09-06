<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class QuarterManagement extends Component
{
    public $selectedSchoolYearId = null;

    public $showCreateModal = false;
    public $showEditModal = false;

    public $quarterId = null;
    public $name = '';
    public $short_name = '';
    public $sort_order = 1;
    public $start_date = '';
    public $end_date = '';
    public $status = 'upcoming';

    public function mount($schoolYear = null)
    {
        if ($schoolYear) {
            $this->selectedSchoolYearId = is_numeric($schoolYear) ? $schoolYear : SchoolYear::where('school_year', $schoolYear)->first()?->id;
        }

        if (!$this->selectedSchoolYearId) {
            $activeSy = AcademicContextService::getActiveSchoolYear();
            if ($activeSy) {
                $this->selectedSchoolYearId = $activeSy->id;
            } else {
                $firstSy = SchoolYear::orderBy('school_year', 'desc')->first();
                $this->selectedSchoolYearId = $firstSy?->id;
            }
        }
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['quarterId', 'name', 'short_name', 'start_date', 'end_date']);
        $maxSort = Quarter::where('school_year_id', $this->selectedSchoolYearId)->max('sort_order') ?? 0;
        $this->sort_order = $maxSort + 1;
        $this->status = 'upcoming';
        $this->showCreateModal = true;
    }

    public function createQuarter()
    {
        $this->validate([
            'selectedSchoolYearId' => 'required|exists:school_years,id',
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:upcoming,active,completed',
        ]);

        DB::transaction(function () {
            if ($this->status === 'active') {
                Quarter::where('school_year_id', $this->selectedSchoolYearId)->where('status', 'active')->update(['status' => 'completed']);
            }

            $q = Quarter::create([
                'school_year_id' => $this->selectedSchoolYearId,
                'name' => $this->name,
                'short_name' => strtoupper($this->short_name),
                'sort_order' => $this->sort_order,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
                'status' => $this->status,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Quarter',
                'subject_type' => Quarter::class,
                'subject_id' => $q->id,
                'description' => "Created Quarter {$q->name} ({$q->short_name}) for selected school year.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Quarter successfully created!");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $q = Quarter::findOrFail($id);
        $this->quarterId = $q->id;
        $this->name = $q->name;
        $this->short_name = $q->short_name;
        $this->sort_order = $q->sort_order;
        $this->start_date = $q->start_date;
        $this->end_date = $q->end_date;
        $this->status = $q->status;

        $this->showEditModal = true;
    }

    public function updateQuarter()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:upcoming,active,completed',
        ]);

        $q = Quarter::findOrFail($this->quarterId);

        DB::transaction(function () use ($q) {
            if ($this->status === 'active' && $q->status !== 'active') {
                Quarter::where('school_year_id', $q->school_year_id)->where('status', 'active')->where('id', '!=', $q->id)->update(['status' => 'completed']);
            }

            $q->update([
                'name' => $this->name,
                'short_name' => strtoupper($this->short_name),
                'sort_order' => $this->sort_order,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
                'status' => $this->status,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Quarter',
                'subject_type' => Quarter::class,
                'subject_id' => $q->id,
                'description' => "Updated Quarter {$q->name} details.",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Quarter updated successfully!");
    }

    public function activateQuarter($id)
    {
        $q = Quarter::findOrFail($id);

        DB::transaction(function () use ($q) {
            Quarter::where('school_year_id', $q->school_year_id)->where('status', 'active')->update(['status' => 'completed']);
            $q->update(['status' => 'active']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Activated Quarter',
                'subject_type' => Quarter::class,
                'subject_id' => $q->id,
                'description' => "Set Quarter {$q->name} to Active.",
            ]);
        });

        session()->flash('message', "Quarter activated successfully!");
    }

    public function completeQuarter($id)
    {
        $q = Quarter::findOrFail($id);
        $q->update(['status' => 'completed']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Completed Quarter',
            'subject_type' => Quarter::class,
            'subject_id' => $q->id,
            'description' => "Set Quarter {$q->name} status to Completed.",
        ]);

        session()->flash('message', "Quarter marked as Completed.");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = $this->selectedSchoolYearId ? Quarter::where('school_year_id', $this->selectedSchoolYearId)->orderBy('sort_order')->get() : collect();

        return view('livewire.admin.quarter-management', [
            'schoolYears' => $schoolYears,
            'quarters' => $quarters,
        ])->layout('layouts.admin', ['title' => 'Quarter Management', 'subtitle' => 'Manage academic quarters and active grading periods']);
    }
}
