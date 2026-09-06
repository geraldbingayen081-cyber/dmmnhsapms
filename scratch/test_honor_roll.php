<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\AcademicAchievementsManagement();
$comp->mount();
$viewData = $comp->render()->getData();

echo "Honor roll candidates count: " . count($viewData['candidates']) . "\n";
foreach ($viewData['candidates'] as $c) {
    echo "Candidate: {$c['student_name']} ({$c['student_number']}) - Average: {$c['average']} - Title: {$c['honor_title']}\n";
}
