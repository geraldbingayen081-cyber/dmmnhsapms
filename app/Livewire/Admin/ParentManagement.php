<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class ParentManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showLinkModal = false;
    public $showDetailModal = false;

    // Parent Form Fields
    public $parentId = null;
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $contact_number = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // Linking Modal Fields
    public $linkParentId = null;
    public $selectedStudentId = null;
    public $relationship = 'Mother';
    public $studentSearch = '';

    public $selectedParent = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['parentId', 'first_name', 'middle_name', 'last_name', 'suffix', 'contact_number']);
        $this->showCreateModal = true;
    }

    public function createParent()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
        ], [
            'contact_number.regex' => 'The contact number must consist of exactly 10 numeric digits (e.g. 9123456789 after +63).',
        ]);

        $loginId = UserIdGeneratorService::generateParentId();
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $userEmail = strtolower($loginId) . '@parent.dmmnhs.edu.ph';
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($loginId, $fullName, $userEmail, $formattedContact) {
            $user = User::create([
                'login_id' => $loginId,
                'name' => $fullName,
                'email' => $userEmail,
                'password' => Hash::make('password'),
                'role' => 'parent',
                'status' => 'active',
            ]);

            $parent = ParentModel::create([
                'user_id' => $user->id,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'contact_number' => $formattedContact,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Parent',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Created parent account {$fullName} with Parent ID {$loginId}.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Parent account successfully created with Parent ID: {$loginId}");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $parent = ParentModel::findOrFail($id);
        $this->parentId = $parent->id;
        $this->first_name = $parent->first_name;
        $this->middle_name = $parent->middle_name;
        $this->last_name = $parent->last_name;
        $this->suffix = $parent->suffix;
        $this->contact_number = $parent->contact_number ? preg_replace('/^\+63/', '', $parent->contact_number) : '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->showEditModal = true;
    }

    public function updateParent()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
        ];

        if (!empty($this->new_password)) {
            $rules['new_password'] = 'min:6|same:new_password_confirmation';
        }

        $this->validate($rules, [
            'contact_number.regex' => 'The contact number must consist of exactly 10 numeric digits (e.g. 9123456789 after +63).',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.same' => 'The password confirmation does not match.',
        ]);

        $parent = ParentModel::findOrFail($this->parentId);
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($parent, $fullName, $formattedContact) {
            $parent->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'contact_number' => $formattedContact,
            ]);

            if ($parent->user) {
                $userPayload = ['name' => $fullName];
                if (!empty($this->new_password)) {
                    $userPayload['password'] = Hash::make($this->new_password);
                }
                $parent->user->update($userPayload);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Parent Profile',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Updated parent profile" . (!empty($this->new_password) ? " & password" : "") . " for {$fullName}.",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Parent profile updated successfully!");
    }

    public function openLinkModal($id)
    {
        $this->resetValidation();
        $this->linkParentId = $id;
        $this->selectedStudentId = null;
        $this->relationship = 'Mother';
        $this->studentSearch = '';
        $this->showLinkModal = true;
    }

    public function linkChild()
    {
        $this->validate([
            'linkParentId' => 'required|exists:parents,id',
            'selectedStudentId' => 'required|exists:students,id',
            'relationship' => 'required|string|max:100',
        ]);

        $parent = ParentModel::findOrFail($this->linkParentId);
        $student = Student::findOrFail($this->selectedStudentId);

        if ($parent->students()->where('student_id', $student->id)->exists()) {
            $this->addError('selectedStudentId', 'This student child is already linked to this parent.');
            return;
        }

        DB::transaction(function () use ($parent, $student) {
            $parent->students()->attach($student->id, ['relationship' => $this->relationship]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Linked Parent and Student',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Linked parent {$parent->full_name} to student {$student->full_name} ({$student->student_number}) as {$this->relationship}.",
            ]);
        });

        $this->showLinkModal = false;
        session()->flash('message', "Student {$student->full_name} successfully linked to parent!");
    }

    public function unlinkChild($parentId, $studentId)
    {
        $parent = ParentModel::findOrFail($parentId);
        $student = Student::findOrFail($studentId);

        DB::transaction(function () use ($parent, $student) {
            $parent->students()->detach($student->id);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Unlinked Parent and Student',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Unlinked student {$student->full_name} from parent {$parent->full_name}.",
            ]);
        });

        if ($this->selectedParent && $this->selectedParent->id === $parent->id) {
            $this->viewParentDetails($parent->id);
        }

        session()->flash('message', "Student unlinked from parent.");
    }

    public function toggleStatus($parentId)
    {
        $parent = ParentModel::with('user')->findOrFail($parentId);
        if ($parent->user) {
            $newStatus = $parent->user->status === 'active' ? 'inactive' : 'active';
            $parent->user->update(['status' => $newStatus]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Changed Parent Account Status',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Set account status to {$newStatus} for parent {$parent->full_name}.",
            ]);

            session()->flash('message', "Parent status set to " . ucfirst($newStatus));
        }
    }

    public function viewParentDetails($id)
    {
        $this->selectedParent = ParentModel::with([
            'user',
            'students.user',
            'students.enrollments.schoolYearSection.yearLevel',
            'students.enrollments.schoolYearSection.section'
        ])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function render()
    {
        $query = ParentModel::with(['user', 'students.user']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('login_id', 'LIKE', '%' . $this->search . '%'));
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->whereHas('user', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        $availableStudents = !empty($this->studentSearch)
            ? Student::with('user')
                ->where('first_name', 'LIKE', '%' . $this->studentSearch . '%')
                ->orWhere('last_name', 'LIKE', '%' . $this->studentSearch . '%')
                ->orWhere('student_number', 'LIKE', '%' . $this->studentSearch . '%')
                ->take(10)->get()
            : Student::with('user')->take(10)->get();

        return view('livewire.admin.parent-management', [
            'parents' => $query->orderBy('last_name')->paginate(10),
            'availableStudents' => $availableStudents,
        ])->layout('layouts.admin', ['title' => 'Parent Management & Child Linker', 'subtitle' => 'Manage parent accounts and link family records']);
    }
}
