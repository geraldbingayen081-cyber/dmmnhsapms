<?php

namespace App\Livewire\Student;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentProfile extends Component
{
    use WithFileUploads;

    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $gender = '';
    public $birthdate = '';
    public $contact_number = '';
    public $email = '';

    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public $photo;

    public function mount()
    {
        $user = Auth::user();
        $student = $user->student;

        if ($student) {
            $this->first_name = $student->first_name;
            $this->middle_name = $student->middle_name ?? '';
            $this->last_name = $student->last_name;
            $this->suffix = $student->suffix ?? '';
            $this->gender = $student->gender ?? '';
            $this->birthdate = $student->birthdate ? ($student->birthdate instanceof \DateTimeInterface ? $student->birthdate->format('Y-m-d') : (string) $student->birthdate) : '';
            $this->contact_number = $student->contact_number ?? '';
        } else {
            $this->first_name = $user->name;
        }

        $this->email = $user->email ?? '';
    }

    public function updatePersonalInfo()
    {
        $this->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $user = Auth::user();
        $student = $user->student;

        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $user->update([
            'name' => $fullName,
            'email' => $this->email ?: null,
        ]);

        if ($student) {
            $student->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix ?: null,
                'gender' => $this->gender ?: null,
                'birthdate' => $this->birthdate ?: null,
                'contact_number' => $this->contact_number ?: null,
            ]);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Profile Updated',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'description' => "Student {$user->name} updated their profile details.",
        ]);

        session()->flash('info_message', 'Personal information updated successfully.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password provided is incorrect.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Security Password Updated',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'description' => "Student {$user->name} updated their account security credentials.",
        ]);

        session()->flash('security_message', 'Security credentials updated successfully.');
    }

    public function updatePhoto()
    {
        $this->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB Max
        ], [
            'photo.max' => 'The profile picture size must not exceed 5MB.',
        ]);

        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $this->photo->store('profile-photos', 'public');
        $user->update([
            'profile_photo_path' => $path,
        ]);

        $this->reset('photo');

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Profile Photo Uploaded',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'description' => "Student {$user->name} uploaded a new profile picture.",
        ]);

        session()->flash('photo_message', 'Profile picture updated successfully.');
    }

    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update([
            'profile_photo_path' => null,
        ]);

        session()->flash('photo_message', 'Profile picture removed.');
    }

    public function render()
    {
        return view('livewire.student.student-profile')
            ->layout('layouts.student', [
                'title' => 'My Profile & Security Settings',
                'subtitle' => 'Manage your learner details, avatar picture, and login password',
            ]);
    }
}
