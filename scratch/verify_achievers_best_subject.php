<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SchoolYearSection;
use App\Models\StudentEnrollment;

$sys = SchoolYearSection::find(1);
$enrollments = StudentEnrollment::where('school_year_section_id', $sys->id)
    ->with(['student.achievements', 'grades.sectionSubject.subject'])
    ->get();

$subjectMax = [];
foreach ($enrollments as $enr) {
    foreach ($enr->grades as $g) {
        $sub = $g->sectionSubject?->subject?->subject_name;
        if ($sub && $g->grade !== null) {
            $val = (float)$g->grade;
            if (!isset($subjectMax[$sub]) || $val > $subjectMax[$sub]) {
                $subjectMax[$sub] = $val;
            }
        }
    }
}

$candidates = [];
foreach ($enrollments as $enr) {
    $qGrades = $enr->grades;
    if ($qGrades->isEmpty()) continue;

    $avg = $qGrades->avg('grade');
    $min = $qGrades->min('grade');
    $honorTitle = null;

    if ($avg >= 98.00 && $min >= 85.00) $honorTitle = 'With Highest Honors';
    elseif ($avg >= 95.00 && $min >= 85.00) $honorTitle = 'With High Honors';
    elseif ($avg >= 90.00 && $min >= 85.00) $honorTitle = 'With Honors';

    $bestSubjects = [];
    foreach ($qGrades as $g) {
        $sub = $g->sectionSubject?->subject?->subject_name;
        if ($sub && $g->grade !== null) {
            $val = (float)$g->grade;
            $isTopInSection = isset($subjectMax[$sub]) && $val >= $subjectMax[$sub] && $val >= 90.00;
            if ($val >= 95.00 || $isTopInSection) {
                $bestSubjects[$sub] = [
                    'subject_name' => $sub,
                    'award_title' => "Best in {$sub}",
                    'grade' => $val,
                    'is_top' => $isTopInSection,
                ];
            }
        }
    }

    $awards = [];
    if ($honorTitle) {
        $awards[] = ['type' => 'honor', 'title' => $honorTitle];
    }
    foreach ($bestSubjects as $bs) {
        $awards[] = ['type' => 'subject', 'title' => $bs['award_title'], 'grade' => $bs['grade']];
    }

    if ($honorTitle || !empty($bestSubjects)) {
        $candidates[] = [
            'student' => $enr->student?->full_name,
            'average' => round($avg, 2),
            'lowest' => round($min, 2),
            'honorTitle' => $honorTitle,
            'awards' => $awards,
        ];
    }
}

echo "Total Achievers: " . count($candidates) . "\n";
foreach ($candidates as $cand) {
    echo "\nStudent: {$cand['student']} (Avg: {$cand['average']}, Min: {$cand['lowest']})\n";
    echo "Honor: {$cand['honorTitle']}\n";
    echo "Awards:\n";
    foreach ($cand['awards'] as $aw) {
        echo "  - " . ($aw['type'] === 'honor' ? '🏆 ' : '🏅 ') . "{$aw['title']}" . (isset($aw['grade']) ? " ({$aw['grade']})" : '') . "\n";
    }
}
