<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$grade = \App\Models\Grade::first();
if (!$grade) {
    echo "No grades found to edit.\n";
    exit(0);
}

$comp = new \App\Livewire\Admin\GradeMonitoringManagement();
$comp->openEditGradeModal($grade->id);
$comp->editGradeValue = '95.50';
$comp->editStatus = 'approved';
$comp->updateGrade();

$updated = \App\Models\Grade::find($grade->id);
echo "Updated grade value: " . $updated->grade . " (Remarks: " . $updated->remarks . ", Status: " . $updated->status . ")\n";
