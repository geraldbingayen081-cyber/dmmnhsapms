<?php

namespace App\Livewire\Admin;

use App\Models\Achievement;
use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicAchievementsManagement extends Component
{
    use WithPagination;

    public $activeTab = 'list';

    public $selectedSchoolYearId = 'all';
    public $selectedQuarterId = 'all';
    public $selectedYearLevelId = 'all';
    public $selectedSectionId = 'all';
    public $filterHonorCategory = 'all';

    public $minHighestAverage = 98.00;
    public $minHighAverage = 95.00;
    public $minHonorsAverage = 90.00;
    public $minGradeCap = 85.00;

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedSchoolYearId = $activeSy->id;
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->selectedQuarterId = $currentQuarter->id;
            }
        }
    }

    public function updatedSelectedYearLevelId()
    {
        $this->selectedSectionId = 'all';
        $this->resetPage();
    }

    public function updatedSelectedSchoolYearId()
    {
        $this->selectedSectionId = 'all';
        $this->resetPage();
    }

    public function awardHonorTitle($studentEnrollmentId, $title)
    {
        $enrollment = StudentEnrollment::with('student')->findOrFail($studentEnrollmentId);

        DB::transaction(function () use ($enrollment, $title) {
            $ach = Achievement::create([
                'student_id' => $enrollment->student_id,
                'title' => $title,
                'description' => "Official DepEd Honor Roll Recognition ({$title}) for SY {$enrollment->schoolYearSection?->schoolYear?->school_year}.",
                'date_awarded' => now()->toDateString(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Awarded Academic Honor',
                'subject_type' => Achievement::class,
                'subject_id' => $ach->id,
                'description' => "Awarded academic honor title '{$title}' to student {$enrollment->student?->full_name} ({$enrollment->student?->student_number}).",
            ]);
        });

        session()->flash('message', "Academic honor '{$title}' awarded successfully!");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = $this->selectedSchoolYearId !== 'all' ? Quarter::where('school_year_id', $this->selectedSchoolYearId)->orderBy('sort_order')->get() : Quarter::orderBy('sort_order')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = \App\Models\SchoolYearSection::query()
            ->when($this->selectedSchoolYearId !== 'all', fn($q) => $q->where('school_year_id', $this->selectedSchoolYearId))
            ->when($this->selectedYearLevelId !== 'all', fn($q) => $q->where('year_level_id', $this->selectedYearLevelId))
            ->with('section')
            ->get()
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        // Calculate Honor Candidates from Student Enrollments & Grades
        $enrollmentsQuery = StudentEnrollment::with([
            'student.user',
            'student.achievements',
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section',
            'grades' => function ($q) {
                if ($this->selectedQuarterId !== 'all') {
                    $q->where('quarter_id', $this->selectedQuarterId);
                }
            }
        ]);

        if ($this->selectedSchoolYearId !== 'all') {
            $enrollmentsQuery->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $this->selectedSchoolYearId));
        }

        if ($this->selectedYearLevelId !== 'all') {
            $enrollmentsQuery->whereHas('schoolYearSection', fn($q) => $q->where('year_level_id', $this->selectedYearLevelId));
        }

        if ($this->selectedSectionId !== 'all') {
            $enrollmentsQuery->whereHas('schoolYearSection', fn($q) => $q->where('section_id', $this->selectedSectionId));
        }

        $candidates = [];
        foreach ($enrollmentsQuery->get() as $enr) {
            $grades = $enr->grades;
            if ($grades->count() === 0) continue;

            $avg = $grades->avg('grade');
            $minGrade = $grades->min('grade');

            // DepEd Honor Qualification Logic
            $honorTitle = null;
            if ($avg >= $this->minHighestAverage && $minGrade >= $this->minGradeCap) {
                $honorTitle = 'With Highest Honors';
            } elseif ($avg >= $this->minHighAverage && $minGrade >= $this->minGradeCap) {
                $honorTitle = 'With High Honors';
            } elseif ($avg >= $this->minHonorsAverage && $minGrade >= $this->minGradeCap) {
                $honorTitle = 'With Honors';
            }

            $bestSubjects = [];
            foreach ($grades as $g) {
                $subName = $g->sectionSubject?->subject?->subject_name;
                if ($subName && $g->grade >= 95.00) {
                    $bestSubjects[] = "Best in {$subName}";
                }
            }
            $bestSubjects = array_values(array_unique($bestSubjects));

            if ($this->filterHonorCategory !== 'all') {
                if ($this->filterHonorCategory === 'highest' && $honorTitle !== 'With Highest Honors') continue;
                if ($this->filterHonorCategory === 'high' && $honorTitle !== 'With High Honors') continue;
                if ($this->filterHonorCategory === 'honors' && $honorTitle !== 'With Honors') continue;
                if (str_starts_with($this->filterHonorCategory, 'best_')) {
                    $targetSubjectName = substr($this->filterHonorCategory, 5);
                    $targetAchievement = "Best in " . $targetSubjectName;
                    if (!in_array($targetAchievement, $bestSubjects)) continue;
                }
            }

            $dbAchievements = $enr->student?->achievements?->pluck('title')->toArray() ?? [];

            $achievementList = [];
            if ($honorTitle) {
                $achievementList[] = $honorTitle;
            }
            foreach ($bestSubjects as $bs) {
                if (!in_array($bs, $achievementList)) {
                    $achievementList[] = $bs;
                }
            }
            foreach ($dbAchievements as $dba) {
                if (!in_array($dba, $achievementList)) {
                    $achievementList[] = $dba;
                }
            }

            if ($honorTitle || !empty($bestSubjects) || !empty($dbAchievements) || $avg >= 88.00) {
                $candidates[] = [
                    'enrollment_id' => $enr->id,
                    'student_id' => $enr->student_id,
                    'student_number' => $enr->student?->student_number,
                    'student_name' => $enr->student?->full_name,
                    'school_year' => $enr->schoolYearSection?->schoolYear?->school_year,
                    'year_level' => $enr->schoolYearSection?->yearLevel?->name,
                    'section' => $enr->schoolYearSection?->section?->name,
                    'average' => $avg,
                    'lowest_grade' => $minGrade,
                    'honor_title' => $honorTitle ?? 'Eligible Candidate',
                    'best_subjects' => $bestSubjects,
                    'achievements' => !empty($achievementList) ? $achievementList : ['Candidate'],
                    'awarded' => $enr->student?->achievements->where('title', $honorTitle)->count() > 0,
                ];
            }
        }

        $subjects = Subject::all();

        usort($candidates, fn($a, $b) => $b['average'] <=> $a['average']);

        // ---- Analytics Data ----
        // Build analytics across ALL school years (no filter applied)
        $allEnrollments = StudentEnrollment::with([
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'grades.sectionSubject.subject',
        ])->get();

        $syLabels = $schoolYears->pluck('school_year')->reverse()->values()->toArray();
        $syIds    = $schoolYears->pluck('id', 'school_year')->reverse()->toArray();

        // Initialize analytics containers
        $honorTrendData = [
            'With Highest Honors' => array_fill_keys($syLabels, 0),
            'With High Honors'    => array_fill_keys($syLabels, 0),
            'With Honors'         => array_fill_keys($syLabels, 0),
        ];
        $gradeLevelTrendData = [];
        foreach ($yearLevels as $yl) {
            $gradeLevelTrendData[$yl->name] = array_fill_keys($syLabels, 0);
        }
        $subjectAwardCounts = array_fill_keys($subjects->pluck('subject_name')->toArray(), 0);
        $avgGpaTrend = array_fill_keys($syLabels, []);

        foreach ($allEnrollments as $enr) {
            $gr = $enr->grades;
            if ($gr->count() === 0) continue;
            $avg = $gr->avg('grade');
            $min = $gr->min('grade');
            $syName = $enr->schoolYearSection?->schoolYear?->school_year;
            $ylName = $enr->schoolYearSection?->yearLevel?->name;
            if (!$syName || !array_key_exists($syName, array_flip($syLabels))) continue;

            // Honor title
            $hTitle = null;
            if ($avg >= 98 && $min >= 85) $hTitle = 'With Highest Honors';
            elseif ($avg >= 95 && $min >= 85) $hTitle = 'With High Honors';
            elseif ($avg >= 90 && $min >= 85) $hTitle = 'With Honors';

            if ($hTitle) {
                $honorTrendData[$hTitle][$syName]++;
            }

            // Grade level achievers trend (any honor)
            if ($hTitle && $ylName && isset($gradeLevelTrendData[$ylName])) {
                $gradeLevelTrendData[$ylName][$syName]++;
            }

            // Subject awards
            foreach ($gr as $g) {
                $subName = $g->sectionSubject?->subject?->subject_name;
                if ($subName && $g->grade >= 95 && isset($subjectAwardCounts[$subName])) {
                    $subjectAwardCounts[$subName]++;
                }
            }

            // GPA trend (honor students only)
            if ($hTitle) {
                $avgGpaTrend[$syName][] = $avg;
            }
        }

        // Flatten GPA trend to averages per SY
        $avgGpaTrendLine = [];
        foreach ($syLabels as $sy) {
            $vals = $avgGpaTrend[$sy];
            $avgGpaTrendLine[$sy] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : null;
        }

        // Total achievers per SY
        $totalAchieversTrend = array_fill_keys($syLabels, 0);
        foreach ($honorTrendData as $vals) {
            foreach ($vals as $sy => $count) {
                $totalAchieversTrend[$sy] += $count;
            }
        }

        // KPI summary totals (from filtered candidates)
        $kpiTotal      = count($candidates);
        $kpiHighest    = count(array_filter($candidates, fn($c) => $c['honor_title'] === 'With Highest Honors'));
        $kpiHigh       = count(array_filter($candidates, fn($c) => $c['honor_title'] === 'With High Honors'));
        $kpiHonors     = count(array_filter($candidates, fn($c) => $c['honor_title'] === 'With Honors'));
        $kpiBestSubj   = count(array_filter($candidates, fn($c) => !empty($c['best_subjects'])));
        arsort($subjectAwardCounts);

        return view('livewire.admin.academic-achievements-management', [
            'candidates'          => $candidates,
            'schoolYears'         => $schoolYears,
            'quarters'            => $quarters,
            'yearLevels'          => $yearLevels,
            'sections'            => $sections,
            'subjects'            => $subjects,
            // Analytics
            'syLabels'            => $syLabels,
            'honorTrendData'      => $honorTrendData,
            'gradeLevelTrendData' => $gradeLevelTrendData,
            'totalAchieversTrend' => $totalAchieversTrend,
            'avgGpaTrendLine'     => $avgGpaTrendLine,
            'subjectAwardCounts'  => $subjectAwardCounts,
            'kpiTotal'            => $kpiTotal,
            'kpiHighest'          => $kpiHighest,
            'kpiHigh'             => $kpiHigh,
            'kpiHonors'           => $kpiHonors,
            'kpiBestSubj'         => $kpiBestSubj,
        ])->layout('layouts.admin', ['title' => 'Student Achievements', 'subtitle' => 'Automated student achievement tracking, honor recognition, and excellence lists']);
    }
}
