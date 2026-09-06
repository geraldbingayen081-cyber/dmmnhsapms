<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Teacher\AdvisoryManagement;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('login_id', 'EMP-0001')->first();
Auth::login($user);

echo "=== Testing AdvisoryManagement Component ===\n";

$component = new AdvisoryManagement();
$component->mount();
$viewData = $component->render()->getData();

$candidates = $viewData['candidates'];
echo "Achievers count: " . count($candidates) . "\n\n";

foreach ($candidates as $cand) {
    echo "Student: {$cand['student']->full_name} (Avg: " . number_format($cand['average'], 2) . ")\n";
    echo "  Total Awards: " . count($cand['awards']) . "\n";
    foreach ($cand['awards'] as $aw) {
        $gradeStr = isset($aw['grade']) ? " (" . number_format($aw['grade'], 2) . ")" : '';
        echo "   - [{$aw['type']}] {$aw['title']}{$gradeStr} (Awarded: " . ($aw['awarded'] ? 'YES' : 'NO') . ")\n";
    }
    echo "\n";
}

// Test awarding one subject award
if (!empty($candidates)) {
    $firstCand = $candidates[0];
    $sampleAward = collect($firstCand['awards'])->firstWhere('type', 'subject');
    if ($sampleAward) {
        echo "Testing awardHonorTitle for: {$sampleAward['title']}\n";
        $component->awardHonorTitle($firstCand['enrollment_id'], $sampleAward['title']);
        $ach = Achievement::where('student_id', $firstCand['student']->id)->where('title', $sampleAward['title'])->first();
        if ($ach) {
            echo "SUCCESS: Found award in DB: ID {$ach->id}, Title: {$ach->title}, Student ID: {$ach->student_id}\n";
        } else {
            echo "FAIL: Award not found in DB\n";
        }
    }
}

echo "=== Test Completed Cleanly ===\n";
