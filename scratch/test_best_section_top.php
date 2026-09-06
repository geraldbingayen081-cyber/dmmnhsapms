<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\SchoolYearSection;
use App\Models\StudentEnrollment;

$sys = SchoolYearSection::find(1); // Rizal
$enrollments = StudentEnrollment::where('school_year_section_id', $sys->id)->with(['student', 'grades.sectionSubject.subject'])->get();

// Find max grade per subject in this section
$subjectMax = [];
foreach ($enrollments as $enr) {
    foreach ($enr->grades as $g) {
        $sub = $g->sectionSubject?->subject?->subject_name;
        if ($sub) {
            $val = (float)$g->grade;
            if (!isset($subjectMax[$sub]) || $val > $subjectMax[$sub]) {
                $subjectMax[$sub] = $val;
            }
        }
    }
}

echo "Section Rizal Max Grades per Subject:\n";
print_r($subjectMax);

foreach ($enrollments as $enr) {
    $best = [];
    foreach ($enr->grades as $g) {
        $sub = $g->sectionSubject?->subject?->subject_name;
        $val = (float)$g->grade;
        // Best in subject if >= 95 OR (is max in section AND >= 90)
        if ($sub && ($val >= 95.00 || ($val >= 90.00 && $val >= ($subjectMax[$sub] ?? 100)))) {
            $best[$sub] = $val;
        }
    }
    echo "{$enr->student?->full_name}:\n";
    foreach ($best as $s => $v) {
        echo "   - Best in {$s} ({$v})\n";
    }
}
