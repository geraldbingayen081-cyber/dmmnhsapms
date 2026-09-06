<?php

namespace App\Livewire\Teacher;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Livewire\Component;

class TeacherDashboard extends Component
{
    public $filterClassId = 'all';
    public $filterQuarterId = 'all';

    public function updatedFilterClassId()
    {
        // Reactively triggers re-render of analytics
    }

    public function updatedFilterQuarterId()
    {
        // Reactively triggers re-render of analytics
    }

    public function render()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $currentQuarter = $activeSy ? AcademicContextService::getCurrentQuarter($activeSy->id) : null;
        $quarters = $activeSy ? Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get() : collect();

        // 1. Advised section (if assigned as Class Adviser)
        $advisedSection = null;
        $advisoryStats = [
            'generalAverage' => null,
            'honorsCount' => 0,
            'bestSubjectsCount' => 0,
            'atRiskCount' => 0,
            'totalStudents' => 0,
        ];

        if ($teacher && $activeSy) {
            $advisedSection = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->with(['section', 'yearLevel'])
                ->first();

            if ($advisedSection) {
                $enrollments = StudentEnrollment::where('school_year_section_id', $advisedSection->id)
                    ->with(['student', 'grades.sectionSubject.subject'])
                    ->get();

                $advisoryStats['totalStudents'] = $enrollments->count();

                // Compute section max per subject for Best in Subject identification
                $sectionSubjectMax = [];
                foreach ($enrollments as $enr) {
                    foreach ($enr->grades as $g) {
                        $sub = $g->sectionSubject?->subject?->subject_name;
                        if ($sub && $g->grade !== null) {
                            $val = (float)$g->grade;
                            if (!isset($sectionSubjectMax[$sub]) || $val > $sectionSubjectMax[$sub]) {
                                $sectionSubjectMax[$sub] = $val;
                            }
                        }
                    }
                }

                $allAverages = [];
                $honors = 0;
                $atRisk = 0;
                $bestSubjectsTotal = 0;

                foreach ($enrollments as $enr) {
                    $grades = $enr->grades;
                    if ($grades->isNotEmpty()) {
                        $avg = $grades->avg('grade');
                        $min = $grades->min('grade');
                        $allAverages[] = $avg;

                        if ($avg >= 90.00 && $min >= 85.00) {
                            $honors++;
                        }
                        if ($min < 75.00) {
                            $atRisk++;
                        }

                        // Check Best in Subject
                        foreach ($grades as $g) {
                            $sub = $g->sectionSubject?->subject?->subject_name;
                            if ($sub && $g->grade !== null) {
                                $val = (float)$g->grade;
                                $isTop = isset($sectionSubjectMax[$sub]) && $val >= $sectionSubjectMax[$sub] && $val >= 90.00;
                                if ($val >= 95.00 || $isTop) {
                                    $bestSubjectsTotal++;
                                }
                            }
                        }
                    }
                }

                $advisoryStats['generalAverage'] = count($allAverages) > 0 ? round(array_sum($allAverages) / count($allAverages), 2) : null;
                $advisoryStats['honorsCount'] = $honors;
                $advisoryStats['bestSubjectsCount'] = $bestSubjectsTotal;
                $advisoryStats['atRiskCount'] = $atRisk;
            }
        }

        // 2. Subject teaching loads
        $allTeachingLoads = collect();
        if ($teacher && $activeSy) {
            $allTeachingLoads = SectionSubject::where('teacher_id', $teacher->id)
                ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject'])
                ->get()
                ->sortBy(fn($l) => $l->schoolYearSection?->yearLevel?->sort_order . '-' . $l->schoolYearSection?->section?->name)
                ->values();
        }

        // Active teaching loads based on filter
        $activeLoads = $this->filterClassId !== 'all'
            ? $allTeachingLoads->where('id', (int)$this->filterClassId)
            : $allTeachingLoads;

        $targetLoadIds = $activeLoads->pluck('id');
        $targetSysIds = $activeLoads->pluck('school_year_section_id')->unique();

        // 3. Analytics Calculations
        //
        // IMPORTANT: All metrics must count STUDENTS (not raw grade records).
        // A student has many grade records (one per subject per quarter).
        // We group by student_enrollment_id, compute AVG(grade) per student,
        // then classify each student exactly once — so the total always equals
        // the number of unique students with grade data in the selected scope.
        //
        $uniqueEnrollmentIds = StudentEnrollment::whereIn('school_year_section_id', $targetSysIds)
            ->pluck('id');
        $totalStudentsHandled = $uniqueEnrollmentIds->count();

        // Build a query that returns one row per student: their average grade
        // across the filtered scope (class + quarter)
        $perStudentQuery = Grade::whereIn('section_subject_id', $targetLoadIds)
            ->whereIn('student_enrollment_id', $uniqueEnrollmentIds)
            ->whereNotNull('grade');

        if ($this->filterQuarterId !== 'all') {
            $perStudentQuery->where('quarter_id', (int)$this->filterQuarterId);
        }

        // One average per student — this is the canonical grade for classification
        $studentAverages = $perStudentQuery
            ->selectRaw('student_enrollment_id, AVG(grade) as avg_grade, MIN(grade) as min_grade, MAX(grade) as max_grade')
            ->groupBy('student_enrollment_id')
            ->get();

        $studentAvgValues = $studentAverages->pluck('avg_grade')->filter();

        $overallAverage  = $studentAvgValues->isNotEmpty() ? round($studentAvgValues->avg(), 2) : null;
        $totalPassing    = $studentAverages->filter(fn($r) => (float)$r->avg_grade >= 75.00)->count();
        $passingRate     = $studentAverages->isNotEmpty() ? round(($totalPassing / $studentAverages->count()) * 100, 1) : 0;
        $atRiskCount     = $studentAverages->filter(fn($r) => (float)$r->avg_grade < 75.00)->count();
        $honorsCount     = $studentAverages->filter(fn($r) => (float)$r->avg_grade >= 90.00)->count();


