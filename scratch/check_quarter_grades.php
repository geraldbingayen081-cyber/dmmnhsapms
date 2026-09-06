<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\StudentEnrollment;

$quarters = Quarter::whereIn('sort_order', [1, 2, 3])->orderBy('sort_order')->get();
echo "Quarters (Q1-Q3): " . $quarters->pluck('short_name')->implode(', ') . "\n\n";

$enrollments = StudentEnrollment::with(['student.user', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->get();

echo "Checking enrollments with grades across Q1, Q2, Q3:\n";
foreach ($enrollments as $enr) {
    $st = $enr->student;
    $grades = Grade::where('student_enrollment_id', $enr->id)->whereIn('quarter_id', $quarters->pluck('id'))->get();
    $qCounts = [];
    foreach ($quarters as $q) {
        $qCounts[$q->short_name] = $grades->where('quarter_id', $q->id)->count();
    }
    $totalGrades = $grades->count();
    if ($totalGrades > 0) {
        echo "Student: {$st?->full_name} ({$st?->student_number}) [{$st?->user?->login_id}] - {$enr->schoolYearSection?->yearLevel?->name} {$enr->schoolYearSection?->section?->name}\n";
        echo "  Grades by Q: " . json_encode($qCounts) . " (Total: {$totalGrades})\n";
    }
}
