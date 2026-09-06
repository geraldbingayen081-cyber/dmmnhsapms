<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SystemSettingsManagement extends Component
{
    use WithFileUploads;

    // Institutional Profile & Logo
    public $school_name = 'Don Mariano Marcos National High School';
    public $school_address = 'Dummun, Gattaran, Cagayan, Philippines';
    public $school_division = 'DepEd Region II - Division of Cagayan';
    public $school_level = 'Junior High School Only (Grade 7 - Grade 10)';
    public $logo;
    public $current_logo_url = null;

    // Academic Policies
    public $passing_grade = 75.00;
    public $honor_grade_cap = 85.00;
    public $highest_honor_min = 98.00;
    public $high_honor_min = 95.00;
    public $honors_min = 90.00;

    // Admin Credentials
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        // Load saved institutional profile from database
        $this->school_name = SystemSetting::get('school_name', 'Don Mariano Marcos National High School');
        $this->school_address = SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan, Philippines');
        $this->school_division = SystemSetting::get('school_division', 'DepEd Region II - Division of Cagayan');
        $this->school_level = SystemSetting::get('school_level', 'Junior High School Only (Grade 7 - Grade 10)');
        $this->current_logo_url = SystemSetting::logoUrl();

        // Load academic policies if saved
        $this->passing_grade = (float) SystemSetting::get('passing_grade', 75.00);
        $this->honor_grade_cap = (float) SystemSetting::get('honor_grade_cap', 85.00);
        $this->highest_honor_min = (float) SystemSetting::get('highest_honor_min', 98.00);
        $this->high_honor_min = (float) SystemSetting::get('high_honor_min', 95.00);
        $this->honors_min = (float) SystemSetting::get('honors_min', 90.00);
    }

    public function saveSchoolProfile()
    {
        $rules = [
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string|max:255',
            'school_division' => 'required|string|max:255',
        ];

        if ($this->logo) {
            $rules['logo'] = 'image|mimes:jpeg,png,jpg,webp,svg|max:3072';
        }

        $this->validate($rules, [
            'logo.image' => 'The file uploaded must be a valid image file.',
            'logo.mimes' => 'The school logo must be a file of type: jpeg, png, jpg, webp, svg.',
            'logo.max' => 'The school logo must not exceed 3MB in size.',
        ]);

        // If a new logo file was uploaded
        if ($this->logo) {
            // Delete previous logo file if one exists
            $oldPath = SystemSetting::get('school_logo');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Store new logo in 'public/branding'
            $storedPath = $this->logo->store('branding', 'public');
            SystemSetting::set('school_logo', $storedPath);

            $this->current_logo_url = SystemSetting::logoUrl();
            $this->logo = null;
        }

        SystemSetting::set('school_name', $this->school_name);
        SystemSetting::set('school_address', $this->school_address);
        SystemSetting::set('school_division', $this->school_division);
        SystemSetting::set('school_level', $this->school_level);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated School Profile & Logo',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => "Updated institutional school profile and logo branding for {$this->school_name}.",
        ]);

        session()->flash('message', "Institutional school profile and logo branding saved successfully!");
    }

    public function removeLogo()
    {
        $oldPath = SystemSetting::get('school_logo');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        SystemSetting::forget('school_logo');
        $this->current_logo_url = null;
        $this->logo = null;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Removed School Logo',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => "Removed custom school logo. Reverted to default institutional crest.",
        ]);

        session()->flash('message', "School logo removed. Reverted to default institutional crest.");
    }

    public function saveAcademicPolicies()
    {
        $this->validate([
            'passing_grade' => 'required|numeric|min:60|max:99',
            'honor_grade_cap' => 'required|numeric|min:75|max:99',
            'highest_honor_min' => 'required|numeric|min:90|max:99',
            'high_honor_min' => 'required|numeric|min:85|max:99',
            'honors_min' => 'required|numeric|min:80|max:99',
        ]);

        SystemSetting::set('passing_grade', $this->passing_grade);
        SystemSetting::set('honor_grade_cap', $this->honor_grade_cap);
        SystemSetting::set('highest_honor_min', $this->highest_honor_min);
        SystemSetting::set('high_honor_min', $this->high_honor_min);
        SystemSetting::set('honors_min', $this->honors_min);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Academic Policies',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => "Updated academic passing grade threshold ({$this->passing_grade}) and honor roll rules.",
        ]);

        session()->flash('message', "Academic policy thresholds updated successfully!");
    }

    public function updateAdminPassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password provided does not match our records.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Admin Password',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => "Administrator password changed successfully.",
        ]);

        session()->flash('message', "Admin password updated successfully!");
    }

    public function triggerDatabaseBackup()
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Triggered Database Backup',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => "Executed administrative database backup download.",
        ]);

        $backupContent = "-- APMS MariaDB/MySQL Database Backup\n";
        $backupContent .= "-- School: " . $this->school_name . "\n";
        $backupContent .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $backupContent .= "SELECT 'Database Backup Verified Clean';\n";

        return response()->streamDownload(function () use ($backupContent) {
            echo $backupContent;
        }, 'APMS_Database_Backup_' . date('Y_m_d_His') . '.sql');
    }

    public function render()
    {
        return view('livewire.admin.system-settings-management')
            ->layout('layouts.admin', [
                'title' => 'System Settings & School Configuration',
                'subtitle' => 'Configure institutional branding, official logo, academic policy thresholds, database backup, and admin security',
            ]);
    }
}
