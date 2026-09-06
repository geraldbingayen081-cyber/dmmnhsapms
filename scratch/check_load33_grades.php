<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;

$load = SectionSubject::find(33); // Filipino for Grade 7 Mercury
echo "Load: {$load->subject?->subject_name} for {$load->schoolYearSection?->yearLevel?->name} - {$load->schoolYearSection?->section?->name}\n";

$enrollments = StudentEnrollment::where('school_year_section_id', $load->school_year_section_id)->with('student')->get();
echo "Total enrollments: {$enrollments->count()}\n";

$quarters = Quarter::where('school_year_id', 1)->orderBy('sort_order')->get();

foreach ($enrollments as $enr) {
    echo "Student: {$enr->student?->full_name} ({$enr->student?->student_number})\n";
    foreach ($quarters as $q) {
        $g = Grade::where('student_enrollment_id', $enr->id)
            ->where('section_subject_id', $load->id)
            ->where('quarter_id', $q->id)
            ->first();
        echo "   {$q->short_name} ({$q->status}): " . ($g ? $g->grade : 'NO GRADE') . "\n";
    }
}
