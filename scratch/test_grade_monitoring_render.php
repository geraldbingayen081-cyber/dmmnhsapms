<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\GradeMonitoringManagement();
$comp->mount();
$res = $comp->render();

echo "GradeMonitoringManagement rendered successfully. Status filter: " . $comp->filterStatus . "\n";
