<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SectionSubject;
use App\Models\Teacher;

$teacher = Teacher::where('employee_number', 'EMP-0001')->first();
$loads = SectionSubject::where('teacher_id', $teacher->id)->with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->get();

foreach ($loads as $l) {
    echo "Load ID {$l->id}: {$l->subject?->subject_name} ({$l->subject?->subject_code}) in {$l->schoolYearSection?->yearLevel?->name} - {$l->schoolYearSection?->section?->name}\n";
}
