<?php

namespace App\Livewire;

use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $role = 'student';
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $email = '';
    public $contact_number = '';
    public $gender = 'Male';
    public $password = 'password';

    public $selectedParentId = null;
    public $selectedStudentId = null;
    public $relationship = 'Guardian';

    public $search = '';
    public $filterRole = 'all';

    public function updatedRole($value)
    {
        $this->password = ($value === 'admin') ? 'admin123' : 'password';
    }

    public function createUser()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required|in:admin,teacher,student,parent',
            'email' => 'nullable|email|unique:users,email',
        ]);

        $loginId = match ($this->role) {
            'admin' => UserIdGeneratorService::generateAdminId(),
            'teacher' => UserIdGeneratorService::generateTeacherId(),
            'parent' => UserIdGeneratorService::generateParentId(),
            'student' => UserIdGeneratorService::generateStudentId(),
        };

        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $userEmail = $this->email ?: strtolower($loginId) . '@dmmnhs.edu.ph';

        DB::transaction(function () use ($loginId, $fullName, $userEmail) {
            $user = User::create([
                'login_id' => $loginId,
                'name' => $fullName,
                'email' => $userEmail,
                'password' => Hash::make($this->password ?: ($this->role === 'admin' ? 'admin123' : 'password')),
                'role' => $this->role,
                'status' => 'active',
            ]);

            if ($this->role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_number' => $loginId,
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'suffix' => $this->suffix,
                    'gender' => $this->gender,
                    'contact_number' => $this->contact_number,
                ]);
            } elseif ($this->role === 'teacher') {
                Teacher::create([
                    'user_id' => $user->id,
                    'employee_number' => $loginId,
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'suffix' => $this->suffix,
                    'contact_number' => $this->contact_number,
                ]);
            } elseif ($this->role === 'parent') {
                ParentModel::create([
                    'user_id' => $user->id,
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'suffix' => $this->suffix,
                    'contact_number' => $this->contact_number,
                ]);
            }
        });

        $this->reset(['first_name', 'middle_name', 'last_name', 'suffix', 'email', 'contact_number']);
        session()->flash('message', "User account successfully created with Account ID: {$loginId}");
    }

    public function linkParentStudent()
    {
        $this->validate([
            'selectedParentId' => 'required|exists:parents,id',
            'selectedStudentId' => 'required|exists:students,id',
            'relationship' => 'required|string|max:100',
        ]);

        $parent = ParentModel::find($this->selectedParentId);
        $parent->students()->syncWithoutDetaching([
            $this->selectedStudentId => ['relationship' => $this->relationship]
        ]);

        session()->flash('message', 'Parent successfully linked to student child!');
    }

    public function render()
    {
        $query = User::query();

        if ($this->filterRole !== 'all') {
            $query->where('role', $this->filterRole);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('login_id', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $this->search . '%');
            });
        }

        return view('livewire.user-management', [
            'users' => $query->orderBy('created_at', 'desc')->paginate(10),
            'parents' => ParentModel::with('user')->get(),
            'students' => Student::with('user')->get(),
        ])->layout('layouts.admin', ['title' => 'User & Parent Linker']);
    }
}
