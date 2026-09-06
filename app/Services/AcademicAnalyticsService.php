<?php

namespace App\Services;


use App\Models\Grade;

use App\Models\ParentModel;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\YearLevel;
use Illuminate\Support\Facades\DB;

class AcademicAnalyticsService
{
    /**
     * Parse filters array or individual params
     */
    private static function parseFilterIds($schoolYearId = null, $quarterId = null): array
    {
        if (is_array($schoolYearId)) {
            $filters = $schoolYearId;
            $syId = isset($filters['school_year_id']) && is_numeric($filters['school_year_id']) ? (int)$filters['school_year_id'] : AcademicContextService::getActiveSchoolYear()?->id;
            $qId = isset($filters['quarter_id']) && is_numeric($filters['quarter_id']) ? (int)$filters['quarter_id'] : null;
            $ylId = isset($filters['year_level_id']) && is_numeric($filters['year_level_id']) ? (int)$filters['year_level_id'] : null;
            $secId = isset($filters['section_id']) && is_numeric($filters['section_id']) ? (int)$filters['section_id'] : null;
        } else {
            $syId = is_numeric($schoolYearId) ? (int)$schoolYearId : AcademicContextService::getActiveSchoolYear()?->id;
            $qId = is_numeric($quarterId) ? (int)$quarterId : null;
            $ylId = null;
            $secId = null;
        }

        return [$syId, $qId, $ylId, $secId];
    }

