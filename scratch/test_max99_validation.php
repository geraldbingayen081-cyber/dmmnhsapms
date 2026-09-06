<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Admin\GradeMonitoringManagement;
use App\Livewire\GradeManagement;
use App\Livewire\Teacher\GradeEncoding;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Max 99 Validation for Admin and Teacher ===\n\n";

// 1. Test Admin GradeMonitoringManagement validation
echo "1. Testing Admin GradeMonitoringManagement edit modal:\n";
$adminUser = User::where('role', 'admin')->first();
Auth::login($adminUser);

$sampleGrade = Grade::first();
$adminMonitoring = new GradeMonitoringManagement();
$adminMonitoring->openEditGradeModal($sampleGrade->id);

// Try setting to 100
$adminMonitoring->editGradeValue = '100.00';
try {
    $adminMonitoring->updateGrade();
    echo "  [FAIL] Did not reject 100.00!\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "  [PASS] Successfully caught ValidationException for 100.00: " . $e->validator->errors()->first('editGradeValue') . "\n";
}

// Try setting to 99.00
$adminMonitoring->editGradeValue = '99.00';
try {
    $adminMonitoring->updateGrade();
    echo "  [PASS] Accepted valid maximum grade 99.00!\n";
} catch (\Exception $e) {
    echo "  [FAIL] Threw error for 99.00: " . $e->getMessage() . "\n";
}

// 2. Test Admin GradeManagement
echo "\n2. Testing Admin GradeManagement grid save:\n";
$adminGrid = new GradeManagement();
$adminGrid->selectedSchoolYearSectionId = 1;
$adminGrid->selectedSectionSubjectId = 1;
$adminGrid->loadGrades();

// Put 100 in first student
$enrId = array_key_first($adminGrid->gradesData);
$adminGrid->gradesData[$enrId][1] = '100.00';
$adminGrid->saveGrades();
if ($adminGrid->getErrorBag()->has("gradesData.{$enrId}.1")) {
    echo "  [PASS] Successfully rejected 100.00 in Admin GradeManagement: " . $adminGrid->getErrorBag()->first("gradesData.{$enrId}.1") . "\n";
} else {
    echo "  [FAIL] Failed to reject 100.00 in Admin GradeManagement!\n";
}

// 3. Test Teacher GradeEncoding
echo "\n3. Testing Teacher GradeEncoding save:\n";
$teacherUser = User::where('role', 'teacher')->first();
Auth::login($teacherUser);

$teacherEncoding = new GradeEncoding();
$secSubj = SectionSubject::where('teacher_id', $teacherUser->teacher?->id)->first();
$activeQ = Quarter::where('status', 'active')->first();
$teacherEncoding->selectedClassId = $secSubj->id;
$teacherEncoding->selectedQuarterId = $activeQ->id;
$teacherEncoding->loadGrades();

$firstEnr = StudentEnrollment::where('school_year_section_id', $secSubj->school_year_section_id)->first();
$teacherEncoding->grades[$firstEnr->id] = '100.00';
$teacherEncoding->saveGrades();

if ($teacherEncoding->getErrorBag()->has("grades.{$firstEnr->id}")) {
    echo "  [PASS] Successfully rejected 100.00 in Teacher GradeEncoding: " . $teacherEncoding->getErrorBag()->first("grades.{$firstEnr->id}") . "\n";
} else {
    echo "  [FAIL] Failed to reject 100.00 in Teacher GradeEncoding!\n";
}

// Test Teacher with 99.00
$teacherEncoding->resetErrorBag();
$teacherEncoding->grades[$firstEnr->id] = '99.00';
$teacherEncoding->saveGrades();
if (!$teacherEncoding->getErrorBag()->has("grades.{$firstEnr->id}")) {
    echo "  [PASS] Successfully accepted valid grade 99.00 in Teacher GradeEncoding!\n";
} else {
    echo "  [FAIL] Threw error for valid 99.00 in Teacher GradeEncoding: " . $teacherEncoding->getErrorBag()->first("grades.{$firstEnr->id}") . "\n";
}

echo "\n=== All Max 99 Validation Tests Passed! ===\n";
