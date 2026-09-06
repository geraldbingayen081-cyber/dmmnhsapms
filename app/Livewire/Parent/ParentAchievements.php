<?php

namespace App\Livewire\Parent;

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Livewire\Component;

class ParentAchievements extends Component
{
    public $selectedStudentId = null;
    public $selectedQuarter = 'all';

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
        $allAwards = [];

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
                        ->first();

                    if ($activeEnrollment) {
                        $sectionId = $activeEnrollment->school_year_section_id;

                        $sectionSubjects = SectionSubject::where('school_year_section_id', $sectionId)
                            ->with('subject')
                            ->get();

                        $childGrades = Grade::where('student_enrollment_id', $activeEnrollment->id)
                            ->whereIn('quarter_id', $quarters->pluck('id'))
                            ->get();

                        $allSectionGrades = Grade::whereHas('studentEnrollment', fn($q) => $q->where('school_year_section_id', $sectionId))
                            ->whereIn('quarter_id', $quarters->pluck('id'))
                            ->get();

                        // 1. Calculate Quarterly Academic Honors and Best in Subject for each quarter
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

                                // DepEd General Academic Honor for this specific quarter
                                $qHonor = null;
                                if ($qAvg >= 98.00 && $minG >= 85.00) {
                                    $qHonor = 'With Highest Honors';
                                } elseif ($qAvg >= 95.00 && $minG >= 85.00) {
                                    $qHonor = 'With High Honors';
                                } elseif ($qAvg >= 90.00 && $minG >= 85.00) {
                                    $qHonor = 'With Honors';
                                }

                                if ($qHonor) {
                                    $allAwards[] = [
                                        'type' => 'honor',
                                        'title' => $qHonor,
                                        'quarter' => $q->name,
                                        'quarter_short' => $q->short_name,
                                        'quarter_id' => $q->id,
                                        'grade' => $qAvg,
                                        'description' => "Official DepEd Academic Excellence Honor for general average of {$qAvg} in the {$q->name}.",
                                    ];
                                }

                                // Best in Subject awards for this specific quarter
                                foreach ($sectionSubjects as $ss) {
                                    if (isset($qGrades[$ss->id])) {
                                        $subMark = $qGrades[$ss->id];
                                        $subName = $ss->subject?->subject_name ?? 'Subject';

                                        // Section top grade for this subject in this quarter
                                        $secTop = $allSectionGrades->where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->max('grade');
                                        $secTopVal = $secTop ? (float)$secTop : 0;

                                        if ($subMark >= 95.00 || ($subMark >= 90.00 && $subMark >= $secTopVal && $secTopVal > 0)) {
                                            $allAwards[] = [
                                                'type' => 'subject',
                                                'title' => "Best in {$subName}",
                                                'subject' => $subName,
                                                'quarter' => $q->name,
                                                'quarter_short' => $q->short_name,
                                                'quarter_id' => $q->id,
                                                'grade' => $subMark,
                                                'description' => "Recognized for top academic performance in {$subName} in the {$q->name}.",
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        // 2. Official registered achievements from database
                        $dbAchievements = $selectedStudent->achievements;
                        foreach ($dbAchievements as $dba) {
                            $dbaQuarter = !empty($dba->quarter) ? $dba->quarter : '1st Quarter';
                            $matchingQ = $quarters->first(fn($q) => stripos($q->name, $dbaQuarter) !== false || stripos($q->short_name, $dbaQuarter) !== false);

                            $exists = collect($allAwards)->contains(function ($item) use ($dba, $dbaQuarter) {
                                return $item['title'] === $dba->title && $item['quarter'] === $dbaQuarter;
                            });

                            if (!$exists) {
                                $allAwards[] = [
                                    'type' => 'citation',
                                    'title' => $dba->title,
                                    'quarter' => $dbaQuarter,
                                    'quarter_short' => $matchingQ ? $matchingQ->short_name : 'Award',
                                    'quarter_id' => $matchingQ ? $matchingQ->id : null,
                                    'description' => $dba->description ?? "Official Academic Citation recorded in school registry for {$dbaQuarter}.",
                                    'date' => $dba->date_awarded,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Apply quarter filter
        $filteredAwards = $allAwards;
        if ($this->selectedQuarter !== 'all') {
            $filteredAwards = array_values(array_filter($allAwards, function ($aw) {
                return (string)($aw['quarter_id'] ?? '') === (string)$this->selectedQuarter
                    || stripos($aw['quarter'], "{$this->selectedQuarter}") !== false;
            }));
        }

        return view('livewire.parent.parent-achievements', [
            'parent' => $parent,
            'children' => $children,
            'selectedStudent' => $selectedStudent,
            'activeSy' => $activeSy,
            'quarters' => $quarters,
            'awardsList' => $filteredAwards,
            'totalAwardsCount' => count($allAwards),
        ])->layout('layouts.parent', [
            'title' => 'Child Awards and Achievements',
            'subtitle' => 'Quarterly Academic Recognitions and Subject Excellence Awards',
        ]);
    }
}
