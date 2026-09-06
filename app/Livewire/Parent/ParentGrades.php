<?php

namespace App\Livewire\Parent;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Livewire\Component;

class ParentGrades extends Component
{
    public $selectedStudentId = null;

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
        $parent = auth()->user()->parent;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $children = collect();
        $selectedStudent = null;
        $activeEnrollment = null;
        $quarters = collect();
        $subjectRows = [];
        $gwa = null;

        if ($parent && $activeSy) {
            // Strictly 1st Quarter to 3rd Quarter
            $quarters = Quarter::where('school_year_id', $activeSy->id)
                ->whereIn('sort_order', [1, 2, 3])
                ->orderBy('sort_order')
                ->get();

            $children = $parent->students()
                ->with(['enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section'])
                ->get();

            if ($children->isNotEmpty()) {
                if (!$this->selectedStudentId || !$children->contains('id', (int)$this->selectedStudentId)) {
                    $this->selectedStudentId = $children->first()->id;
                }

                $selectedStudent = $children->firstWhere('id', (int)$this->selectedStudentId);

                if ($selectedStudent) {
                    $activeEnrollment = StudentEnrollment::where('student_id', $selectedStudent->id)
                        ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                        ->with(['schoolYearSection.yearLevel', 'schoolYearSection.section', 'schoolYearSection.adviser.user'])
                        ->first();

                    if ($activeEnrollment) {
                        $sectionId = $activeEnrollment->school_year_section_id;

                        $sectionSubjects = SectionSubject::where('school_year_section_id', $sectionId)
                            ->with(['subject', 'teacher.user'])
                            ->get()
                            ->sortBy(fn($ss) => $ss->subject?->subject_name)
                            ->values();

                        $childGrades = Grade::where('student_enrollment_id', $activeEnrollment->id)
                            ->whereIn('quarter_id', $quarters->pluck('id'))
                            ->get();

                        $finalAvgs = [];

                        foreach ($sectionSubjects as $ss) {
                            $qMarks = [];
                            $validMarks = [];

                            foreach ($quarters as $q) {
                                $rec = $childGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->first();
                                $val = $rec ? (float)$rec->grade : null;
                                $qMarks[$q->id] = $val;
                                if ($val !== null) {
                                    $validMarks[] = $val;
                                }
                            }

                            // Final Grade strictly requires complete grades from 1st to 3rd quarter
                            $isCompleteQ1toQ3 = (count($validMarks) === $quarters->count()) && $quarters->count() === 3;
                            $finalAvg = $isCompleteQ1toQ3 ? round(array_sum($validMarks) / count($validMarks), 2) : null;
                            if ($finalAvg !== null) {
                                $finalAvgs[] = $finalAvg;
                            }

                            $subjectRows[] = [
                                'name' => $ss->subject?->subject_name ?? 'Subject',
                                'code' => $ss->subject?->subject_code ?? 'SUB',
                                'teacher' => $ss->teacher?->full_name ?? 'Faculty Instructor',
                                'q_marks' => $qMarks,
                                'final_avg' => $finalAvg,
                                'is_complete' => $isCompleteQ1toQ3,
                                'remarks' => $finalAvg !== null ? ($finalAvg >= 75.00 ? 'PASSED' : 'FAILED') : 'PENDING',
                            ];
                        }

                        $isAllComplete = count($finalAvgs) === count($sectionSubjects) && count($sectionSubjects) > 0;
                        if ($isAllComplete) {
                            $gwa = round(array_sum($finalAvgs) / count($finalAvgs), 2);
                        }
                    }
                }
            }
        }

        return view('livewire.parent.parent-grades', [
            'parent' => $parent,
            'children' => $children,
            'selectedStudent' => $selectedStudent,
            'activeSy' => $activeSy,
            'activeEnrollment' => $activeEnrollment,
            'quarters' => $quarters,
            'subjectRows' => $subjectRows,
            'gwa' => $gwa,
        ])->layout('layouts.parent', [
            'title' => 'Official Progress Report Card (SF9)',
            'subtitle' => 'DepEd Form 138-JHS Consolidated Quarterly Ratings (Q1 to Q3)',
        ]);
    }
}