    /**
     * Get summary metrics for Dashboard Analytics
     */
    public static function getSummaryMetrics($schoolYearId = null, $quarterId = null): array
    {
        [$syId, $qId] = self::parseFilterIds($schoolYearId, $quarterId);

        $totalStudents = StudentEnrollment::whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $syId))->count();
        $totalTeachers = Teacher::count();
        $totalParents = ParentModel::count();
        $activeSections = SchoolYearSection::where('school_year_id', $syId)->where('status', 'active')->count();

        $gradeQuery = Grade::whereHas('sectionSubject.schoolYearSection', fn($q) => $q->where('school_year_id', $syId));
        if ($qId) {
            $gradeQuery->where('quarter_id', $qId);
        }
        $academicAverage = round($gradeQuery->avg('grade') ?? 0, 2);

        $totalSectionSubjects = SectionSubject::whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $syId))->count();
        $expectedGradesCount = $totalStudents * ($totalSectionSubjects > 0 ? $totalSectionSubjects : 1);
        $actualGradesCount = $gradeQuery->whereNotNull('grade')->count();
        $gradeCompletion = $expectedGradesCount > 0 ? round(($actualGradesCount / $expectedGradesCount) * 100, 1) : 0;

        // Count students qualifying for any honor tier (avg >= 90, no grade < 85) for the selected SY/Quarter
        $honorGradeRows = Grade::when($syId, fn($q) => $q->whereHas('studentEnrollment.schoolYearSection', fn($sq) => $sq->where('school_year_id', $syId)))
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->get()
            ->groupBy('student_enrollment_id');
        $totalAchievements = $honorGradeRows->filter(fn($g) => $g->avg('grade') >= 90.00 && $g->min('grade') >= 85.00)->count();

        return [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalParents' => $totalParents,
            'activeSections' => $activeSections,
            'gradeCompletion' => min($gradeCompletion, 100),
            'academicAverage' => $academicAverage,
            'totalAchievements' => $totalAchievements,
        ];
    }

    /**
     * Get summary stats for Academic Analytics Management
     */
    public static function getSummaryStats(array $filters = []): array
    {
        [$syId, $qId, $ylId, $secId] = self::parseFilterIds($filters);

        $gradeQuery = Grade::query();

        if ($syId || $ylId || $secId) {
            $gradeQuery->whereHas('studentEnrollment.schoolYearSection', function ($q) use ($syId, $ylId, $secId) {
                if ($syId) $q->where('school_year_id', $syId);
                if ($ylId) $q->where('year_level_id', $ylId);
                if ($secId) $q->where('section_id', $secId);
            });
        }

        if ($qId) {
            $gradeQuery->where('quarter_id', $qId);
        }

        $overallAverage = round($gradeQuery->avg('grade') ?? 0, 2);

        $studentAverages = Grade::query()
            ->when($syId || $ylId || $secId, function ($q) use ($syId, $ylId, $secId) {
                $q->whereHas('studentEnrollment.schoolYearSection', function ($sq) use ($syId, $ylId, $secId) {
                    if ($syId) $sq->where('school_year_id', $syId);
                    if ($ylId) $sq->where('year_level_id', $ylId);
                    if ($secId) $sq->where('section_id', $secId);
                });
            })
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->select('student_enrollment_id', DB::raw('AVG(grade) as avg_grade'))
            ->groupBy('student_enrollment_id')
            ->get();

        $honorStudentsCount = $studentAverages->filter(fn($item) => $item->avg_grade >= 90.00)->count();
        $totalEvaluatedStudents = $studentAverages->count();
        $passingStudentsCount = $studentAverages->filter(fn($item) => $item->avg_grade >= 75.00)->count();
        $atRiskCount = $studentAverages->filter(fn($item) => $item->avg_grade < 75.00)->count();

        $passingRate = $totalEvaluatedStudents > 0 ? round(($passingStudentsCount / $totalEvaluatedStudents) * 100, 1) : 0.0;

        return [
            'overall_average' => $overallAverage,
            'honor_students' => $honorStudentsCount,
            'passing_rate' => $passingRate,
            'at_risk_students' => $atRiskCount,
        ];
    }

    /**
     * Get average performance by Year Level (Grade 7 - Grade 10)
     */
    public static function getYearLevelPerformance($schoolYearId = null, $quarterId = null): array
    {
        [$syId, $qId] = self::parseFilterIds($schoolYearId, $quarterId);
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $labels = [];
        $values = [];

        foreach ($yearLevels as $yl) {
            $labels[] = $yl->name;
            $avg = Grade::whereHas('studentEnrollment.schoolYearSection', function ($q) use ($syId, $yl) {
                if ($syId) $q->where('school_year_id', $syId);
                $q->where('year_level_id', $yl->id);
            })
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->avg('grade');

            $values[] = round($avg ?? 0, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'hasData' => array_sum($values) > 0,
        ];
    }

    public static function getYearLevelPerformanceData(array $filters = []): array
    {
        $perf = self::getYearLevelPerformance($filters);
        return [
            'categories' => $perf['labels'],
            'series' => $perf['values'],
        ];
    }

    /**
     * Get quarterly performance trend (Q1 - Q3)
     */
    public static function getQuarterlyTrend($schoolYearId = null): array
    {
        [$syId] = self::parseFilterIds($schoolYearId);
        $quarters = Quarter::when($syId, fn($q) => $q->where('school_year_id', $syId))->orderBy('sort_order')->get();
        $labels = [];
        $values = [];

        foreach ($quarters as $q) {
            $labels[] = $q->short_name ?: $q->name;
            $avg = Grade::where('quarter_id', $q->id)->avg('grade');
            $values[] = round($avg ?? 0, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'hasData' => array_sum($values) > 0,
        ];
    }

    public static function getQuarterlyTrendData(array $filters = []): array
    {
        $trend = self::getQuarterlyTrend($filters);
        return [
            'categories' => $trend['labels'],
            'series' => $trend['values'],
        ];
    }

    /**
     * Get subject performance breakdown
     */
    public static function getSubjectPerformance($schoolYearId = null, $quarterId = null): array
    {
        [$syId, $qId, $ylId, $secId] = self::parseFilterIds($schoolYearId, $quarterId);
        $subjects = Subject::all();
        $labels = [];
        $values = [];

        foreach ($subjects as $sub) {
            $avg = Grade::whereHas('sectionSubject', function ($q) use ($syId, $sub, $ylId, $secId) {
                $q->where('subject_id', $sub->id)
                  ->whereHas('schoolYearSection', function ($sq) use ($syId, $ylId, $secId) {
                      if ($syId) $sq->where('school_year_id', $syId);
                      if ($ylId) $sq->where('year_level_id', $ylId);
                      if ($secId) $sq->where('section_id', $secId);
                  });
            })
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->avg('grade');

            if ($avg !== null && $avg > 0) {
                $labels[] = $sub->subject_name;
                $values[] = round($avg, 2);
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'hasData' => count($values) > 0,
        ];
    }

    public static function getSubjectMasteryData(array $filters = []): array
    {
        $sub = self::getSubjectPerformance($filters);
        return [
            'categories' => $sub['labels'],
            'series' => $sub['values'],
        ];
    }

    /**
     * Get grade distribution data
     */
    public static function getGradeDistributionData(array $filters = []): array
    {
        [$syId, $qId, $ylId, $secId] = self::parseFilterIds($filters);

        $gradeQuery = Grade::query();
        if ($syId || $ylId || $secId) {
            $gradeQuery->whereHas('studentEnrollment.schoolYearSection', function ($q) use ($syId, $ylId, $secId) {
                if ($syId) $q->where('school_year_id', $syId);
                if ($ylId) $q->where('year_level_id', $ylId);
                if ($secId) $q->where('section_id', $secId);
            });
        }
        if ($qId) {
            $gradeQuery->where('quarter_id', $qId);
        }

        $grades = $gradeQuery->pluck('grade');

        $outstanding = 0; // 90-100
        $verySatisfactory = 0; // 85-89
        $satisfactory = 0; // 80-84
        $fairlySatisfactory = 0; // 75-79
        $didNotMeet = 0; // < 75

        foreach ($grades as $g) {
            if ($g >= 90) $outstanding++;
            elseif ($g >= 85) $verySatisfactory++;
            elseif ($g >= 80) $satisfactory++;
            elseif ($g >= 75) $fairlySatisfactory++;
            else $didNotMeet++;
        }

        return [
            'labels' => ['Outstanding (90-100)', 'Very Satisfactory (85-89)', 'Satisfactory (80-84)', 'Fairly Satisfactory (75-79)', 'Did Not Meet (Below 75)'],
            'series' => [$outstanding, $verySatisfactory, $satisfactory, $fairlySatisfactory, $didNotMeet],
        ];
    }

    /**
     * Get subject mastery detailed table breakdown
     */
    public static function getSubjectMasteryBreakdown(array $filters = []): array
    {
        [$syId, $qId, $ylId, $secId] = self::parseFilterIds($filters);
        $subjects = Subject::all();
        $breakdown = [];

        foreach ($subjects as $sub) {
            $grades = Grade::whereHas('sectionSubject', function ($q) use ($syId, $sub, $ylId, $secId) {
                $q->where('subject_id', $sub->id)
                  ->whereHas('schoolYearSection', function ($sq) use ($syId, $ylId, $secId) {
                      if ($syId) $sq->where('school_year_id', $syId);
                      if ($ylId) $sq->where('year_level_id', $ylId);
                      if ($secId) $sq->where('section_id', $secId);
                  });
            })
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->pluck('grade');

            if ($grades->count() > 0) {
                $avg = round($grades->avg() ?? 0, 2);
                $passedCount = $grades->filter(fn($g) => $g >= 75.00)->count();
                $passingRate = round(($passedCount / $grades->count()) * 100, 1);

                $breakdown[] = [
                    'subject_code' => $sub->subject_code,
                    'subject_name' => $sub->subject_name,
                    'students_count' => $grades->count(),
                    'average' => $avg,
                    'passing_rate' => $passingRate,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Get at-risk students (< 75)
     */
    public static function getAtRiskStudents($schoolYearId = null, $quarterId = null, int $limit = 5): array
    {
        [$syId, $qId, $ylId, $secId] = self::parseFilterIds($schoolYearId, $quarterId);

        $studentGrades = Grade::with([
            'studentEnrollment.student',
            'studentEnrollment.schoolYearSection.yearLevel',
            'studentEnrollment.schoolYearSection.section'
        ])
        ->when($syId || $ylId || $secId, function ($q) use ($syId, $ylId, $secId) {
            $q->whereHas('studentEnrollment.schoolYearSection', function ($sq) use ($syId, $ylId, $secId) {
                if ($syId) $sq->where('school_year_id', $syId);
                if ($ylId) $sq->where('year_level_id', $ylId);
                if ($secId) $sq->where('section_id', $secId);
            });
        })
        ->when($qId, fn($q) => $q->where('quarter_id', $qId))
        ->get()
        ->groupBy('student_enrollment_id');

        $atRiskList = [];

        foreach ($studentGrades as $enrollmentId => $gList) {
            $firstGrade = $gList->first();
            $enrollment = $firstGrade?->studentEnrollment;
            $student = $enrollment?->student;

            if (!$student) continue;

            $avg = round($gList->avg('grade') ?? 0, 2);
            $failingCount = $gList->filter(fn($g) => $g->grade < 75.00)->count();

            if ($avg < 75.00 || $failingCount > 0) {
                $atRiskList[] = [
                    'student_number' => $student->student_number,
                    'full_name' => $student->full_name,
                    'year_level' => $enrollment->schoolYearSection?->yearLevel?->name ?? 'N/A',
                    'section' => $enrollment->schoolYearSection?->section?->name ?? 'N/A',
                    'failing_subjects_count' => $failingCount,
                    'average_grade' => $avg,
                ];
            }
        }

        return array_slice($atRiskList, 0, $limit);
    }

    /**
     * Get achievement distribution — counts students per honor tier using
     * the same DepEd criteria as AcademicAchievementsManagement.
     *
     * Tiers:
     *   With Highest Honors : avg >= 98  AND min grade >= 85
     *   With High Honors    : avg >= 95  AND min grade >= 85
     *   With Honors         : avg >= 90  AND min grade >= 85
     *   Non-Qualifier       : avg < 90   or  min grade < 85
     */
    public static function getAchievementDistribution($schoolYearId = null, $quarterId = null): array
    {
        [$syId, $qId] = self::parseFilterIds($schoolYearId, $quarterId);

        // Pull all grade rows for enrolled students, filtered by school year / quarter
        $gradeRows = Grade::with('studentEnrollment')
            ->when($syId, function ($q) use ($syId) {
                $q->whereHas('studentEnrollment.schoolYearSection', fn($sq) => $sq->where('school_year_id', $syId));
            })
            ->when($qId, fn($q) => $q->where('quarter_id', $qId))
            ->get();

        if ($gradeRows->isEmpty()) {
            return [
                'labels'  => ['With Highest Honors', 'With High Honors', 'With Honors', 'Non-Qualifier'],
                'values'  => [0, 0, 0, 0],
                'hasData' => false,
            ];
        }

        // Group grades by student_enrollment_id and compute avg + min per student
        $grouped = $gradeRows->groupBy('student_enrollment_id');

        $highestHonors = 0;
        $highHonors    = 0;
        $honors        = 0;
        $nonQualifier  = 0;

        foreach ($grouped as $enrollmentId => $gList) {
            $avg      = $gList->avg('grade');
            $minGrade = $gList->min('grade');

            if ($avg >= 98.00 && $minGrade >= 85.00) {
                $highestHonors++;
            } elseif ($avg >= 95.00 && $minGrade >= 85.00) {
                $highHonors++;
            } elseif ($avg >= 90.00 && $minGrade >= 85.00) {
                $honors++;
            } else {
                $nonQualifier++;
            }
        }

        $totalStudents = $grouped->count();
        $hasData = $totalStudents > 0;

        return [
            'labels'       => ['With Highest Honors', 'With High Honors', 'With Honors', 'Non-Qualifier'],
            'values'       => [$highestHonors, $highHonors, $honors, $nonQualifier],
            'hasData'      => $hasData,
            'totalStudents'=> $totalStudents,
        ];
    }
}

