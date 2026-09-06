<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class StudentManagement extends Component
{
    use WithPagination;

    // Filters & Search
    public $search = '';
    public $filterSchoolYearId = 'all';
    public $filterYearLevelId = 'all';
    public $filterSectionId = 'all';
    public $filterStatus = 'all';

    // Modal Visibility Triggers
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDetailModal = false;

    // Student Form Fields
    public $studentId = null;
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $gender = 'Male';
    public $birthdate = '';
    public $contact_number = '';
    public $school_year_section_id = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // Selected Student Profile for Detail View
    public $selectedStudent = null;

    public function mount()
    {
        $this->filterSchoolYearId = 'all';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['studentId', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender', 'birthdate', 'contact_number', 'school_year_section_id']);
        $this->showCreateModal = true;
    }

    public function createStudent()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'school_year_section_id' => 'nullable|exists:school_year_sections,id',
        ], [
            'contact_number.regex' => 'The contact number must consist of exactly 10 numeric digits (e.g. 9123456789 after +63).',
        ]);

        $loginId = UserIdGeneratorService::generateStudentId();
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $userEmail = strtolower($loginId) . '@student.dmmnhs.edu.ph';
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($loginId, $fullName, $userEmail, $formattedContact) {
            $user = User::create([
                'login_id' => $loginId,
                'name' => $fullName,
                'email' => $userEmail,
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $loginId,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'gender' => $this->gender,
                'birthdate' => $this->birthdate ?: null,
                'contact_number' => $formattedContact,
            ]);

            if ($this->school_year_section_id) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'school_year_section_id' => $this->school_year_section_id,
                    'status' => 'enrolled',
                    'enrollment_date' => now()->toDateString(),
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Student',
                'subject_type' => Student::class,
                'subject_id' => $student->id,
                'description' => "Created student {$fullName} with Student Number {$loginId}.",
            ]);
        });

        $this->showCreateModal = false;
        $this->reset(['search', 'filterSchoolYearId', 'filterYearLevelId', 'filterSectionId', 'filterStatus']);
        $this->filterSchoolYearId = 'all';
        session()->flash('message', "Student record successfully created with Student Number: {$loginId}");
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $student = Student::findOrFail($id);
        $this->studentId = $student->id;
        $this->first_name = $student->first_name;
        $this->middle_name = $student->middle_name;
        $this->last_name = $student->last_name;
        $this->suffix = $student->suffix;
        $this->gender = $student->gender;
        $this->birthdate = $student->birthdate;
        $this->contact_number = $student->contact_number ? preg_replace('/^\+63/', '', $student->contact_number) : '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->showEditModal = true;
    }

    public function updateStudent()
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

        $student = Student::findOrFail($this->studentId);
        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $formattedContact = $this->contact_number ? '+63' . preg_replace('/[^0-9]/', '', $this->contact_number) : null;

        DB::transaction(function () use ($student, $fullName, $formattedContact) {
            $student->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'gender' => $this->gender,
                'birthdate' => $this->birthdate ?: null,
                'contact_number' => $formattedContact,
            ]);

            if ($student->user) {
                $userPayload = ['name' => $fullName];
                if (!empty($this->new_password)) {
                    $userPayload['password'] = Hash::make($this->new_password);
                }
                $student->user->update($userPayload);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Student Profile',
                'subject_type' => Student::class,
                'subject_id' => $student->id,
                'description' => "Updated student details" . (!empty($this->new_password) ? " & password" : "") . " for {$fullName} ({$student->student_number}).",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Student record updated successfully!");
    }

    public function toggleStatus($studentId)
    {
        $student = Student::with('user')->findOrFail($studentId);
        if ($student->user) {
            $newStatus = $student->user->status === 'active' ? 'inactive' : 'active';
            $student->user->update(['status' => $newStatus]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Changed Student Account Status',
                'subject_type' => Student::class,
                'subject_id' => $student->id,
                'description' => "Set account status to {$newStatus} for student {$student->full_name}.",
            ]);

            session()->flash('message', "Student status set to " . ucfirst($newStatus));
        }
    }

    public function viewStudentDetails($id)
    {
        $this->selectedStudent = Student::with([
            'user',
            'enrollments.schoolYearSection.schoolYear',
            'enrollments.schoolYearSection.yearLevel',
            'enrollments.schoolYearSection.section',
            'parents.user'
        ])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function render()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $query = Student::with(['user', 'enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section', 'enrollments.schoolYearSection.schoolYear']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filterSchoolYearId !== 'all') {
            $query->whereHas('enrollments.schoolYearSection', function ($q) {
                $q->where('school_year_id', $this->filterSchoolYearId);
            });
        }

        if ($this->filterYearLevelId !== 'all') {
            $query->whereHas('enrollments.schoolYearSection', function ($q) {
                $q->where('year_level_id', $this->filterYearLevelId);
            });
        }

        if ($this->filterSectionId !== 'all') {
            $query->whereHas('enrollments.schoolYearSection', function ($q) {
                $q->where('section_id', $this->filterSectionId);
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->whereHas('user', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        $availableSections = $activeSy ? SchoolYearSection::with(['section', 'yearLevel'])->where('school_year_id', $activeSy->id)->get() : collect();

        return view('livewire.admin.student-management', [
            'students' => $query->orderBy('created_at', 'desc')->paginate(10),
            'schoolYears' => SchoolYear::orderBy('school_year', 'desc')->get(),
            'yearLevels' => YearLevel::orderBy('sort_order')->get(),
            'sections' => Section::all(),
            'availableSections' => $availableSections,
        ])->layout('layouts.admin', ['title' => 'Student Management', 'subtitle' => 'Manage student records and academic placement']);
    }
}
