<?php

namespace App\Livewire\Parent;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ParentProfile extends Component
{
    use WithFileUploads;

    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $contact_number = '';
    public $email = '';

    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public $photo;

    public function mount()
    {
        $user = Auth::user();
        $parentModel = $user->parent;

        if ($parentModel) {
            $this->first_name = $parentModel->first_name;
            $this->middle_name = $parentModel->middle_name ?? '';
            $this->last_name = $parentModel->last_name;
            $this->suffix = $parentModel->suffix ?? '';
            $this->contact_number = $parentModel->contact_number ?? '';
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
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $user = Auth::user();
        $parentModel = $user->parent;

        $fullName = trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
        $user->update([
            'name' => $fullName,
            'email' => $this->email ?: null,
        ]);

        if ($parentModel) {
            $parentModel->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix ?: null,
                'contact_number' => $this->contact_number ?: null,
            ]);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Profile Updated',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'description' => "Parent/Guardian {$user->name} updated their profile info.",
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
            'description' => "Parent/Guardian {$user->name} updated their account security credentials.",
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
            'description' => "Parent/Guardian {$user->name} uploaded a new profile picture.",
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
        return view('livewire.parent.parent-profile')
            ->layout('layouts.parent', [
                'title' => 'My Profile & Security Settings',
                'subtitle' => 'Manage your parent/guardian details, avatar photo, and security password',
            ]);
    }
}
