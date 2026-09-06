<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $showCreateModal = false;
    public $showEditModal = false;

    public $subjectId = null;
    public $subject_code = '';
    public $subject_name = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['subjectId', 'subject_code', 'subject_name']);
        $this->showCreateModal = true;
    }

    public function createSubject()
    {
        $this->validate([
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code',
            'subject_name' => 'required|string|max:255',
        ]);

        DB::transaction(function () {
            $sub = Subject::create([
                'subject_code' => strtoupper(trim($this->subject_code)),
                'subject_name' => trim($this->subject_name),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Subject',
                'subject_type' => Subject::class,
                'subject_id' => $sub->id,
                'description' => "Created subject {$sub->subject_name} ({$sub->subject_code}).",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Subject successfully created!");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $sub = Subject::findOrFail($id);
        $this->subjectId = $sub->id;
        $this->subject_code = $sub->subject_code;
        $this->subject_name = $sub->subject_name;

        $this->showEditModal = true;
    }

    public function updateSubject()
    {
        $this->validate([
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code,' . $this->subjectId,
            'subject_name' => 'required|string|max:255',
        ]);

        $sub = Subject::findOrFail($this->subjectId);

        DB::transaction(function () use ($sub) {
            $sub->update([
                'subject_code' => strtoupper(trim($this->subject_code)),
                'subject_name' => trim($this->subject_name),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Subject',
                'subject_type' => Subject::class,
                'subject_id' => $sub->id,
                'description' => "Updated subject details for {$sub->subject_name} ({$sub->subject_code}).",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Subject updated successfully!");
    }

    public function render()
    {
        $query = Subject::withCount('sectionSubjects');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('subject_code', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('subject_name', 'LIKE', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.subject-management', [
            'subjects' => $query->orderBy('subject_code')->paginate(10),
        ])->layout('layouts.admin', ['title' => 'Subject Management', 'subtitle' => 'Manage DepEd core subjects and learning area definitions']);
    }
}
