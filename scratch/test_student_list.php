<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = \App\Models\Student::with(['user', 'enrollments.schoolYearSection'])->get();
echo "Total Students in DB: " . $students->count() . "\n";
foreach ($students as $s) {
    echo "ID: {$s->id} | Student Number: {$s->student_number} | Name: {$s->full_name} | Enrollments Count: " . $s->enrollments->count() . "\n";
}
