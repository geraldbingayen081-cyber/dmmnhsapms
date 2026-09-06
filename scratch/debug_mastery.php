<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\Auth;

$user = User::where('login_id', 'EMP-0001')->first();
Auth::login($user);

$teacher  = $user->teacher;
$activeSy = AcademicContextService::getActiveSchoolYear();

$allTeachingLoads = SectionSubject::where('teacher_id', $teacher->id)
    ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
    ->get();

$targetLoadIds = $allTeachingLoads->pluck('id');
$targetSysIds  = $allTeachingLoads->pluck('school_year_section_id')->unique();

echo "Teacher: {$teacher->full_name}\n";
echo "Teaching Loads: " . $targetLoadIds->count() . "\n";
echo "Unique Sections: " . $targetSysIds->count() . "\n\n";

$uniqueEnrollmentIds = StudentEnrollment::whereIn('school_year_section_id', $targetSysIds)->pluck('id');
echo "Total Unique Students (enrollments): " . $uniqueEnrollmentIds->count() . "\n\n";

// OLD (BUGGY): iterating raw grade records
$rawGrades = Grade::whereIn('section_subject_id', $targetLoadIds)->whereNotNull('grade')->pluck('grade');
echo "=== OLD (BUGGY): " . $rawGrades->count() . " raw grade records (counts students multiple times) ===\n";

$oldBands = ['Did Not Meet (<75)' => 0, 'Fairly Satisfactory (75-79)' => 0, 'Satisfactory (80-84)' => 0, 'Very Satisfactory (85-89)' => 0, 'Outstanding (90-100)' => 0];
foreach ($rawGrades as $g) {
    $val = (float)$g;
    if ($val < 75)      $oldBands['Did Not Meet (<75)']++;
    elseif ($val < 80)  $oldBands['Fairly Satisfactory (75-79)']++;
    elseif ($val < 85)  $oldBands['Satisfactory (80-84)']++;
    elseif ($val < 90)  $oldBands['Very Satisfactory (85-89)']++;
    else                $oldBands['Outstanding (90-100)']++;
}
foreach ($oldBands as $k => $v) echo "  {$k}: {$v}\n";
echo "  SUM: " . array_sum($oldBands) . " (should equal raw records, NOT unique students)\n\n";

// NEW (CORRECT): group by enrollment, compute average per student, then classify once
echo "=== NEW (CORRECT): Per-student average then classify once ===\n";

// All quarters (cumulative)
$studentAvgsCumulative = Grade::whereIn('section_subject_id', $targetLoadIds)
    ->whereIn('student_enrollment_id', $uniqueEnrollmentIds)
    ->whereNotNull('grade')
    ->selectRaw('student_enrollment_id, AVG(grade) as avg_grade')
    ->groupBy('student_enrollment_id')
    ->pluck('avg_grade', 'student_enrollment_id');

echo "Students with grades (all quarters): " . $studentAvgsCumulative->count() . "\n";

$newBands = ['Did Not Meet (<75)' => 0, 'Fairly Satisfactory (75-79)' => 0, 'Satisfactory (80-84)' => 0, 'Very Satisfactory (85-89)' => 0, 'Outstanding (90-100)' => 0];
foreach ($studentAvgsCumulative as $eid => $avg) {
    $val = (float)$avg;
    if ($val < 75)      $newBands['Did Not Meet (<75)']++;
    elseif ($val < 80)  $newBands['Fairly Satisfactory (75-79)']++;
    elseif ($val < 85)  $newBands['Satisfactory (80-84)']++;
    elseif ($val < 90)  $newBands['Very Satisfactory (85-89)']++;
    else                $newBands['Outstanding (90-100)']++;
}
foreach ($newBands as $k => $v) echo "  {$k}: {$v}\n";
echo "  SUM: " . array_sum($newBands) . " (should equal unique students with grades)\n\n";

// Test with a specific quarter (Q1)
$q1 = Quarter::where('school_year_id', $activeSy->id)->where('sort_order', 1)->first();
echo "=== Per-student for Q1 only (id={$q1->id}) ===\n";
$studentAvgsQ1 = Grade::whereIn('section_subject_id', $targetLoadIds)
    ->whereIn('student_enrollment_id', $uniqueEnrollmentIds)
    ->where('quarter_id', $q1->id)
    ->whereNotNull('grade')
    ->selectRaw('student_enrollment_id, AVG(grade) as avg_grade')
    ->groupBy('student_enrollment_id')
    ->pluck('avg_grade', 'student_enrollment_id');

echo "Students with Q1 grades: " . $studentAvgsQ1->count() . "\n";
$q1Bands = ['Did Not Meet (<75)' => 0, 'Fairly Satisfactory (75-79)' => 0, 'Satisfactory (80-84)' => 0, 'Very Satisfactory (85-89)' => 0, 'Outstanding (90-100)' => 0];
foreach ($studentAvgsQ1 as $eid => $avg) {
    $val = (float)$avg;
    if ($val < 75)      $q1Bands['Did Not Meet (<75)']++;
    elseif ($val < 80)  $q1Bands['Fairly Satisfactory (75-79)']++;
    elseif ($val < 85)  $q1Bands['Satisfactory (80-84)']++;
    elseif ($val < 90)  $q1Bands['Very Satisfactory (85-89)']++;
    else                $q1Bands['Outstanding (90-100)']++;
}
foreach ($q1Bands as $k => $v) echo "  {$k}: {$v}\n";
echo "  SUM: " . array_sum($q1Bands) . "\n";
