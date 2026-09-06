<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$enrollment = \App\Models\StudentEnrollment::first();
if (!$enrollment) {
    echo "No student enrollments found.\n";
    exit(0);
}

$comp = new \App\Livewire\Admin\StudentEnrollmentManagement();
$reflection = new \ReflectionClass($comp);
$method = $reflection->getMethod('evaluateAcademicPerformance');
$method->setAccessible(true);

$eval = $method->invoke($comp, $enrollment);
echo "Student ID: {$enrollment->student_id}\n";
echo "Evaluation Result: " . json_encode($eval) . "\n";
