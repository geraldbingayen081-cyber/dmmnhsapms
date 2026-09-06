<?php

namespace App\Livewire\Student;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Livewire\Component;

class StudentDashboard extends Component
{
    public $filterQuarter = 'all'; // 'all', or quarter_id

    public function render()
    {
        $user = auth()->user();
        $student = $user->student;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $activeEnrollment = null;
        $quarters = collect();
        $currentQuarter = null;
        $subjectRows = [];
        $earnedAwards = [];

        $kpi = [
            'gwa' => null,
            'honorTitle' => 'Regular Standing',
            'honorStatus' => 'none',
            'passedCount' => 0,
            'totalSubjects' => 0,
            'bestSubjectCount' => 0,
            'lowestGrade' => null,
            'highestGrade' => null,
        ];

        $progressionChart = [
            'categories' => [],
            'myAverages' => [],
            'sectionAverages' => [],
            'hasData' => false,
        ];

        $subjectMasteryChart = [
            'subjects' => [],
            'grades' => [],
            'hasData' => false,
        ];

        if ($student && $activeSy) {
            // Strictly 1st Quarter to 3rd Quarter as instructed
            $quarters = Quarter::where('school_year_id', $activeSy->id)
                ->whereIn('sort_order', [1, 2, 3])
                ->orderBy('sort_order')
                ->get();

            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);

            // Active enrollment for current school year
            $activeEnrollment = StudentEnrollment::where('student_id', $student->id)
                ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                ->with(['schoolYearSection.yearLevel', 'schoolYearSection.section', 'schoolYearSection.adviser.user'])
                ->first();

            if ($activeEnrollment) {
                $sectionId = $activeEnrollment->school_year_section_id;

                // Load all subjects assigned to this section
                $sectionSubjects = SectionSubject::where('school_year_section_id', $sectionId)
                    ->with(['subject', 'teacher.user'])
                    ->get()
                    ->sortBy(fn($ss) => $ss->subject?->subject_name)
                    ->values();

                // Load this student's grades across Q1 to Q3
                $myGrades = Grade::where('student_enrollment_id', $activeEnrollment->id)
                    ->whereIn('quarter_id', $quarters->pluck('id'))
                    ->get();

                // Compute section max and section averages per subject to identify "Best in Subject" and peer comparisons
                $allSectionGrades = Grade::whereHas('studentEnrollment', fn($q) => $q->where('school_year_section_id', $sectionId))
                    ->whereIn('quarter_id', $quarters->pluck('id'))
                    ->get();

                // Section max grade per subject (for Best in Subject detection)
                $sectionMaxGrades = [];
                foreach ($sectionSubjects as $ss) {
                    $maxG = $allSectionGrades->where('section_subject_id', $ss->id)->max('grade');
                    $sectionMaxGrades[$ss->id] = $maxG ? (float)$maxG : 0;
                }

                $subjectAverages = [];
                $allValidGrades = [];

                foreach ($sectionSubjects as $ss) {
                    $subName = $ss->subject?->subject_name ?? 'Subject';
                    $subCode = $ss->subject?->subject_code ?? 'SUB';
                    $teacherName = $ss->teacher?->full_name ?? 'Faculty Instructor';

                    $qMarks = [];
                    $qValidList = [];

                    foreach ($quarters as $q) {
                        $gradeRecord = $myGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
                        $val = $gradeRecord ? (float)$gradeRecord->grade : null;
                        $qMarks[$q->id] = $val;
                        if ($val !== null) {
                            $qValidList[] = $val;
                            $allValidGrades[] = $val;
                        }
                    }

                    // A valid Final Grade strictly requires complete grades from 1st to 3rd quarter
                    $isCompleteQ1toQ3 = (count($qValidList) === $quarters->count()) && $quarters->count() === 3;
                    $finalAvg = $isCompleteQ1toQ3 ? round(array_sum($qValidList) / count($qValidList), 2) : null;
                    if ($finalAvg !== null) {
                        $subjectAverages[] = $finalAvg;
                    }

                    // Check if student qualifies for "Best in [Subject]"
                    $isBestInSubject = false;
                    $topScore = $finalAvg ?? (!empty($qValidList) ? max($qValidList) : 0);
                    $sectionTop = $sectionMaxGrades[$ss->id] ?? 0;

                    if ($topScore >= 95.00 || ($topScore >= 90.00 && $topScore >= $sectionTop && $sectionTop > 0)) {
                        $isBestInSubject = true;
                        $earnedAwards[] = [
                            'type' => 'subject',
                            'title' => "Best in {$subName}",
                            'subject' => $subName,
                            'grade' => $topScore,
                        ];
                    }

                    // Status is PASSED/FAILED only when Final Grade is valid (all Q1–Q3 complete)
                    $status = 'PENDING';
                    if ($finalAvg !== null) {
                        $status = $finalAvg >= 75.00 ? 'PASSED' : 'FAILED';
                    }

                    $subjectRows[] = [
                        'id' => $ss->id,
                        'name' => $subName,
                        'code' => $subCode,
                        'teacher' => $teacherName,
                        'q_marks' => $qMarks,
                        'final_avg' => $finalAvg,
                        'is_complete' => $isCompleteQ1toQ3,
                        'status' => $status,
                        'is_best' => $isBestInSubject,
                    ];
                }

                // 2. Compute KPI Metrics
                $isAllComplete = count($subjectAverages) === count($sectionSubjects) && count($sectionSubjects) > 0;
                $runningAverage = count($allValidGrades) > 0 ? round(array_sum($allValidGrades) / count($allValidGrades), 2) : null;
                $finalGwa = $isAllComplete ? round(array_sum($subjectAverages) / count($subjectAverages), 2) : null;

                $minGrade = count($allValidGrades) > 0 ? min($allValidGrades) : 0;
                $maxGrade = count($allValidGrades) > 0 ? max($allValidGrades) : 0;

                $kpi['gwa'] = $finalGwa;
                $kpi['runningGwa'] = $runningAverage;
                $kpi['isCompleteGwa'] = $isAllComplete;
                $kpi['lowestGrade'] = $minGrade;
                $kpi['highestGrade'] = $maxGrade;
                $kpi['totalSubjects'] = count($sectionSubjects);
                $kpi['passedCount'] = count(array_filter($subjectAverages, fn($v) => $v >= 75.00));
                $kpi['bestSubjectCount'] = count($earnedAwards);

                // DepEd Honors Qualification based on valid grades
                $evalAverage = $finalGwa ?? $runningAverage;
                if ($evalAverage !== null) {
                    if ($evalAverage >= 98.00 && $minGrade >= 85.00) {
                        $kpi['honorTitle'] = 'With Highest Honors';
                        $kpi['honorStatus'] = 'highest';
                    } elseif ($evalAverage >= 95.00 && $minGrade >= 85.00) {
                        $kpi['honorTitle'] = 'With High Honors';
                        $kpi['honorStatus'] = 'high';
                    } elseif ($evalAverage >= 90.00 && $minGrade >= 85.00) {
                        $kpi['honorTitle'] = 'With Honors';
                        $kpi['honorStatus'] = 'honors';
                    } else {
                        $kpi['honorTitle'] = $evalAverage >= 75.00 ? 'Passing Standing' : 'Needs Support';
                        $kpi['honorStatus'] = $evalAverage >= 75.00 ? 'passing' : 'at_risk';
                    }

                    if (in_array($kpi['honorStatus'], ['highest', 'high', 'honors'])) {
                        array_unshift($earnedAwards, [
                            'type' => 'honor',
                            'title' => $kpi['honorTitle'],
                            'grade' => $evalAverage,
                        ]);
                    }
                }

                // 3. Analytics Chart 1: Quarterly Progression (Q1 to Q3)
                $chartCats = [];
                $myQuarterAverages = [];
                $sectionQuarterAverages = [];

                foreach ($quarters as $q) {
                    $chartCats[] = $q->short_name . ' (' . $q->name . ')';

                    // My quarter average
                    $myQGrades = $myGrades->where('quarter_id', $q->id)->pluck('grade');
                    $myQuarterAverages[] = $myQGrades->isNotEmpty() ? round($myQGrades->avg(), 2) : null;

                    // Section class average
                    $secQGrades = $allSectionGrades->where('quarter_id', $q->id)->pluck('grade');
                    $sectionQuarterAverages[] = $secQGrades->isNotEmpty() ? round($secQGrades->avg(), 2) : null;
                }

                $progressionChart = [
                    'categories' => $chartCats,
                    'myAverages' => $myQuarterAverages,
                    'sectionAverages' => $sectionQuarterAverages,
                    'hasData' => collect($myQuarterAverages)->filter()->isNotEmpty(),
                ];

                // 4. Analytics Chart 2: Subject Mastery Bar Chart
                $chartSubjectNames = [];
                $chartSubjectMarks = [];

                foreach ($subjectRows as $row) {
                    $chartSubjectNames[] = $row['name'];
                    if ($this->filterQuarter !== 'all') {
                        $mark = $row['q_marks'][(int)$this->filterQuarter] ?? null;
                    } else {
                        $mark = $row['final_avg'];
                    }
                    $chartSubjectMarks[] = $mark ? (float)$mark : 0;
                }

                $subjectMasteryChart = [
                    'subjects' => $chartSubjectNames,
                    'grades' => $chartSubjectMarks,
                    'hasData' => collect($chartSubjectMarks)->filter(fn($v) => $v > 0)->isNotEmpty(),
                ];

                // Include official registered achievements from database
                $dbAchievements = $student->achievements;
                foreach ($dbAchievements as $dba) {
                    if (!collect($earnedAwards)->contains('title', $dba->title)) {
                        $earnedAwards[] = [
                            'type' => 'citation',
                            'title' => $dba->title,
                            'date' => $dba->date_awarded,
                        ];
                    }
                }
            }
        }

        return view('livewire.student.student-dashboard', [
            'student' => $student,
            'activeSy' => $activeSy,
            'currentQuarter' => $currentQuarter,
            'quarters' => $quarters,
            'activeEnrollment' => $activeEnrollment,
            'kpi' => $kpi,
            'subjectRows' => $subjectRows,
            'earnedAwards' => $earnedAwards,
            'progressionChart' => $progressionChart,
            'subjectMasteryChart' => $subjectMasteryChart,
        ])->layout('layouts.student', [
            'title' => 'Student Dashboard & Academic Performance',
            'subtitle' => 'Personal grade monitoring, quarterly progression, and academic achievements',
        ]);
    }
}
