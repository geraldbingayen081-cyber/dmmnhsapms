<?php

use App\Livewire\Admin\AcademicAchievementsManagement;
use App\Livewire\Admin\AcademicAnalyticsManagement;
use App\Livewire\Admin\ActivityLogManagement;
use App\Livewire\Admin\AdviserAssignment;
use App\Livewire\Admin\GradeMonitoringManagement;
use App\Livewire\Admin\ParentManagement;
use App\Livewire\Admin\QuarterManagement;
use App\Livewire\Admin\ReportManagement;
use App\Livewire\Admin\SchoolYearConfiguration;
use App\Livewire\Admin\SchoolYearManagement;
use App\Livewire\Admin\SectionManagement;
use App\Livewire\Admin\StudentEnrollmentManagement;
use App\Livewire\Admin\StudentManagement;
use App\Livewire\Admin\SubjectManagement;
use App\Livewire\Admin\SubjectTeacherAssignment;
use App\Livewire\Admin\SystemSettingsManagement;
use App\Livewire\Admin\TeacherManagement;
use App\Livewire\Admin\YearLevelManagement;
use App\Livewire\DashboardAnalytics;
use App\Livewire\Parent\ParentAcademicHistory;
use App\Livewire\Parent\ParentAchievements;
use App\Livewire\Parent\ParentDashboard;
use App\Livewire\Parent\ParentGrades;
use App\Livewire\Parent\ParentProfile;
use App\Livewire\Student\StudentAcademicHistory;
use App\Livewire\Student\StudentAchievements;
use App\Livewire\Student\StudentDashboard;
use App\Livewire\Student\StudentGrades;
use App\Livewire\Student\StudentProfile;
use App\Livewire\Teacher\AdvisoryManagement;
use App\Livewire\Teacher\GradeEncoding;
use App\Livewire\Teacher\SubjectClasses;
use App\Livewire\Teacher\TeacherDashboard;
use App\Livewire\Teacher\TeacherParentManagement;
use App\Livewire\Teacher\TeacherProfile;
use App\Livewire\Teacher\TeacherReportManagement;
use App\Livewire\Teacher\TeacherStudentDirectory;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Default Auth Dashboard Redirect
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user && $user->isTeacher()) {
        return redirect()->route('teacher.dashboard');
    }
    if ($user && $user->isStudent()) {
        return redirect()->route('student.dashboard');
    }
    if ($user && $user->isParent()) {
        return redirect()->route('parent.dashboard');
    }

    return redirect()->route('login');
})->middleware(['auth', 'prevent-back-history'])->name('dashboard');

// Session Termination / Logout Route
Route::any('/logout', function (Request $request) {
    if (auth()->check()) {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'User Session Terminated',
            'subject_type' => User::class,
            'subject_id' => auth()->id(),
            'description' => 'User session terminated for account '.(auth()->user()->login_id ?? auth()->user()->name).'.',
        ]);
    }
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $response = redirect()->route('login');
    $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    $response->headers->set('Clear-Site-Data', '"cache", "storage"');

    return $response;
})->name('logout');

// Protected Admin Module Route Group
Route::middleware(['auth', 'admin', 'prevent-back-history'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', DashboardAnalytics::class)->name('dashboard');
    Route::get('/students', StudentManagement::class)->name('students');
    Route::get('/teachers', TeacherManagement::class)->name('teachers');
    Route::get('/teacher-assignments', SubjectTeacherAssignment::class)->name('teacher-assignments');
    Route::get('/school-years/{schoolYear}/sections/{section}/subjects', SubjectTeacherAssignment::class)->name('sections.subjects');
    Route::get('/parents', ParentManagement::class)->name('parents');
    Route::get('/year-levels', YearLevelManagement::class)->name('year-levels');
    Route::get('/sections', SectionManagement::class)->name('sections');
    Route::get('/school-years', SchoolYearManagement::class)->name('school-years');
    Route::get('/school-years/{schoolYear}/configure', SchoolYearConfiguration::class)->name('school-years.configure');
    Route::get('/terms', QuarterManagement::class)->name('terms');
    Route::get('/school-years/{schoolYear}/terms', QuarterManagement::class)->name('school-years.terms');
    Route::get('/quarters', QuarterManagement::class)->name('quarters');
    Route::get('/school-years/{schoolYear}/quarters', QuarterManagement::class)->name('school-years.quarters');
    Route::get('/advisers', AdviserAssignment::class)->name('advisers');
    Route::get('/subjects', SubjectManagement::class)->name('subjects');
    Route::get('/enrollments', StudentEnrollmentManagement::class)->name('enrollments');
    Route::get('/grades', GradeMonitoringManagement::class)->name('grades');
    Route::get('/analytics', AcademicAnalyticsManagement::class)->name('analytics');
    Route::get('/achievements', AcademicAchievementsManagement::class)->name('achievements');
    Route::get('/reports', ReportManagement::class)->name('reports');
    Route::get('/activity-logs', ActivityLogManagement::class)->name('activity-logs');
    Route::get('/settings', SystemSettingsManagement::class)->name('settings');
});

// Protected Teacher / Faculty Module Route Group
Route::middleware(['auth', 'teacher', 'prevent-back-history'])->prefix('teacher')->as('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
    Route::get('/classes', SubjectClasses::class)->name('classes');
    Route::get('/grades/{classId?}', GradeEncoding::class)->name('grades');
    Route::get('/advisory', AdvisoryManagement::class)->name('advisory');
    Route::get('/parents', TeacherParentManagement::class)->name('parents');
    Route::get('/reports', TeacherReportManagement::class)->name('reports');
    Route::get('/students', TeacherStudentDirectory::class)->name('students');
    Route::get('/profile', TeacherProfile::class)->name('profile');
});

// Protected Student / Learner Module Route Group
Route::middleware(['auth', 'student', 'prevent-back-history'])->prefix('student')->as('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
    Route::get('/grades', StudentGrades::class)->name('grades');
    Route::get('/history', StudentAcademicHistory::class)->name('history');
    Route::get('/achievements', StudentAchievements::class)->name('achievements');
    Route::get('/profile', StudentProfile::class)->name('profile');
});

// Protected Parent / Guardian Module Route Group
Route::middleware(['auth', 'parent', 'prevent-back-history'])->prefix('parent')->as('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->name('dashboard');
    Route::get('/grades', ParentGrades::class)->name('grades');
    Route::get('/history', ParentAcademicHistory::class)->name('history');
    Route::get('/achievements', ParentAchievements::class)->name('achievements');
    Route::get('/profile', ParentProfile::class)->name('profile');
});

require __DIR__.'/settings.php';
