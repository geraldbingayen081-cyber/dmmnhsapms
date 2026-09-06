<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$grades = \App\Models\Grade::with([
    'studentEnrollment.student.user',
    'studentEnrollment.schoolYearSection.yearLevel',
    'studentEnrollment.schoolYearSection.section',
    'studentEnrollment.schoolYearSection.schoolYear',
    'sectionSubject.subject',
    'sectionSubject.teacher.user',
    'quarter'
])->take(5)->get();

echo "Grades count: " . $grades->count() . "\n";
foreach ($grades as $g) {
    echo "Grade ID: {$g->id} | Student: " . ($g->studentEnrollment?->student?->full_name ?? 'N/A') . " | Subject: " . ($g->sectionSubject?->subject?->subject_code ?? 'N/A') . "\n";
}
