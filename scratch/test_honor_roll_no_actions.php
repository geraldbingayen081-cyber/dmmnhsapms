<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\AcademicAchievementsManagement();
$comp->mount();
$view = $comp->render();

echo "AcademicAchievementsManagement view rendered without Actions column.\n";
