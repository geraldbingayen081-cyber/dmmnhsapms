<?php

// Test algorithm for TestDataSeeder updates

$ylProfiles = [
    'G7' => [
        'name' => 'Grade 7',
        // 15 slots per section:
        // 0=Highest(98-99), 1=High(95-97), 2=Honors(90-94), 3=Average(82-88), 4=Fair(76-79)
        // G7: 8 achievers per section (1 Highest, 3 High, 4 Honors, 5 Avg, 2 Fair)
        'slots' => [0, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4],
    ],
    'G8' => [
        'name' => 'Grade 8',
        // G8: 3 achievers per section (0 Highest, 1 High, 2 Honors, 7 Avg, 5 Fair)
        'slots' => [1, 2, 2, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4],
    ],
    'G9' => [
        'name' => 'Grade 9',
        // G9: 5 achievers per section (1 Highest, 2 High, 2 Honors, 7 Avg, 3 Fair)
        'slots' => [0, 1, 1, 2, 2, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4],
    ],
    'G10' => [
        'name' => 'Grade 10',
        // G10: 9 achievers per section (2 Highest, 4 High, 3 Honors, 5 Avg, 1 Fair)
        'slots' => [0, 0, 1, 1, 1, 1, 2, 2, 2, 3, 3, 3, 3, 3, 4],
    ],
];

$tierRanges = [
    0 => [98, 99], // Highest
    1 => [95, 97], // High
    2 => [90, 94], // Honors
    3 => [81, 88], // Average
    4 => [76, 79], // Fair
];

function generateDistinctGrades($min, $max) {
    // Generate distinct grades for Q1, Q2, Q3 within [$min, $max]
    // If range is narrow (e.g. 98-99, difference is 1), allow picking from [min, max] but alternating or jittering by 1
    $q1 = rand($min, $max);
    
    // Q2
    $possibleQ2 = array_values(array_filter(range(max(60, $min - 1), min(99, $max + 1)), fn($v) => $v != $q1));
    if (empty($possibleQ2)) {
        $q2 = ($q1 == 99) ? 98 : $q1 + 1;
    } else {
        $q2 = $possibleQ2[array_rand($possibleQ2)];
    }
    
    // Q3
    $possibleQ3 = array_values(array_filter(range(max(60, $min - 1), min(99, $max + 1)), fn($v) => $v != $q2));
    if (empty($possibleQ3)) {
        $q3 = ($q2 == 99) ? 98 : $q2 + 1;
    } else {
        $q3 = $possibleQ3[array_rand($possibleQ3)];
    }

    // Ensure all <= 99 and >= 60
    return [
        min(99, max(60, $q1)),
        min(99, max(60, $q2)),
        min(99, max(60, $q3)),
    ];
}

echo "=== Year Level Comparison Simulation ===\n";
foreach ($ylProfiles as $code => $prof) {
    $allGrades = [];
    $achievers = 0;
    $totalStudents = 8 * 15; // 120 students per year level

    for ($sec = 0; $sec < 8; $sec++) {
        for ($st = 0; $st < 15; $st++) {
            $tier = $prof['slots'][$st];
            [$min, $max] = $tierRanges[$tier];
            
            // 8 subjects
            $studentSubjectAvgs = [];
            for ($sub = 0; $sub < 8; $sub++) {
                $qMarks = generateDistinctGrades($min, $max);
                // Check if all 3 quarters are same
                if ($qMarks[0] == $qMarks[1] && $qMarks[1] == $qMarks[2]) {
                    echo "WARNING: All quarters identical for tier {$tier}\n";
                }
                $avg = array_sum($qMarks) / 3;
                $studentSubjectAvgs[] = $avg;
                foreach ($qMarks as $g) {
                    $allGrades[] = $g;
                }
            }
            $studentGwa = array_sum($studentSubjectAvgs) / 8;
            $minSub = min($studentSubjectAvgs);
            if ($studentGwa >= 90.00 && $minSub >= 85.00) {
                $achievers++;
            }
        }
    }

    $ylAvg = array_sum($allGrades) / count($allGrades);
    echo "{$prof['name']}: Total Students = {$totalStudents}, Achievers = {$achievers}, YL Overall Avg = " . number_format($ylAvg, 2) . "%\n";
}

echo "\n=== Parent Linking Simulation ===\n";
$totalStudents = 480;
$studentIds = range(1, 480);
$parents = [];
$pIndex = 1;
$sIndex = 0;

while ($sIndex < $totalStudents) {
    // 1 or 2 children
    $remaining = $totalStudents - $sIndex;
    $numChildren = ($remaining == 1) ? 1 : rand(1, 2);
    
    $assignedStudents = array_slice($studentIds, $sIndex, $numChildren);
    $parents[$pIndex] = $assignedStudents;
    $sIndex += $numChildren;
    $pIndex++;
}

$childCounts = array_map('count', $parents);
echo "Total Parents Created: " . count($parents) . "\n";
echo "Parents with 1 child: " . count(array_filter($childCounts, fn($c) => $c === 1)) . "\n";
echo "Parents with 2 children: " . count(array_filter($childCounts, fn($c) => $c === 2)) . "\n";
echo "Parents with other count: " . count(array_filter($childCounts, fn($c) => $c !== 1 && $c !== 2)) . "\n";
echo "Min children per parent: " . min($childCounts) . "\n";
echo "Max children per parent: " . max($childCounts) . "\n";
