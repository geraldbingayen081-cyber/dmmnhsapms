<?php

namespace App\Livewire\Teacher;

use App\Models\ActivityLog;
use App\Models\ParentModel;
use App\Models\SchoolYearSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\AcademicContextService;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherParentManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterScope = 'advisees'; // 'advisees' or 'all'

    // Create / Edit Modal State
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showLinkModal = false;
    public $showDetailModal = false;

    // Parent Form
    public $parentId = null;
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $contact_number = '';

    // Link Modal
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
            'contact_number.regex' => 'Contact number must be 10 digits after +63 (e.g. 9171234567).',
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
                'action' => 'Created Parent Account',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Teacher created parent account for {$fullName} with ID {$loginId}.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('message', "Parent account created successfully with ID: {$loginId} (Default Password: password)");
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

        $this->showEditModal = true;
    }

    public function updateParent()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
        ], [
            'contact_number.regex' => 'Contact number must be 10 digits after +63 (e.g. 9171234567).',
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
                $parent->user->update(['name' => $fullName]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Parent Profile',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Teacher updated parent profile for {$fullName}.",
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
            $this->addError('selectedStudentId', 'This student is already linked to this parent.');
            return;
        }

        DB::transaction(function () use ($parent, $student) {
            $parent->students()->attach($student->id, ['relationship' => $this->relationship]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Linked Parent and Student',
                'subject_type' => ParentModel::class,
                'subject_id' => $parent->id,
                'description' => "Teacher linked parent {$parent->full_name} to student {$student->full_name} as {$this->relationship}.",
            ]);
        });

        $this->showLinkModal = false;
        session()->flash('message', "Student {$student->full_name} successfully linked to {$parent->full_name}!");
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
                'description' => "Teacher unlinked parent {$parent->full_name} from student {$student->full_name}.",
            ]);
        });

        session()->flash('message', "Parent {$parent->full_name} unlinked from student {$student->full_name}.");
    }

    public function viewParentDetails($id)
    {
        $this->selectedParent = ParentModel::with(['user', 'students'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        // Advisee students list for quick filtering / linking
        $adviseeIds = collect();
        if ($teacher && $activeSy) {
            $advisedSection = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->first();

            if ($advisedSection) {
                $adviseeIds = StudentEnrollment::where('school_year_section_id', $advisedSection->id)->pluck('student_id');
            }
        }

        $query = ParentModel::with(['user', 'students']);

        if ($this->filterScope === 'advisees' && $adviseeIds->isNotEmpty()) {
            $query->whereHas('students', fn($q) => $q->whereIn('students.id', $adviseeIds));
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('login_id', 'LIKE', '%' . $this->search . '%'))
                  ->orWhereHas('students', function ($sq) {
                      $sq->where('first_name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('student_number', 'LIKE', '%' . $this->search . '%');
                  });
            });
        }

        // Available students for linking dropdown
        $studentsQuery = Student::query();
        if (!empty($this->studentSearch)) {
            $studentsQuery->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->studentSearch . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->studentSearch . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->studentSearch . '%');
            });
        }
        $availableStudents = $studentsQuery->orderBy('last_name')->take(25)->get();

        return view('livewire.teacher.teacher-parent-management', [
            'parents' => $query->orderBy('last_name')->paginate(10),
            'availableStudents' => $availableStudents,
            'adviseeIds' => $adviseeIds,
        ])->layout('layouts.teacher', [
            'title' => 'Parent Accounts & Linker',
            'subtitle' => 'Register parent accounts and link them to their children for portal access'
        ]);
    }
}
