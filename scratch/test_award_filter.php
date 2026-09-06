<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comp = new \App\Livewire\Admin\AcademicAchievementsManagement();
$comp->mount();

$comp->filterHonorCategory = 'best_subject';
$viewData = $comp->render()->getData();

echo "Best in Subject filter candidates count: " . count($viewData['candidates']) . "\n";
foreach (array_slice($viewData['candidates'], 0, 3) as $c) {
    echo "Student: {$c['student_name']} - Best in Subjects: " . implode(', ', $c['best_subjects']) . "\n";
}
