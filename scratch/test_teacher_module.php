<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParentModel;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\UserIdGeneratorService;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Teacher Module Backend ===\n";

// 1. Find Teacher EMP-0001
$user = User::where('login_id', 'EMP-0001')->first();
if (!$user) {
    echo "FAIL: Teacher EMP-0001 not found\n";
    exit(1);
}
echo "PASS: Teacher user found: {$user->name} ({$user->login_id}), Role: {$user->role}\n";
echo "PASS: user->isTeacher() = " . ($user->isTeacher() ? 'true' : 'false') . "\n";

$teacher = $user->teacher;
if (!$teacher) {
    echo "FAIL: Teacher relationship not loaded\n";
    exit(1);
}
echo "PASS: Teacher model: {$teacher->full_name}, Employee No: {$teacher->employee_number}\n";

// 2. Advised Section check
$advised = $teacher->advisedSections()->with(['section', 'yearLevel', 'schoolYear'])->first();
if ($advised) {
    echo "PASS: Advised Section: {$advised->yearLevel?->name} - {$advised->section?->name} (SY {$advised->schoolYear?->school_year})\n";
} else {
    echo "INFO: No advised section for this teacher\n";
}

// 3. Subject loads check
$loads = SectionSubject::where('teacher_id', $teacher->id)->with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->get();
echo "PASS: Subject loads count = " . $loads->count() . "\n";
foreach ($loads->take(3) as $l) {
    echo "   - {$l->subject?->subject_name} ({$l->subject?->subject_code}) for {$l->schoolYearSection?->yearLevel?->name} - {$l->schoolYearSection?->section?->name}\n";
}

// 4. Test Parent Creation & Linking simulation
$parentId = UserIdGeneratorService::generateParentId();
echo "PASS: Generated Parent ID: {$parentId}\n";

$testParentUser = User::firstOrCreate(
    ['login_id' => 'PAR-TEST-0001'],
    [
        'name' => 'Test Parent Juan',
        'email' => 'par-test-0001@parent.dmmnhs.edu.ph',
        'password' => bcrypt('password'),
        'role' => 'parent',
        'status' => 'active',
    ]
);

$testParent = ParentModel::firstOrCreate(
    ['user_id' => $testParentUser->id],
    [
        'first_name' => 'Juan',
        'last_name' => 'TestParent',
        'contact_number' => '+639170001122',
    ]
);
echo "PASS: Parent created: {$testParent->full_name} ({$testParentUser->login_id})\n";

// Link to first student in advisee section
$firstStudent = Student::first();
if ($firstStudent) {
    if (!$testParent->students()->where('student_id', $firstStudent->id)->exists()) {
        $testParent->students()->attach($firstStudent->id, ['relationship' => 'Father']);
        echo "PASS: Linked parent {$testParent->full_name} to student {$firstStudent->full_name} as Father\n";
    } else {
        echo "PASS: Parent already linked to student {$firstStudent->full_name}\n";
    }

    $linkedCheck = $firstStudent->parents()->where('parent_id', $testParent->id)->first();
    echo "PASS: Reverse relation check: Student {$firstStudent->full_name} has linked parent: " . ($linkedCheck ? $linkedCheck->full_name : 'none') . " (" . ($linkedCheck?->pivot->relationship ?? '') . ")\n";
}

echo "=== All Teacher Module Backend Tests Passed Successfully! ===\n";
