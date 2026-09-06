<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;

$grades = Grade::where('student_enrollment_id', 1)->with(['sectionSubject.subject', 'quarter'])->get();
echo "Total grades for Enrollment 1: " . $grades->count() . "\n";
foreach ($grades as $g) {
    echo "  - {$g->sectionSubject?->subject?->subject_name} | Quarter: {$g->quarter?->short_name} (ID: {$g->quarter_id}) | Grade: {$g->grade}\n";
}
