<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\SectionSubject;

echo "Sample Q2 grades:\n";
foreach (Grade::where('quarter_id', 2)->take(10)->get() as $g) {
    $ss = SectionSubject::with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->find($g->section_subject_id);
    echo "Grade ID {$g->id}: Enr={$g->student_enrollment_id}, SS_ID={$g->section_subject_id} ({$ss?->subject?->subject_name} for {$ss?->schoolYearSection?->yearLevel?->name} {$ss?->schoolYearSection?->section?->name}), Grade={$g->grade}\n";
}
