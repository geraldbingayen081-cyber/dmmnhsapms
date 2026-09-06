<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\AcademicContextService;

$studentUser = User::where('login_id', '2026-0001')->first();
$student = $studentUser->student;
$activeSy = AcademicContextService::getActiveSchoolYear();

$quarters = Quarter::where('school_year_id', $activeSy->id)
    ->whereIn('sort_order', [1, 2, 3])
    ->orderBy('sort_order')
    ->get();

$activeEnrollment = StudentEnrollment::where('student_id', $student->id)
    ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
    ->first();

$sectionId = $activeEnrollment->school_year_section_id;

$sectionSubjects = SectionSubject::where('school_year_section_id', $sectionId)
    ->with('subject')
    ->get();

$myGrades = Grade::where('student_enrollment_id', $activeEnrollment->id)
    ->whereIn('quarter_id', $quarters->pluck('id'))
    ->get();

$allSectionGrades = Grade::whereHas('studentEnrollment', fn($q) => $q->where('school_year_section_id', $sectionId))
    ->whereIn('quarter_id', $quarters->pluck('id'))
    ->get();

$awards = [];

// Calculate per quarter (Q1, Q2, Q3)
foreach ($quarters as $q) {
    $qGrades = [];
    foreach ($sectionSubjects as $ss) {
        $g = $myGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
        if ($g && $g->grade !== null) {
            $qGrades[$ss->id] = (float)$g->grade;
        }
    }

    if (!empty($qGrades)) {
        $qAvg = round(array_sum($qGrades) / count($qGrades), 2);
        $minG = min($qGrades);

        // General Honor for Quarter
        $qHonor = null;
        if ($qAvg >= 98.00 && $minG >= 85.00) {
            $qHonor = 'With Highest Honors';
        } elseif ($qAvg >= 95.00 && $minG >= 85.00) {
            $qHonor = 'With High Honors';
        } elseif ($qAvg >= 90.00 && $minG >= 85.00) {
            $qHonor = 'With Honors';
        }

        if ($qHonor) {
            $awards[] = [
                'type' => 'honor',
                'title' => $qHonor,
                'quarter' => $q->name,
                'quarter_short' => $q->short_name,
                'quarter_id' => $q->id,
                'grade' => $qAvg,
                'description' => "Official DepEd Academic Excellence Honor for achieving a general average of {$qAvg} in the {$q->name}.",
            ];
        }

        // Best in Subject for Quarter
        foreach ($sectionSubjects as $ss) {
            if (isset($qGrades[$ss->id])) {
                $subMark = $qGrades[$ss->id];
                $subName = $ss->subject?->subject_name ?? 'Subject';

                // Section top grade for this subject in this quarter
                $secTop = $allSectionGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->max('grade');
                $secTopVal = $secTop ? (float)$secTop : 0;

                if ($subMark >= 95.00 || ($subMark >= 90.00 && $subMark >= $secTopVal && $secTopVal > 0)) {
                    $awards[] = [
                        'type' => 'subject',
                        'title' => "Best in {$subName}",
                        'subject' => $subName,
                        'quarter' => $q->name,
                        'quarter_short' => $q->short_name,
                        'quarter_id' => $q->id,
                        'grade' => $subMark,
                        'description' => "Recognized for academic excellence and top performance in {$subName} in the {$q->name}.",
                    ];
                }
            }
        }
    }
}

echo "=== Generated Awards with Quarter Details (" . count($awards) . ") ===\n";
foreach ($awards as $a) {
    echo "• [{$a['quarter_short']} - {$a['quarter']}] {$a['title']} | Grade: {$a['grade']}\n";
    echo "  Desc: {$a['description']}\n";
}
