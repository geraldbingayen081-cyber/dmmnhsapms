<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;

echo "Total Grades in DB: " . Grade::count() . "\n";

foreach (Quarter::all() as $q) {
    echo "Quarter {$q->short_name} (ID: {$q->id}, Status: {$q->status}): " . Grade::where('quarter_id', $q->id)->count() . " grades\n";
}

$sample = Grade::take(5)->get();
foreach ($sample as $g) {
    $ss = SectionSubject::with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->find($g->section_subject_id);
    echo "Grade ID {$g->id}: Enrollment={$g->student_enrollment_id}, SS_ID={$g->section_subject_id} ({$ss?->subject?->subject_name} - {$ss?->schoolYearSection?->section?->name}), Q={$g->quarter_id}, Grade={$g->grade}\n";
}
