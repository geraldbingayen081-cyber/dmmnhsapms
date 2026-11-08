<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Rules\SequentialSchoolYear;
use App\Services\SchoolYearService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SchoolYearManagement extends Component
{
    public $search = '';

    public $showCreateModal = false;
    public $showEditModal = false;

    public $schoolYearId = null;
    public $school_year = '';
    public $start_date = '';
    public $end_date = '';
    public $status = 'draft';

    public $isInitial = false;
    public $nextSuggestedSchoolYear = '';

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['schoolYearId', 'school_year', 'start_date', 'end_date']);
        
        $this->isInitial = !SchoolYearService::hasSchoolYears();
        $this->nextSuggestedSchoolYear = SchoolYearService::getNextAvailableSchoolYear();
        $this->school_year = $this->nextSuggestedSchoolYear;

        $suggestedDates = SchoolYearService::getSuggestedDates($this->school_year);
        $this->start_date = $suggestedDates['start_date'];
        $this->end_date = $suggestedDates['end_date'];

        $this->status = 'draft';
        $this->showCreateModal = true;
    }

    public function createSchoolYear()
    {
        $this->validate([
            'school_year' => ['required', 'string', 'unique:school_years,school_year', new SequentialSchoolYear()],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,completed,archived',
        ]);

        DB::transaction(function () {
            if ($this->status === 'active') {
                SchoolYear::where('status', 'active')->update(['status' => 'completed']);
            }

            $sy = SchoolYear::create([
                'school_year' => $this->school_year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);

            // Auto-generate Grade Levels, rotated Sections, Quarters, and Subject loads
            \App\Services\SchoolYearRotationService::generateStructureForSchoolYear($sy);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created School Year',
                'subject_type' => SchoolYear::class,
                'subject_id' => $sy->id,
                'description' => "Created School Year {$sy->school_year} ({$sy->status}) with auto-generated grade levels, rotated sections, and 3 default terms.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "School Year {$this->school_year}, grade levels, and rotated sections successfully created!");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $sy = SchoolYear::findOrFail($id);
        $this->schoolYearId = $sy->id;
        $this->school_year = $sy->school_year;
        $this->start_date = $sy->start_date;
        $this->end_date = $sy->end_date;
        $this->status = $sy->status;

        $this->showEditModal = true;
    }

    public function updateSchoolYear()
    {
        $this->validate([
            'school_year' => [
                'required',
                'string',
                'unique:school_years,school_year,' . $this->schoolYearId,
                function ($attribute, $value, $fail) {
                    $error = SchoolYearService::validateConsecutiveYears((string) $value);
                    if ($error) {
                        $fail($error);
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,completed,archived',
        ]);

        $sy = SchoolYear::findOrFail($this->schoolYearId);

        DB::transaction(function () use ($sy) {
            if ($this->status === 'active' && $sy->status !== 'active') {
                SchoolYear::where('status', 'active')->where('id', '!=', $sy->id)->update(['status' => 'completed']);
            }

            $sy->update([
                'school_year' => $this->school_year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated School Year',
                'subject_type' => SchoolYear::class,
                'subject_id' => $sy->id,
                'description' => "Updated School Year {$sy->school_year} details.",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "School Year updated successfully!");
    }

    public function activateSchoolYear($id)
    {
        DB::transaction(function () use ($id) {
            SchoolYear::where('status', 'active')->update(['status' => 'completed']);
            $sy = SchoolYear::findOrFail($id);
            $sy->update(['status' => 'active']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Activated School Year',
                'subject_type' => SchoolYear::class,
                'subject_id' => $sy->id,
                'description' => "Activated School Year {$sy->school_year}.",
            ]);
        });

        session()->flash('message', "School Year activated successfully!");
    }

    public function completeSchoolYear($id)
    {
        $sy = SchoolYear::findOrFail($id);
        $sy->update(['status' => 'completed']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Completed School Year',
            'subject_type' => SchoolYear::class,
            'subject_id' => $sy->id,
            'description' => "Set School Year {$sy->school_year} status to Completed.",
        ]);

        session()->flash('message', "School Year status set to Completed.");
    }

    public function archiveSchoolYear($id)
    {
        $sy = SchoolYear::findOrFail($id);
        $sy->update(['status' => 'archived']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Archived School Year',
            'subject_type' => SchoolYear::class,
            'subject_id' => $sy->id,
            'description' => "Archived School Year {$sy->school_year}.",
        ]);

        session()->flash('message', "School Year archived.");
    }

    public function render()
    {
        $query = SchoolYear::withCount(['schoolYearSections', 'quarters']);

        if (!empty($this->search)) {
            $query->where('school_year', 'LIKE', '%' . $this->search . '%');
        }

        return view('livewire.admin.school-year-management', [
            'schoolYears' => $query->orderBy('school_year', 'desc')->get(),
        ])->layout('layouts.admin', ['title' => 'School Year Management', 'subtitle' => 'Configure academic school year cycles and active operational statuses']);
    }
}
