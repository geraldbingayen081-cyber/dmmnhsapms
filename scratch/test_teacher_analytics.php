<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SectionSubject;
use App\Models\Teacher;
use App\Models\User;

$user = User::where('login_id', 'EMP-0001')->first();
$teacher = $user->teacher;
$sy = SchoolYear::where('status', 'active')->first();
$quarters = Quarter::where('school_year_id', $sy->id)->orderBy('sort_order')->get();

$teachingLoads = SectionSubject::where('teacher_id', $teacher->id)
    ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sy->id))
    ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject'])
    ->get();

echo "Teaching loads count: {$teachingLoads->count()}\n";

// Line Chart 1: Progression across quarters per section
$series = [];
$quarterLabels = $quarters->pluck('short_name')->toArray();

foreach ($teachingLoads->take(5) as $load) {
    $secName = $load->schoolYearSection?->section?->name;
    $ylName = $load->schoolYearSection?->yearLevel?->name;
    $name = "{$ylName} - {$secName}";
    $data = [];

    foreach ($quarters as $q) {
        $avg = Grade::where('section_subject_id', $load->id)
            ->where('quarter_id', $q->id)
            ->avg('grade');
        $data[] = $avg ? round((float)$avg, 2) : null;
    }

    $series[] = [
        'name' => $name,
        'data' => $data,
    ];
}

echo "Line Chart Series:\n";
print_r($series);

// Line Chart 2: Grade distribution across DepEd bands
$gradesQuery = Grade::whereIn('section_subject_id', $teachingLoads->pluck('id'));
$allGrades = $gradesQuery->pluck('grade');

$bands = [
    'Did Not Meet (<75)' => 0,
    'Fairly Satisfactory (75-79)' => 0,
    'Satisfactory (80-84)' => 0,
    'Very Satisfactory (85-89)' => 0,
    'Outstanding (90-100)' => 0,
];

foreach ($allGrades as $g) {
    if ($g < 75) $bands['Did Not Meet (<75)']++;
    elseif ($g < 80) $bands['Fairly Satisfactory (75-79)']++;
    elseif ($g < 85) $bands['Satisfactory (80-84)']++;
    elseif ($g < 90) $bands['Very Satisfactory (85-89)']++;
    else $bands['Outstanding (90-100)']++;
}

echo "Grade Distribution Bands:\n";
print_r($bands);
