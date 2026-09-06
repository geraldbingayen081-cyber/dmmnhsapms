<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Teacher\GradeEncoding;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Grade Encoding Component Logic ===\n";

$user = User::where('login_id', 'EMP-0001')->first();
Auth::login($user);

$component = new GradeEncoding();
$component->mount(33); // Filipino in Grade 7 - Mercury

echo "Selected Class: {$component->selectedClassId}\n";
echo "Selected Quarter: {$component->selectedQuarterId}\n";

$quarter = Quarter::find($component->selectedQuarterId);
echo "Quarter: {$quarter->name} ({$quarter->short_name}), Status: {$quarter->status}\n";

// Check grades loaded
echo "Loaded grades count: " . count($component->grades) . "\n";
foreach ($component->grades as $enrId => $val) {
    echo "  - Enrollment ID {$enrId}: Grade = '{$val}'\n";
}

// Test 1: Save while on active quarter (Q3)
echo "\n--- Test 1: Saving on Active Quarter (Q3) ---\n";
$component->grades[array_key_first($component->grades)] = '95.50';
$component->saveGrades();
if (session()->has('message')) {
    echo "PASS: Success message: " . session('message') . "\n";
} else {
    echo "FAIL: Expected success message\n";
}

// Test 2: Switch to Q1 (Completed / Inactive) and try to save
echo "\n--- Test 2: Saving on Inactive Quarter (Q1) ---\n";
$q1 = Quarter::where('short_name', 'Q1')->where('school_year_id', 1)->first();
$component->selectedQuarterId = $q1->id;
$component->updatedSelectedQuarterId();

echo "Quarter switched to: {$q1->name}, Status: {$q1->status}\n";
echo "Loaded Q1 grades count: " . count($component->grades) . "\n";
foreach ($component->grades as $enrId => $val) {
    echo "  - Q1 Enrollment ID {$enrId}: Existing Grade = '{$val}'\n";
}

$component->saveGrades();
if (session()->has('error')) {
    echo "PASS: Blocked save on locked quarter! Error message: " . session('error') . "\n";
} else {
    echo "FAIL: Expected error message for locked quarter\n";
}

echo "\n=== All Tests Completed Successfully! ===\n";
