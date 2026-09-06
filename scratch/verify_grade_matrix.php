<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('login_id', 'EMP-0001')->first();
Auth::login($user);

// Use Load ID 33 (Filipino, Grade 7 Mercury) which has grades in Q1, Q2, Q3
$classId = 33;

$class = SectionSubject::find($classId);
$quarters = Quarter::where('school_year_id', 1)->orderBy('sort_order')->get();
$enrollments = StudentEnrollment::where('school_year_section_id', $class->school_year_section_id)
    ->with('student')
    ->get()
    ->sortBy(fn($e) => $e->student?->last_name);

echo "=== Grade Matrix for Load {$classId}: {$class->subject?->subject_name} - {$class->schoolYearSection?->section?->name} ===\n\n";

// Build the matrix the same way GradeEncoding::render() does
$allGrades = Grade::where('section_subject_id', $classId)
    ->whereIn('student_enrollment_id', $enrollments->pluck('id'))
    ->get();

printf("%-30s", "Student");
foreach ($quarters as $q) {
    printf("%-12s", "{$q->short_name}({$q->status})");
}
printf("%-12s", "Gen Avg");
echo "\n" . str_repeat('-', 30 + 12 * (count($quarters) + 1)) . "\n";

foreach ($enrollments as $enr) {
    printf("%-30s", $enr->student?->last_name . ', ' . $enr->student?->first_name);
    $matrixVals = [];
    foreach ($quarters as $q) {
        $record = $allGrades
            ->where('student_enrollment_id', $enr->id)
            ->where('quarter_id', $q->id)
            ->first();
        $val = $record ? (float)$record->grade : null;
        $matrixVals[] = $val;
        printf("%-12s", $val !== null ? number_format($val, 2) : '—');
    }
    $withData = array_filter($matrixVals, fn($v) => $v !== null);
    $genAvg = count($withData) > 0 ? round(array_sum($withData) / count($withData), 2) : null;
    printf("%-12s", $genAvg !== null ? number_format($genAvg, 2) : '—');
    echo "\n";
}

echo "\n=== Quarter Status Check ===\n";
foreach ($quarters as $q) {
    $canEncode = $q->status === 'active' ? 'YES - CAN ENCODE' : 'NO - LOCKED';
    echo "{$q->name} ({$q->short_name}): Status={$q->status} => {$canEncode}\n";
}
