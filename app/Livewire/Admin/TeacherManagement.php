<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Teacher;
use App\Models\User;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all';
    public $filterYearLevelId = 'all';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDetailModal = false;

    public $teacherId = null;
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $contact_number = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public $selectedTeacher = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterYearLevelId()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['teacherId', 'first_name', 'middle_name', 'last_name', 'suffix', 'contact_number']);
        $this->showCreateModal = true;
    }

    public function createTeacher()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
        ], [
            'contact_number.regex' => 'The contact number must consist of exactly 10 numeric digits (e.g. 9123456789 after +63).',
        ]);

        $loginId = UserIdGeneratorService::generateTeacherId();
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $userEmail = strtolower($loginId) . '@faculty.dmmnhs.edu.ph';
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($loginId, $fullName, $userEmail, $formattedContact) {
            $user = User::create([
                'login_id' => $loginId,
                'name' => $fullName,
                'email' => $userEmail,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'status' => 'active',
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_number' => $loginId,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'contact_number' => $formattedContact,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Teacher',
                'subject_type' => Teacher::class,
                'subject_id' => $teacher->id,
                'description' => "Created faculty member {$fullName} with Employee Number {$loginId}.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Faculty member successfully created with Employee ID: {$loginId}");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $teacher = Teacher::findOrFail($id);
        $this->teacherId = $teacher->id;
        $this->first_name = $teacher->first_name;
        $this->middle_name = $teacher->middle_name;
        $this->last_name = $teacher->last_name;
        $this->suffix = $teacher->suffix;
        $this->contact_number = $teacher->contact_number ? preg_replace('/^\+63/', '', $teacher->contact_number) : '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->showEditModal = true;
    }

    public function updateTeacher()
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

        $teacher = Teacher::findOrFail($this->teacherId);
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($teacher, $fullName, $formattedContact) {
            $teacher->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'contact_number' => $formattedContact,
            ]);

            if ($teacher->user) {
                $userPayload = ['name' => $fullName];
                if (!empty($this->new_password)) {
                    $userPayload['password'] = Hash::make($this->new_password);
                }
                $teacher->user->update($userPayload);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Teacher Profile',
                'subject_type' => Teacher::class,
                'subject_id' => $teacher->id,
                'description' => "Updated faculty profile" . (!empty($this->new_password) ? " & password" : "") . " for {$fullName} ({$teacher->employee_number}).",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Faculty profile updated successfully!");
    }

    public function toggleStatus($teacherId)
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);
        if ($teacher->user) {
            $newStatus = $teacher->user->status === 'active' ? 'inactive' : 'active';
            $teacher->user->update(['status' => $newStatus]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Changed Teacher Account Status',
                'subject_type' => Teacher::class,
                'subject_id' => $teacher->id,
                'description' => "Set account status to {$newStatus} for faculty member {$teacher->full_name}.",
            ]);

            session()->flash('message', "Faculty status set to " . ucfirst($newStatus));
        }
    }

    public function viewTeacherDetails($id)
    {
        $this->selectedTeacher = Teacher::with([
            'user',
            'advisedSections.schoolYear',
            'advisedSections.yearLevel',
            'advisedSections.section',
            'sectionSubjects.schoolYearSection.schoolYear',
            'sectionSubjects.schoolYearSection.yearLevel',
            'sectionSubjects.schoolYearSection.section',
            'sectionSubjects.subject'
        ])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function render()
    {
        $query = Teacher::with([
            'user',
            'advisedSections.section',
            'advisedSections.yearLevel',
            'sectionSubjects.subject',
            'sectionSubjects.schoolYearSection.section'
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('employee_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->whereHas('user', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        // Filter by year level — teachers who advise OR teach in the selected year level
        if ($this->filterYearLevelId !== 'all') {
            $query->where(function ($q) {
                $q->whereHas('advisedSections', fn($sq) => $sq->where('year_level_id', $this->filterYearLevelId))
                  ->orWhereHas('sectionSubjects.schoolYearSection', fn($sq) => $sq->where('year_level_id', $this->filterYearLevelId));
            });
        }

        return view('livewire.admin.teacher-management', [
            'teachers'   => $query->orderBy('last_name')->paginate(10),
            'yearLevels' => \App\Models\YearLevel::orderBy('sort_order')->get(),
        ])->layout('layouts.admin', ['title' => 'Teacher Management', 'subtitle' => 'Manage faculty records and workload assignments']);
    }
}
