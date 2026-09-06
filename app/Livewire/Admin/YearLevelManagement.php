<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\YearLevel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class YearLevelManagement extends Component
{
    public $search = '';

    public $showCreateModal = false;
    public $showEditModal = false;

    public $yearLevelId = null;
    public $name = '';
    public $code = '';
    public $sort_order = 1;

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['yearLevelId', 'name', 'code']);
        $maxSort = YearLevel::max('sort_order') ?? 0;
        $this->sort_order = $maxSort + 1;
        $this->showCreateModal = true;
    }

    public function createYearLevel()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:year_levels,name',
            'code' => 'required|string|max:50|unique:year_levels,code',
            'sort_order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () {
            $yl = YearLevel::create([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'sort_order' => $this->sort_order,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Year Level',
                'subject_type' => YearLevel::class,
                'subject_id' => $yl->id,
                'description' => "Created Year Level {$yl->name} ({$yl->code}) with sort order {$yl->sort_order}.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Year Level successfully created!");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $yl = YearLevel::findOrFail($id);
        $this->yearLevelId = $yl->id;
        $this->name = $yl->name;
        $this->code = $yl->code;
        $this->sort_order = $yl->sort_order;

        $this->showEditModal = true;
    }

    public function updateYearLevel()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:year_levels,name,' . $this->yearLevelId,
            'code' => 'required|string|max:50|unique:year_levels,code,' . $this->yearLevelId,
            'sort_order' => 'required|integer|min:1',
        ]);

        $yl = YearLevel::findOrFail($this->yearLevelId);

        DB::transaction(function () use ($yl) {
            $yl->update([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'sort_order' => $this->sort_order,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Year Level',
                'subject_type' => YearLevel::class,
                'subject_id' => $yl->id,
                'description' => "Updated Year Level {$yl->name} ({$yl->code}).",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Year Level updated successfully!");
    }

    public function render()
    {
        $query = YearLevel::query();

        if (!empty($this->search)) {
            $query->where('name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('code', 'LIKE', '%' . $this->search . '%');
        }

        return view('livewire.admin.year-level-management', [
            'yearLevels' => $query->orderBy('sort_order')->get(),
        ])->layout('layouts.admin', ['title' => 'Year Level Management', 'subtitle' => 'Manage Junior High School academic grade levels (Grade 7 – 10)']);
    }
}
