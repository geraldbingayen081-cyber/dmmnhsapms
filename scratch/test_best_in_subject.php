<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;

$user = User::where('login_id', 'EMP-0001')->first();
$teacher = $user->teacher;
$sy = SchoolYear::where('status', 'active')->first();

echo "Teacher: {$teacher->full_name}\n";

$advisedSection = SchoolYearSection::where('school_year_id', $sy->id)
    ->where('adviser_id', $teacher->id)
    ->with(['section', 'yearLevel'])
    ->first();

if (!$advisedSection) {
    echo "No advised section!\n";
    exit;
}

echo "Advised Section: {$advisedSection->yearLevel?->name} - {$advisedSection->section?->name} (ID: {$advisedSection->id})\n";

$enrollments = StudentEnrollment::where('school_year_section_id', $advisedSection->id)
    ->with(['student.achievements', 'grades.sectionSubject.subject'])
    ->get();

echo "Total advisees: {$enrollments->count()}\n\n";

foreach ($enrollments as $enr) {
    echo "Student: {$enr->student?->full_name} ({$enr->student?->student_number})\n";
    $grades = $enr->grades;
    $avg = $grades->avg('grade');
    $min = $grades->min('grade');
    echo "  Average: {$avg}, Min Grade: {$min}\n";

    // Best in subjects (grade >= 95)
    $best = [];
    foreach ($grades as $g) {
        $sub = $g->sectionSubject?->subject?->subject_name;
        if ($sub && $g->grade >= 95.00) {
            $best[$sub] = max($best[$sub] ?? 0, (float)$g->grade);
        }
    }
    foreach ($best as $subName => $gradeVal) {
        echo "  - Best in {$subName} ({$gradeVal})\n";
    }
    echo "\n";
}
