<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;

$sy = SchoolYear::where('status', 'active')->first();
echo "Active SY: {$sy->school_year} (ID: {$sy->id})\n";

$allQuarters = Quarter::all();
echo "All Quarters in DB:\n";
foreach ($allQuarters as $q) {
    echo "  - ID {$q->id}: SY_ID={$q->school_year_id}, {$q->short_name} ({$q->name}), sort={$q->sort_order}, status={$q->status}\n";
}

$sampleGrades = Grade::take(10)->get();
echo "\nSample Grade Records in DB:\n";
foreach ($sampleGrades as $g) {
    echo "  - Grade ID: {$g->id}, enr_id: {$g->student_enrollment_id}, sec_subj_id: {$g->section_subject_id}, quarter_id: {$g->quarter_id}, grade: {$g->grade}\n";
}
