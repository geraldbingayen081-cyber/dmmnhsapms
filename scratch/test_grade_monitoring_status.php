<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\GradeMonitoringManagement();
$comp->mount();

$comp->filterStatus = 'passed';
$passedCount = $comp->render()->getData()['grades']->total();

$comp->filterStatus = 'failed';
$failedCount = $comp->render()->getData()['grades']->total();

echo "Passed grades count: {$passedCount}\n";
echo "Failed grades count: {$failedCount}\n";
