<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\GradeMonitoringManagement();

// Test Passing Grade
$comp->updatedEditGradeValue('85.50');
echo "Grade 85.50 -> Remarks: " . $comp->editRemarks . "\n";

// Test Failing Grade
$comp->updatedEditGradeValue('72.00');
echo "Grade 72.00 -> Remarks: " . $comp->editRemarks . "\n";

// Test Edge Grade 75.00
$comp->updatedEditGradeValue('75.00');
echo "Grade 75.00 -> Remarks: " . $comp->editRemarks . "\n";
