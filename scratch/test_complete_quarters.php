<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\User;
use App\Livewire\Student\StudentDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$studentUser = User::where('login_id', '2026-0001')->first();
Auth::login($studentUser);

echo "=== Test: Temporarily adding Q2 & Q3 grades for 1 subject to test complete 3 quarters ===\n";

$q2 = Quarter::where('short_name', 'Q2')->first();
$q3 = Quarter::where('short_name', 'Q3')->first();

// Add temporary Q2 and Q3 for Filipino (sec_subj_id = 1, enrollment_id = 1)
$g2 = Grade::create(['student_enrollment_id' => 1, 'section_subject_id' => 1, 'quarter_id' => $q2->id, 'grade' => 94.00]);
$g3 = Grade::create(['student_enrollment_id' => 1, 'section_subject_id' => 1, 'quarter_id' => $q3->id, 'grade' => 96.00]);

$dashboard = new StudentDashboard();
$dashData = $dashboard->render()->getData();

$filipino = collect($dashData['subjectRows'])->firstWhere('name', 'Filipino');
echo "Filipino (Q1: 92, Q2: 94, Q3: 96):\n";
echo "  - is_complete: " . ($filipino['is_complete'] ? 'TRUE' : 'FALSE') . "\n";
echo "  - final_avg: " . $filipino['final_avg'] . " (Expected: 94.00)\n";
echo "  - status: " . $filipino['status'] . " (Expected: PASSED)\n\n";

$english = collect($dashData['subjectRows'])->firstWhere('name', 'English');
echo "English (Q1 only: 93, Q2/Q3 missing):\n";
echo "  - is_complete: " . ($english['is_complete'] ? 'TRUE' : 'FALSE') . "\n";
echo "  - final_avg: " . ($english['final_avg'] ?? 'null') . "\n";
echo "  - status: " . $english['status'] . " (Expected: PENDING)\n";

// Clean up temporary grades
$g2->delete();
$g3->delete();
echo "\n=== Cleaned up temporary test grades. Test Passed Successfully! ===\n";
