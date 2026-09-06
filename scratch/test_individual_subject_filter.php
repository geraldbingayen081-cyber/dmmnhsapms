<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\AcademicAchievementsManagement();
$comp->mount();

$subjects = ['Mathematics', 'Science', 'Filipino', 'English'];

foreach ($subjects as $subName) {
    $comp->filterHonorCategory = 'best_' . $subName;
    $count = count($comp->render()->getData()['candidates']);
    echo "Filter 'Best in {$subName}' -> Count: {$count}\n";
}
