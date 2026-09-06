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

echo "=== Teacher: {$teacher->full_name} | SY: {$activeSy->school_year} ===\n\n";

// Replicate exactly what render() does
$myClasses = SectionSubject::where('teacher_id', $teacher->id)
    ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
    ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject'])
    ->get()
    ->sortBy(fn($l) =>
        ($l->schoolYearSection?->yearLevel?->sort_order ?? 0)
        . '-' . ($l->schoolYearSection?->section?->name ?? '')
        . '-' . ($l->subject?->subject_name ?? '')
    )
    ->values();

$quarters = Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get();

echo "Quarters loaded: " . $quarters->count() . "\n";
foreach ($quarters as $q) {
    echo "  Q: id={$q->id}, {$q->name} ({$q->short_name}), status={$q->status}\n";
}
echo "\n";

// Use Mercury Filipino (SS ID 33) — known to have Q1+Q2+Q3 grades
$selectedClassId = 33;
$selectedQuarterId = 3; // Q3 (active)

$class = $myClasses->firstWhere('id', $selectedClassId);
echo "Selected class found: " . ($class ? "YES - {$class->subject?->subject_name} for {$class->schoolYearSection?->section?->name}" : "NO") . "\n";
echo "school_year_section_id: " . ($class?->school_year_section_id) . "\n\n";

$studentsList = StudentEnrollment::where('school_year_section_id', $class->school_year_section_id)
    ->with(['student'])
    ->get()
    ->sortBy(fn($e) => $e->student?->last_name)
    ->values();

echo "Students loaded: " . $studentsList->count() . "\n";
foreach ($studentsList as $enr) {
    echo "  Enrollment ID={$enr->id}, Student={$enr->student?->last_name}, {$enr->student?->first_name}\n";
}
echo "\n";

// Load all grades
$allGrades = Grade::where('section_subject_id', $selectedClassId)
    ->whereIn('student_enrollment_id', $studentsList->pluck('id'))
    ->get();

echo "All grades loaded (across all quarters): " . $allGrades->count() . "\n";
foreach ($allGrades as $g) {
    echo "  Grade ID={$g->id}, EnrID={$g->student_enrollment_id}, Q={$g->quarter_id}, Grade={$g->grade}\n";
}
echo "\n";

// Simulate gradeMatrix build using collection->where()  (current code)
echo "=== gradeMatrix via collection->where() (CURRENT CODE) ===\n";
foreach ($studentsList as $enr) {
    foreach ($quarters as $q) {
        $record = $allGrades
            ->where('student_enrollment_id', $enr->id)
            ->where('quarter_id', $q->id)
            ->first();
        echo "  Enr{$enr->id} Q{$q->id}: " . ($record ? $record->grade : 'NULL') . "\n";
    }
}

echo "\n=== gradeMatrix via pre-indexed array (PROPOSED FIX) ===\n";
$idx = [];
foreach ($allGrades as $g) {
    $idx[$g->student_enrollment_id][$g->quarter_id] = $g;
}
foreach ($studentsList as $enr) {
    foreach ($quarters as $q) {
        $record = $idx[$enr->id][$q->id] ?? null;
        echo "  Enr{$enr->id} Q{$q->id}: " . ($record ? $record->grade : 'NULL') . "\n";
    }
}
