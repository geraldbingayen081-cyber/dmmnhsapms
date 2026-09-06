<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\AcademicAchievementsManagement();
$comp->mount();
$viewData = $comp->render()->getData();

echo "Total Candidates evaluated: " . count($viewData['candidates']) . "\n";
foreach (array_slice($viewData['candidates'], 0, 5) as $c) {
    echo "Student: {$c['student_name']} ({$c['student_number']}) - Average: {$c['average']}\n";
    echo "   Achievements: " . implode(', ', $c['achievements']) . "\n";
}
