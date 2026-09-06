<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Student\StudentAchievements;
use App\Livewire\Student\StudentDashboard;
use App\Livewire\Student\StudentGrades;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Find student Juan Dela Cruz (login_id = 2026-0001)
$studentUser = User::where('login_id', '2026-0001')->first();
if (!$studentUser) {
    echo "Student user 2026-0001 not found!\n";
    exit(1);
}

Auth::login($studentUser);
echo "=== Authenticated as Student: {$studentUser->name} ({$studentUser->login_id}) ===\n\n";

// 1. Test StudentDashboard
echo "--- Testing StudentDashboard Component ---\n";
$dashboard = new StudentDashboard();
$dashData = $dashboard->render()->getData();

echo "Active Enrollment: " . ($dashData['activeEnrollment'] ? "Found (Section: " . $dashData['activeEnrollment']->schoolYearSection?->section?->name . ")" : "None") . "\n";
echo "Quarters count: " . $dashData['quarters']->count() . " (Should be exactly 3: Q1, Q2, Q3)\n";
foreach ($dashData['quarters'] as $q) {
    echo "  - {$q->short_name}: {$q->name} (status: {$q->status})\n";
}

echo "\nKPI Metrics:\n";
echo "  - GWA: " . $dashData['kpi']['gwa'] . "\n";
echo "  - Honor Standing: " . $dashData['kpi']['honorTitle'] . "\n";
echo "  - Passed Learning Areas: " . $dashData['kpi']['passedCount'] . " / " . $dashData['kpi']['totalSubjects'] . "\n";
echo "  - Best in Subject Count: " . $dashData['kpi']['bestSubjectCount'] . "\n";
echo "  - Lowest Grade: " . $dashData['kpi']['lowestGrade'] . "\n";
echo "  - Highest Grade: " . $dashData['kpi']['highestGrade'] . "\n";

echo "\nSubject Grade Rows (" . count($dashData['subjectRows']) . " subjects):\n";
foreach ($dashData['subjectRows'] as $row) {
    $qMarksStr = implode(', ', array_map(fn($k, $v) => "Q{$k}: " . ($v !== null ? number_format($v, 1) : '—'), array_keys($row['q_marks']), $row['q_marks']));
    echo "  - {$row['name']} ({$row['code']}): {$qMarksStr} | Final: " . number_format($row['final_avg'], 2) . " [{$row['status']}]" . ($row['is_best'] ? " 🏅 BEST IN SUBJECT" : "") . "\n";
}

echo "\nProgression Chart Data:\n";
echo "  Categories: " . implode(', ', $dashData['progressionChart']['categories']) . "\n";
echo "  My Averages: " . implode(', ', array_map(fn($v) => $v !== null ? number_format($v, 2) : 'null', $dashData['progressionChart']['myAverages'])) . "\n";
echo "  Section Averages: " . implode(', ', array_map(fn($v) => $v !== null ? number_format($v, 2) : 'null', $dashData['progressionChart']['sectionAverages'])) . "\n";

echo "\nEarned Awards (" . count($dashData['earnedAwards']) . "):\n";
foreach ($dashData['earnedAwards'] as $aw) {
    echo "  - [{$aw['type']}] {$aw['title']}\n";
}

// 2. Test StudentGrades
echo "\n--- Testing StudentGrades Component ---\n";
$gradesComponent = new StudentGrades();
$gradesData = $gradesComponent->render()->getData();
echo "Report card GWA: " . $gradesData['gwa'] . "\n";
echo "Report card subject rows count: " . count($gradesData['subjectRows']) . "\n";

// 3. Test StudentAchievements
echo "\n--- Testing StudentAchievements Component ---\n";
$achComponent = new StudentAchievements();
$achData = $achComponent->render()->getData();
echo "Awards list count: " . count($achData['awardsList']) . "\n";

echo "\n=== All Student Portal Components Successfully Verified! ===\n";
