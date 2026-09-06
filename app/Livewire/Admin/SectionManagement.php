<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SectionManagement extends Component
{
    public $search = '';

    public $showCreateModal = false;
    public $showEditModal = false;

    public $sectionId = null;
    public $name = '';

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['sectionId', 'name']);
        $this->showCreateModal = true;
    }

    public function createSection()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:sections,name',
        ]);

        DB::transaction(function () {
            $sec = Section::create([
                'name' => trim($this->name),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Reusable Section Name',
                'subject_type' => Section::class,
                'subject_id' => $sec->id,
                'description' => "Created reusable section master name '{$sec->name}'.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', 'Section created successfully!');
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $sec = Section::findOrFail($id);
        $this->sectionId = $sec->id;
        $this->name = $sec->name;

        $this->showEditModal = true;
    }

    public function updateSection()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:sections,name,' . $this->sectionId,
        ]);

        $sec = Section::findOrFail($this->sectionId);

        DB::transaction(function () use ($sec) {
            $sec->update([
                'name' => trim($this->name),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Reusable Section Name',
                'subject_type' => Section::class,
                'subject_id' => $sec->id,
                'description' => "Updated section master name to '{$sec->name}'.",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', 'Section updated successfully!');
    }

    public function render()
    {
        $query = Section::withCount('studentEnrollments');

        if (!empty($this->search)) {
            $query->where('name', 'LIKE', '%' . $this->search . '%');
        }

        return view('livewire.admin.section-management', [
            'sections' => $query->orderBy('name')->get(),
        ])->layout('layouts.admin', ['title' => 'Sections', 'subtitle' => 'Manage reusable section names']);
    }
}
