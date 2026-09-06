<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sy = \App\Models\SchoolYear::first();
if (!$sy) {
    echo "No school year found.\n";
    exit(0);
}

$comp = new \App\Livewire\Admin\StudentEnrollmentManagement();
$comp->autoEnrollSchoolYearId = $sy->id;
$comp->autoEnrollYearLevelId = 'all';
$comp->processAutoEnroll();

echo "Auto-enrollment test completed cleanly.\n";
