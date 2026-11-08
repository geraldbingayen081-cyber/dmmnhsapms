<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;

class AcademicHistoryService
{
    /**
     * Standard DepEd Junior High School Year Levels.
     */
    public const JHS_LEVELS = [
        ['code' => 'G7',  'name' => 'Grade 7',  'sort_order' => 1],
        ['code' => 'G8',  'name' => 'Grade 8',  'sort_order' => 2],
        ['code' => 'G9',  'name' => 'Grade 9',  'sort_order' => 3],
        ['code' => 'G10', 'name' => 'Grade 10', 'sort_order' => 4],
    ];

    /**
     * Retrieve complete academic history for a student from Grade 7 through Grade 10.
     */
    public static function getStudentHistory(int $studentId): array
    {
        $student = Student::with('user')->find($studentId);

        if (! $student) {
            return [
                'student' => null,
                'year_levels' => [],
                'summary' => [
                    'total_available_years' => 0,
                    'total_completed_years' => 0,
                    'overall_gwa' => null,
                    'all_passed_count' => 0,
                    'all_subjects_count' => 0,
                ],
            ];
        }

        // Fetch all enrollments for this student with all relationships
        $enrollments = StudentEnrollment::where('student_id', $student->id)
            ->with([
                'schoolYearSection.schoolYear',
                'schoolYearSection.yearLevel',
                'schoolYearSection.section',
                'schoolYearSection.adviser.user',
                'schoolYearSection.sectionSubjects.subject',
                'schoolYearSection.sectionSubjects.teacher.user',
                'grades.quarter',
            ])
            ->get();

        // Get standard year levels from database or fallback to constants
        $dbYearLevels = YearLevel::whereIn('code', ['G7', 'G8', 'G9', 'G10'])
            ->orderBy('sort_order')
            ->get();

        if ($dbYearLevels->isEmpty()) {
            $yearLevelDefinitions = collect(self::JHS_LEVELS);
        } else {
            $yearLevelDefinitions = $dbYearLevels->map(fn ($yl) => [
                'id' => $yl->id,
                'code' => $yl->code,
                'name' => $yl->name,
                'sort_order' => $yl->sort_order,
            ]);
        }

        $historyByYearLevel = [];
        $allFinalGwas = [];
        $totalPassedSubjects = 0;
        $totalAllSubjects = 0;

        foreach ($yearLevelDefinitions as $ylDef) {
            $ylCode = $ylDef['code'];
            $ylName = $ylDef['name'];
            $ylSort = $ylDef['sort_order'];

            // Find all enrollments for this year level (matching by code or year_level_id)
            $matchingEnrollments = $enrollments->filter(function ($enr) use ($ylCode) {
                return $enr->schoolYearSection?->yearLevel?->code === $ylCode;
            })->sortBy(function ($enr) {
                return $enr->schoolYearSection?->schoolYear?->school_year ?? '';
            })->values();

            if ($matchingEnrollments->isEmpty()) {
                // No records for this grade level in the database
                $historyByYearLevel[$ylCode] = [
                    'code' => $ylCode,
                    'name' => $ylName,
                    'sort_order' => $ylSort,
                    'has_records' => false,
                    'status_label' => 'No Records Available',
                    'message' => "No academic records found for {$ylName}. The learner has not yet enrolled in or completed this grade level.",
                    'records' => [],
                ];

                continue;
            }

            // Process each enrollment under this grade level
            $processedRecords = [];

            foreach ($matchingEnrollments as $enr) {
                $sys = $enr->schoolYearSection;
                $sy = $sys?->schoolYear;
                $section = $sys?->section;
                $adviser = $sys?->adviser;

                // Quarters for this specific school year
                $quarters = collect();
                if ($sy) {
                    $quarters = Quarter::where('school_year_id', $sy->id)
                        ->orderBy('sort_order')
                        ->get();
                }

                // Section subjects for this section
                $sectionSubjects = $sys ? SectionSubject::where('school_year_section_id', $sys->id)
                    ->with(['subject', 'teacher.user'])
                    ->get()
                    ->sortBy(fn ($ss) => $ss->subject?->subject_name)
                    ->values() : collect();

                // Grade records for this enrollment
                $grades = Grade::where('student_enrollment_id', $enr->id)
                    ->whereIn('quarter_id', $quarters->pluck('id'))
                    ->get();

                $subjectRows = [];
                $finalAvgs = [];
                $passedCount = 0;

                foreach ($sectionSubjects as $ss) {
                    $qMarks = [];
                    $validMarks = [];

                    foreach ($quarters as $q) {
                        $rec = $grades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
                        $val = $rec && $rec->grade !== null ? (float) $rec->grade : null;
                        $qMarks[$q->id] = $val;
                        if ($val !== null) {
                            $validMarks[] = $val;
                        }
                    }

                    // A final rating is computed when marks exist
                    $isComplete = count($validMarks) === $quarters->count() && $quarters->count() > 0;
                    $finalAvg = ! empty($validMarks) ? round(array_sum($validMarks) / count($validMarks), 2) : null;

                    if ($finalAvg !== null) {
                        $finalAvgs[] = $finalAvg;
                        if ($finalAvg >= 75.00) {
                            $passedCount++;
                            $totalPassedSubjects++;
                        }
                    }
                    $totalAllSubjects++;

                    $remarks = 'PENDING';
                    if ($finalAvg !== null) {
                        $remarks = $finalAvg >= 75.00 ? 'PASSED' : 'FAILED';
                    }

                    $subjectRows[] = [
                        'section_subject_id' => $ss->id,
                        'name' => $ss->subject?->subject_name ?? 'Subject',
                        'code' => $ss->subject?->subject_code ?? 'SUB',
                        'teacher' => $ss->teacher?->full_name ?? 'Faculty Instructor',
                        'q_marks' => $qMarks,
                        'final_avg' => $finalAvg,
                        'is_complete' => $isComplete,
                        'remarks' => $remarks,
                    ];
                }

                $gwa = ! empty($finalAvgs) ? round(array_sum($finalAvgs) / count($finalAvgs), 2) : null;
                $isAllComplete = count($finalAvgs) === count($sectionSubjects) && count($sectionSubjects) > 0;

                if ($gwa !== null && $isAllComplete) {
                    $allFinalGwas[] = $gwa;
                }

                $hasGrades = $grades->isNotEmpty();

                $processedRecords[] = [
                    'enrollment_id' => $enr->id,
                    'school_year_id' => $sy?->id,
                    'school_year' => $sy?->school_year ?? 'N/A',
                    'school_year_status' => $sy?->status ?? 'unknown',
                    'is_active_sy' => $sy?->status === 'active',
                    'year_level_name' => $ylName,
                    'year_level_code' => $ylCode,
                    'section_name' => $section?->name ?? 'Unassigned',
                    'adviser_name' => $adviser?->full_name ?? 'Assigned Faculty',
                    'enrollment_status' => $enr->status,
                    'enrollment_date' => $enr->enrollment_date,
                    'completion_date' => $enr->completion_date,
                    'quarters' => $quarters,
                    'subject_rows' => $subjectRows,
                    'gwa' => $gwa,
                    'is_all_complete' => $isAllComplete,
                    'passed_count' => $passedCount,
                    'total_subjects' => count($sectionSubjects),
                    'has_grades' => $hasGrades,
                    'overall_remarks' => $gwa !== null ? ($gwa >= 75.00 ? 'PROMOTED / PASSED' : 'NEEDS SUPPORT') : 'ONGOING',
                ];
            }

            $primaryRecord = $processedRecords[0] ?? null;

            $historyByYearLevel[$ylCode] = [
                'code' => $ylCode,
                'name' => $ylName,
                'sort_order' => $ylSort,
                'has_records' => true,
                'status_label' => $primaryRecord && $primaryRecord['has_grades'] ? 'Records Available' : 'Enrolled (Pending Grades)',
                'message' => null,
                'records' => $processedRecords,
                'primary_record' => $primaryRecord,
            ];
        }

        $availableYearsCount = collect($historyByYearLevel)->where('has_records', true)->count();
        $completedYearsCount = collect($historyByYearLevel)->filter(function ($item) {
            return $item['has_records'] && ! empty($item['primary_record']['is_all_complete']);
        })->count();

        $overallGwa = ! empty($allFinalGwas) ? round(array_sum($allFinalGwas) / count($allFinalGwas), 2) : null;

        return [
            'student' => $student,
            'year_levels' => $historyByYearLevel,
            'summary' => [
                'total_available_years' => $availableYearsCount,
                'total_completed_years' => $completedYearsCount,
                'overall_gwa' => $overallGwa,
                'all_passed_count' => $totalPassedSubjects,
                'all_subjects_count' => $totalAllSubjects,
            ],
        ];
    }
}
