<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== School Years ===\n";
$sys = \App\Models\SchoolYear::orderBy('school_year')->get();
foreach ($sys as $sy) {
    echo "  SY {$sy->school_year} (id={$sy->id})\n";
}

echo "\n=== Subjects ===\n";
$subjects = \App\Models\Subject::all();
foreach ($subjects as $s) {
    echo "  [{$s->id}] {$s->subject_name}\n";
}

echo "\n=== Year Levels ===\n";
$yls = \App\Models\YearLevel::orderBy('sort_order')->get();
foreach ($yls as $yl) {
    echo "  [{$yl->id}] {$yl->name}\n";
}

echo "\n=== Grade Distribution by School Year ===\n";
foreach ($sys as $sy) {
    $enrs = \App\Models\StudentEnrollment::whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sy->id))->count();
    echo "  SY {$sy->school_year}: {$enrs} enrollments\n";
}

echo "\n=== Honor Title Counts (All Years) ===\n";
$counts = ['With Highest Honors' => 0, 'With High Honors' => 0, 'With Honors' => 0];
$enrs = \App\Models\StudentEnrollment::with(['schoolYearSection.schoolYear', 'grades.sectionSubject.subject'])->get();
foreach ($enrs as $enr) {
    $grades = $enr->grades;
    if ($grades->count() === 0) continue;
    $avg = $grades->avg('grade');
    $min = $grades->min('grade');
    if ($avg >= 98 && $min >= 85) $counts['With Highest Honors']++;
    elseif ($avg >= 95 && $min >= 85) $counts['With High Honors']++;
    elseif ($avg >= 90 && $min >= 85) $counts['With Honors']++;
}
foreach ($counts as $title => $count) {
    echo "  {$title}: {$count}\n";
}
