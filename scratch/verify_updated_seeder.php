<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Models\YearLevel;

echo "=== Verification of Updated TestDataSeeder ===\n\n";

// 1. Basic entity counts
echo "Total Teachers:  " . Teacher::count() . " (expected: 32)\n";
echo "Total Students:  " . Student::count() . " (expected: 480)\n";
echo "Total Parents:   " . ParentModel::count() . "\n";
echo "Total Grades:    " . Grade::count() . " (expected: 11520 = 480×8×3)\n";

// 2. Parent-Child counts
$parents = ParentModel::with('students')->get();
$childCounts = $parents->map(fn($p) => $p->students->count());
$withOne = $childCounts->filter(fn($c) => $c === 1)->count();
$withTwo = $childCounts->filter(fn($c) => $c === 2)->count();
$withOther = $childCounts->filter(fn($c) => $c !== 1 && $c !== 2)->count();

echo "\n--- Parent-Child Linking Checks ---\n";
echo "Parents with 1 child: {$withOne}\n";
echo "Parents with 2 children: {$withTwo}\n";
echo "Parents with other counts (>2 or 0): {$withOther} (expected: 0)\n";
echo "Min children per parent: " . $childCounts->min() . " (expected: 1)\n";
echo "Max children per parent: " . $childCounts->max() . " (expected: 2)\n";

$studentsWithParents = Student::has('parents')->count();
echo "Students linked to a parent: {$studentsWithParents} / " . Student::count() . " (expected: 480)\n";

// 3. Grade range and distinctness
echo "\n--- Grade Ratings & Distinctness Checks ---\n";
$maxGrade = Grade::max('grade');
$minGrade = Grade::min('grade');
$exceeding99 = Grade::where('grade', '>', 99)->count();
echo "Max grade in database: {$maxGrade} (must be <= 99)\n";
echo "Min grade in database: {$minGrade}\n";
echo "Grades exceeding 99: {$exceeding99} (expected: 0)\n";

// Check that quarter grades per student-subject are not identical
$identicalQuarterCount = 0;
$totalStudentSubjectsChecked = 0;

$enrollments = StudentEnrollment::with(['grades'])->get();
foreach ($enrollments as $enr) {
    $bySubject = $enr->grades->groupBy('section_subject_id');
    foreach ($bySubject as $subGrades) {
        $totalStudentSubjectsChecked++;
        $marks = $subGrades->pluck('grade')->all();
        if (count($marks) === 3 && $marks[0] == $marks[1] && $marks[1] == $marks[2]) {
            $identicalQuarterCount++;
        }
    }
}
echo "Checked student-subject grade sets: {$totalStudentSubjectsChecked}\n";
echo "Grade sets where Q1 == Q2 == Q3: {$identicalQuarterCount} (expected: 0)\n";

// 4. Year Level Averages and Achievers count
echo "\n--- Year Level Averages & Achievers Comparison ---\n";
$yearLevels = YearLevel::orderBy('sort_order')->get();
$ylStats = [];

foreach ($yearLevels as $yl) {
    // Find all sections for this year level
    $sysList = SchoolYearSection::where('year_level_id', $yl->id)->pluck('id');
    
    // Enrollments
    $ylEnrollments = StudentEnrollment::whereIn('school_year_section_id', $sysList)->with(['grades.sectionSubject.subject'])->get();
    
    $ylAllGrades = [];
    $ylAchievers = 0;

    foreach ($ylEnrollments as $enr) {
        $subAvgs = [];
        $bySub = $enr->grades->groupBy('section_subject_id');
        foreach ($bySub as $sg) {
            $avg = $sg->avg('grade');
            $subAvgs[] = $avg;
            foreach ($sg as $g) {
                $ylAllGrades[] = (float)$g->grade;
            }
        }
        $gwa = count($subAvgs) > 0 ? array_sum($subAvgs) / count($subAvgs) : 0;
        $minSub = count($subAvgs) > 0 ? min($subAvgs) : 0;

        // DepEd honor: GWA >= 90.00 and no grade < 85.00
        if ($gwa >= 90.00 && $minSub >= 85.00) {
            $ylAchievers++;
        }
    }

    $ylAvg = count($ylAllGrades) > 0 ? array_sum($ylAllGrades) / count($ylAllGrades) : 0;
    
    $ylStats[$yl->code] = [
        'name' => $yl->name,
        'students' => $ylEnrollments->count(),
        'average' => round($ylAvg, 2),
        'achievers' => $ylAchievers,
    ];

    echo "{$yl->name} ({$yl->code}): Students = {$ylEnrollments->count()}, Year-Level Avg = " . number_format($ylAvg, 2) . "%, Achievers = {$ylAchievers}\n";
}

// Check uniqueness of averages and achiever counts
$averages = array_column($ylStats, 'average');
$achieverCounts = array_column($ylStats, 'achievers');

echo "\nUnique Year-Level Averages: " . count(array_unique($averages)) . " / 4\n";
echo "Unique Achiever Counts:     " . count(array_unique($achieverCounts)) . " / 4\n";

echo "\n=== Verification Complete ===\n";
