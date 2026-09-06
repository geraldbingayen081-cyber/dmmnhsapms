<?php

namespace App\Livewire\Parent;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Livewire\Component;

class ParentDashboard extends Component
{
    public $selectedStudentId = null;
    public $filterQuarter = 'all'; // 'all', or quarter_id

    public function mount()
    {
        $parent = auth()->user()->parent;
        if ($parent) {
            $firstChild = $parent->students()->first();
            if ($firstChild) {
                $this->selectedStudentId = $firstChild->id;
            }
        }
    }

    public function selectChild($studentId)
    {
        $this->selectedStudentId = (int)$studentId;
    }

    public function render()
    {
        $user = auth()->user();
        $parent = $user->parent;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $children = collect();
        $selectedStudent = null;
        $activeEnrollment = null;
        $quarters = collect();
        $currentQuarter = null;
        $subjectRows = [];
        $earnedAwards = [];
        $teachersDirectory = [];

        $kpi = [
            'gwa' => null,
            'runningGwa' => null,
            'isCompleteGwa' => false,
            'passedCount' => 0,
            'totalSubjects' => 0,
            'bestSubjectCount' => 0,
            'lowestGrade' => null,
            'highestGrade' => null,
        ];

        $progressionChart = [
            'categories' => [],
            'childAverages' => [],
            'sectionAverages' => [],
            'hasData' => false,
        ];

        $subjectMasteryChart = [
            'subjects' => [],
            'grades' => [],
            'hasData' => false,
        ];

        if ($parent && $activeSy) {
            // Strictly 1st Quarter to 3rd Quarter as instructed
            $quarters = Quarter::where('school_year_id', $activeSy->id)
                ->whereIn('sort_order', [1, 2, 3])
                ->orderBy('sort_order')
                ->get();

            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);

            // Fetch all children linked to this parent
            $children = $parent->students()
                ->with(['enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section'])
                ->get();

            if ($children->isNotEmpty()) {
                if (!$this->selectedStudentId || !$children->contains('id', (int)$this->selectedStudentId)) {
                    $this->selectedStudentId = $children->first()->id;
                }

                $selectedStudent = $children->firstWhere('id', (int)$this->selectedStudentId);

                if ($selectedStudent) {
                    // Active enrollment for current school year
                    $activeEnrollment = StudentEnrollment::where('student_id', $selectedStudent->id)
                        ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                        ->with(['schoolYearSection.yearLevel', 'schoolYearSection.section', 'schoolYearSection.adviser.user'])
                        ->first();

                    if ($activeEnrollment) {
                        $sectionId = $activeEnrollment->school_year_section_id;

                        // Section subjects
                        $sectionSubjects = SectionSubject::where('school_year_section_id', $sectionId)
                            ->with(['subject', 'teacher.user'])
                            ->get()
                            ->sortBy(fn($ss) => $ss->subject?->subject_name)
                            ->values();

                        // Child's grades
                        $childGrades = Grade::where('student_enrollment_id', $activeEnrollment->id)
                            ->whereIn('quarter_id', $quarters->pluck('id'))
                            ->get();

                        // Section grades for comparisons
                        $allSectionGrades = Grade::whereHas('studentEnrollment', fn($q) => $q->where('school_year_section_id', $sectionId))
                            ->whereIn('quarter_id', $quarters->pluck('id'))
                            ->get();

                        // Section max grades per subject
                        $sectionMaxGrades = [];
                        foreach ($sectionSubjects as $ss) {
                            $maxG = $allSectionGrades->where('section_subject_id', $ss->id)->max('grade');
                            $sectionMaxGrades[$ss->id] = $maxG ? (float)$maxG : 0;

                            // Build teacher directory entry
                            if ($ss->teacher) {
                                $teachersDirectory[$ss->id] = [
                                    'name' => $ss->teacher->full_name,
                                    'subject' => $ss->subject?->subject_name ?? 'Subject',
                                    'contact' => $ss->teacher->contact_number ?? 'Inquire via administration',
                                    'email' => $ss->teacher->user?->email ?? 'N/A',
                                ];
                            }
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
                                $gradeRecord = $childGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
                                $val = $gradeRecord ? (float)$gradeRecord->grade : null;
                                $qMarks[$q->id] = $val;
                                if ($val !== null) {
                                    $qValidList[] = $val;
                                    $allValidGrades[] = $val;
                                }
                            }

                            // Final Grade strictly requires complete grades from 1st to 3rd quarter
                            $isCompleteQ1toQ3 = (count($qValidList) === $quarters->count()) && $quarters->count() === 3;
                            $finalAvg = $isCompleteQ1toQ3 ? round(array_sum($qValidList) / count($qValidList), 2) : null;
                            if ($finalAvg !== null) {
                                $subjectAverages[] = $finalAvg;
                            }

                            // Best in subject
                            $isBestInSubject = false;
                            $topScore = $finalAvg ?? (!empty($qValidList) ? max($qValidList) : 0);
                            $sectionTop = $sectionMaxGrades[$ss->id] ?? 0;

                            if ($topScore >= 95.00 || ($topScore >= 90.00 && $topScore >= $sectionTop && $sectionTop > 0)) {
                                $isBestInSubject = true;
                            }

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

                        // Compute KPI Metrics
                        $isAllComplete = count($subjectAverages) === count($sectionSubjects) && count($sectionSubjects) > 0;
                        $runningAverage = count($allValidGrades) > 0 ? round(array_sum($allValidGrades) / count($allValidGrades), 2) : null;
                        $finalGwa = $isAllComplete ? round(array_sum($subjectAverages) / count($subjectAverages), 2) : null;

                        $kpi['gwa'] = $finalGwa;
                        $kpi['runningGwa'] = $runningAverage;
                        $kpi['isCompleteGwa'] = $isAllComplete;
                        $kpi['totalSubjects'] = count($sectionSubjects);
                        $kpi['passedCount'] = count(array_filter($subjectAverages, fn($v) => $v >= 75.00));
                        $kpi['lowestGrade'] = count($allValidGrades) > 0 ? min($allValidGrades) : null;
                        $kpi['highestGrade'] = count($allValidGrades) > 0 ? max($allValidGrades) : null;

                        // Awards calculation per quarter
                        foreach ($quarters as $q) {
                            $qGrades = [];
                            foreach ($sectionSubjects as $ss) {
                                $g = $childGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
                                if ($g && $g->grade !== null) {
                                    $qGrades[$ss->id] = (float)$g->grade;
                                }
                            }

                            if (!empty($qGrades)) {
                                $qAvg = round(array_sum($qGrades) / count($qGrades), 2);
                                $minG = min($qGrades);

                                $qHonor = null;
                                if ($qAvg >= 98.00 && $minG >= 85.00) {
                                    $qHonor = 'With Highest Honors';
                                } elseif ($qAvg >= 95.00 && $minG >= 85.00) {
                                    $qHonor = 'With High Honors';
                                } elseif ($qAvg >= 90.00 && $minG >= 85.00) {
                                    $qHonor = 'With Honors';
                                }

                                if ($qHonor) {
                                    $earnedAwards[] = [
                                        'type' => 'honor',
                                        'title' => $qHonor,
                                        'quarter' => $q->name,
                                        'quarter_short' => $q->short_name,
                                        'grade' => $qAvg,
                                        'description' => "Official DepEd Academic Excellence Honor for achieving a general average of {$qAvg} in the {$q->name}.",
                                    ];
                                }

                                foreach ($sectionSubjects as $ss) {
                                    if (isset($qGrades[$ss->id])) {
                                        $subMark = $qGrades[$ss->id];
                                        $subName = $ss->subject?->subject_name ?? 'Subject';
                                        $secTop = $allSectionGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->max('grade');
                                        $secTopVal = $secTop ? (float)$secTop : 0;

                                        if ($subMark >= 95.00 || ($subMark >= 90.00 && $subMark >= $secTopVal && $secTopVal > 0)) {
                                            $earnedAwards[] = [
                                                'type' => 'subject',
                                                'title' => "Best in {$subName}",
                                                'subject' => $subName,
                                                'quarter' => $q->name,
                                                'quarter_short' => $q->short_name,
                                                'grade' => $subMark,
                                                'description' => "Recognized for top academic performance in {$subName} in the {$q->name}.",
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $kpi['bestSubjectCount'] = count(array_filter($earnedAwards, fn($a) => $a['type'] === 'subject'));

                        // Progression Chart Data
                        $chartCats = [];
                        $childQuarterAverages = [];
                        $sectionQuarterAverages = [];

                        foreach ($quarters as $q) {
                            $chartCats[] = $q->short_name . ' (' . $q->name . ')';

                            $cqGrades = $childGrades->where('quarter_id', $q->id)->pluck('grade');
                            $childQuarterAverages[] = $cqGrades->isNotEmpty() ? round($cqGrades->avg(), 2) : null;

                            $secQGrades = $allSectionGrades->where('quarter_id', $q->id)->pluck('grade');
                            $sectionQuarterAverages[] = $secQGrades->isNotEmpty() ? round($secQGrades->avg(), 2) : null;
                        }

                        $progressionChart = [
                            'categories' => $chartCats,
                            'childAverages' => $childQuarterAverages,
                            'sectionAverages' => $sectionQuarterAverages,
                            'hasData' => collect($childQuarterAverages)->filter()->isNotEmpty(),
                        ];

                        // Subject Mastery Chart Data
                        $chartSubjectNames = [];
                        $chartSubjectMarks = [];

                        foreach ($subjectRows as $row) {
                            $chartSubjectNames[] = $row['name'];
                            if ($this->filterQuarter !== 'all') {
                                $mark = $row['q_marks'][(int)$this->filterQuarter] ?? null;
                            } else {
                                $mark = $row['final_avg'] ?? (count(array_filter($row['q_marks'])) > 0 ? round(array_sum(array_filter($row['q_marks'])) / count(array_filter($row['q_marks'])), 2) : null);
                            }
                            $chartSubjectMarks[] = $mark ? (float)$mark : 0;
                        }

                        $subjectMasteryChart = [
                            'subjects' => $chartSubjectNames,
                            'grades' => $chartSubjectMarks,
                            'hasData' => collect($chartSubjectMarks)->filter(fn($v) => $v > 0)->isNotEmpty(),
                        ];

                        // Official registered achievements
                        $dbAchievements = $selectedStudent->achievements;
                        foreach ($dbAchievements as $dba) {
                            $dbaQuarter = !empty($dba->quarter) ? $dba->quarter : '1st Quarter';
                            $matchingQ = $quarters->first(fn($q) => stripos($q->name, $dbaQuarter) !== false || stripos($q->short_name, $dbaQuarter) !== false);

                            $exists = collect($earnedAwards)->contains(function ($item) use ($dba, $dbaQuarter) {
                                return $item['title'] === $dba->title && $item['quarter'] === $dbaQuarter;
                            });

                            if (!$exists) {
                                $earnedAwards[] = [
                                    'type' => 'citation',
                                    'title' => $dba->title,
                                    'quarter' => $dbaQuarter,
                                    'quarter_short' => $matchingQ ? $matchingQ->short_name : 'Award',
                                    'description' => $dba->description ?? "Official Academic Citation recorded in school registry for {$dbaQuarter}.",
                                    'date' => $dba->date_awarded,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return view('livewire.parent.parent-dashboard', [
            'parent' => $parent,
            'children' => $children,
            'selectedStudent' => $selectedStudent,
            'activeSy' => $activeSy,
            'currentQuarter' => $currentQuarter,
            'quarters' => $quarters,
            'activeEnrollment' => $activeEnrollment,
            'kpi' => $kpi,
            'subjectRows' => $subjectRows,
            'earnedAwards' => $earnedAwards,
            'progressionChart' => $progressionChart,
            'subjectMasteryChart' => $subjectMasteryChart,
            'teachersDirectory' => $teachersDirectory,
        ])->layout('layouts.parent', [
            'title' => 'Parent & Guardian Portal',
            'subtitle' => 'Monitor your children’s quarterly performance, attendance, and achievements',
        ]);
    }
}