        // 4. Main Line Graph 1: Quarterly Grade Progression Trend
        $completedOrActiveQuarters = $quarters->whereIn('status', ['active', 'completed'])->values();
        $progressionLabels = $completedOrActiveQuarters->map(fn($q) => "{$q->short_name} ({$q->name})")->toArray();
        $progressionSeries = [];

        if ($this->filterClassId !== 'all') {
            // Single selected class progression
            $selectedLoad = $allTeachingLoads->firstWhere('id', (int)$this->filterClassId);
            $className = $selectedLoad
                ? "{$selectedLoad->schoolYearSection?->yearLevel?->name} - {$selectedLoad->schoolYearSection?->section?->name} ({$selectedLoad->subject?->subject_code})"
                : 'Selected Class';

            $dataPoints = [];
            foreach ($completedOrActiveQuarters as $q) {
                $avg = Grade::where('section_subject_id', (int)$this->filterClassId)
                    ->where('quarter_id', $q->id)
                    ->whereNotNull('grade')
                    ->avg('grade');
                $dataPoints[] = $avg !== null ? round((float)$avg, 2) : null;
            }

            $progressionSeries[] = [
                'name' => $className,
                'data' => $dataPoints,
            ];
        } else {
            // Compare top assigned classes across quarters (up to 5 series)
            $loadsToChart = $allTeachingLoads->take(6);
            foreach ($loadsToChart as $ld) {
                $name = "{$ld->schoolYearSection?->yearLevel?->code} - {$ld->schoolYearSection?->section?->name}";
                $dataPoints = [];

                foreach ($completedOrActiveQuarters as $q) {
                    $avg = Grade::where('section_subject_id', $ld->id)
                        ->where('quarter_id', $q->id)
                        ->whereNotNull('grade')
                        ->avg('grade');
                    $dataPoints[] = $avg !== null ? round((float)$avg, 2) : null;
                }

                $progressionSeries[] = [
                    'name' => $name,
                    'data' => $dataPoints,
                ];
            }
        }

        $progressionChart = [
            'labels' => $progressionLabels,
            'series' => $progressionSeries,
            'hasData' => !empty($progressionSeries) && collect($progressionSeries)->pluck('data')->flatten()->filter()->isNotEmpty(),
        ];

        // 5. Mastery Distribution Chart — Correct per-student classification
        //
        // Each student is classified ONCE using their computed average grade.
        // This ensures the sum of all bands equals the number of unique students
        // with valid grade data in the selected scope — never duplicated.
        //
        // DepEd Performance Levels (K-12 Grading System):
        //   Outstanding        : 90–100
        //   Very Satisfactory  : 85–89
        //   Satisfactory       : 80–84
        //   Fairly Satisfactory: 75–79
        //   Did Not Meet       : below 75
        //
        $masteryBands = [
            'Did Not Meet (<75)'          => 0,
            'Fairly Satisfactory (75-79)' => 0,
            'Satisfactory (80-84)'        => 0,
            'Very Satisfactory (85-89)'   => 0,
            'Outstanding (90-100)'        => 0,
        ];

        // $studentAverages already has one row per student — just classify each once
        foreach ($studentAverages as $row) {
            $avg = (float)$row->avg_grade;
            if ($avg < 75.00)      $masteryBands['Did Not Meet (<75)']++;
            elseif ($avg < 80.00)  $masteryBands['Fairly Satisfactory (75-79)']++;
            elseif ($avg < 85.00)  $masteryBands['Satisfactory (80-84)']++;
            elseif ($avg < 90.00)  $masteryBands['Very Satisfactory (85-89)']++;
            else                   $masteryBands['Outstanding (90-100)']++;
        }

        // Sum = count of students who have at least one grade record in scope
        $totalClassifiedStudents = array_sum($masteryBands);

        $masteryChart = [
            'labels'   => array_keys($masteryBands),
            'values'   => array_values($masteryBands),
            'total'    => $totalClassifiedStudents,
            'hasData'  => $totalClassifiedStudents > 0,
        ];


        // 6. Linked Parents count
        $linkedParentsCount = 0;
        if ($advisedSection) {
            $adviseeStudentIds = StudentEnrollment::where('school_year_section_id', $advisedSection->id)->pluck('student_id');
            $linkedParentsCount = ParentModel::whereHas('students', fn($q) => $q->whereIn('students.id', $adviseeStudentIds))->count();
        }

        // 7. Recent activity logs
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.teacher.teacher-dashboard', [
            'teacher' => $teacher,
            'activeSy' => $activeSy,
            'currentQuarter' => $currentQuarter,
            'quarters' => $quarters,
            'advisedSection' => $advisedSection,
            'advisoryStats' => $advisoryStats,
            'allTeachingLoads' => $allTeachingLoads,
            'totalStudentsHandled' => $totalStudentsHandled,
            'overallAverage' => $overallAverage,
            'passingRate' => $passingRate,
            'atRiskCount' => $atRiskCount,
            'honorsCount' => $honorsCount,
            'linkedParentsCount' => $linkedParentsCount,
            'progressionChart' => $progressionChart,
            'masteryChart' => $masteryChart,
            'recentActivities' => $recentActivities,
        ])->layout('layouts.teacher', [
            'title' => 'Teacher Dashboard & Performance Analytics',
            'subtitle' => 'Academic performance metrics, student grade progression, and teaching workloads'
        ]);
    }
}
