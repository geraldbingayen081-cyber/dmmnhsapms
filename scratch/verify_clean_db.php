<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Users count: " . \App\Models\User::count() . "\n";
echo "Students count: " . \App\Models\Student::count() . "\n";
echo "Teachers count: " . \App\Models\Teacher::count() . "\n";
echo "Parents count: " . \App\Models\ParentModel::count() . "\n";
echo "School Years count: " . \App\Models\SchoolYear::count() . "\n";
echo "Quarters count: " . \App\Models\Quarter::count() . "\n";
echo "Year Levels count: " . \App\Models\YearLevel::count() . "\n";
echo "Subjects count: " . \App\Models\Subject::count() . "\n";
echo "Sections count: " . \App\Models\Section::count() . "\n";
echo "Enrollments count: " . \App\Models\StudentEnrollment::count() . "\n";
echo "Grades count: " . \App\Models\Grade::count() . "\n";
