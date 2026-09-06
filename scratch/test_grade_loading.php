<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;

$user = User::where('login_id', 'EMP-0001')->first();
$teacher = $user->teacher;

echo "Teacher: {$teacher->full_name}\n";

$sy = SchoolYear::where('status', 'active')->first();
echo "Active SY: {$sy->school_year} (id: {$sy->id})\n";

$quarters = Quarter::where('school_year_id', $sy->id)->get();
foreach ($quarters as $q) {
    echo "Quarter: id={$q->id}, name={$q->name}, short={$q->short_name}, status={$q->status}\n";
}

$classes = SectionSubject::where('teacher_id', $teacher->id)
    ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sy->id))
    ->with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])
    ->get();

echo "Total classes for teacher: " . $classes->count() . "\n";

$firstClass = $classes->first();
echo "First class: id={$firstClass->id}, subject={$firstClass->subject?->subject_name}, section={$firstClass->schoolYearSection?->section?->name}\n";

$enrollments = StudentEnrollment::where('school_year_section_id', $firstClass->school_year_section_id)->get();
echo "Enrollments in this section: " . $enrollments->count() . "\n";

foreach ($quarters as $q) {
    $existing = Grade::where('section_subject_id', $firstClass->id)
        ->where('quarter_id', $q->id)
        ->get();
    echo "Quarter {$q->short_name} has " . $existing->count() . " grades in DB for class {$firstClass->id}\n";
    foreach ($existing->take(2) as $g) {
        echo "   - Enrollment {$g->student_enrollment_id}: Grade = {$g->grade}\n";
    }
}
