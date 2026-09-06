<?php

namespace App\Livewire\Teacher;

use App\Models\Achievement;
use App\Models\ActivityLog;
use App\Models\ParentModel;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdvisoryManagement extends Component
{
    public $activeTab = 'roster'; // roster, grades, honors
    public $selectedQuarterId = 'all';
    public $filterAchieverCategory = 'all'; // all, honors, highest, high, with_honors, best_subjects, or best_{SubjectName}

    // Link Parent Modal state
    public $showLinkParentModal = false;
    public $targetStudentId = null;
    public $selectedParentId = null;
    public $relationship = 'Mother';
    public $parentSearch = '';

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->selectedQuarterId = $currentQuarter->id;
            }
        }
    }

    public function openLinkParentModal($studentId)
    {
        $this->resetValidation();
        $this->targetStudentId = $studentId;
        $this->selectedParentId = null;
        $this->relationship = 'Mother';
        $this->parentSearch = '';
        $this->showLinkParentModal = true;
    }

    public function linkParent()
    {
        $this->validate([
            'targetStudentId' => 'required|exists:students,id',
            'selectedParentId' => 'required|exists:parents,id',
            'relationship' => 'required|string|max:50',
        ]);

        $parent = ParentModel::findOrFail($this->selectedParentId);
        $student = Student::findOrFail($this->targetStudentId);

        // Sync or attach without detaching existing parents
        $parent->students()->syncWithoutDetaching([
            $student->id => ['relationship' => $this->relationship]
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Linked Parent Account',
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'description' => "Teacher linked parent {$parent->full_name} ({$parent->user?->login_id}) to advisee {$student->full_name} as {$this->relationship}.",
        ]);

        $this->showLinkParentModal = false;
        session()->flash('message', "Parent {$parent->full_name} successfully linked to {$student->full_name}!");
    }

    public function unlinkParent($parentId, $studentId)
    {
        $parent = ParentModel::findOrFail($parentId);
        $student = Student::findOrFail($studentId);

        $parent->students()->detach($student->id);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Unlinked Parent Account',
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'description' => "Teacher unlinked parent {$parent->full_name} from advisee {$student->full_name}.",
        ]);

        session()->flash('message', "Parent unlinked successfully.");
    }

    /**
     * Award an official Academic Honor or Subject Excellence Award
     * to the student in the shared achievements registry.
     */
    public function awardHonorTitle($studentEnrollmentId, $title)
    {
        $enrollment = StudentEnrollment::with(['student', 'schoolYearSection.schoolYear'])->findOrFail($studentEnrollmentId);

        $quarterName = '1st Quarter';
        if ($this->selectedQuarterId !== 'all') {
            $quarterModel = \App\Models\Quarter::find($this->selectedQuarterId);
            if ($quarterModel) {
                $quarterName = $quarterModel->name;
            }
        }

        DB::transaction(function () use ($enrollment, $title, $quarterName) {
            $ach = Achievement::create([
                'student_id' => $enrollment->student_id,
                'school_year_id' => $enrollment->schoolYearSection?->school_year_id,
                'quarter' => $quarterName,
                'title' => $title,
                'description' => "Official DepEd Academic Excellence Recognition ({$title}) awarded for {$quarterName}, SY {$enrollment->schoolYearSection?->schoolYear?->school_year}.",
                'date_awarded' => now()->toDateString(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Teacher Awarded Academic Honor',
                'subject_type' => Achievement::class,
                'subject_id' => $ach->id,
                'description' => "Teacher awarded academic honor/subject award '{$title}' to student {$enrollment->student?->full_name} ({$enrollment->student?->student_number}).",
            ]);
        });

        session()->flash('message', "Official award '{$title}' successfully registered for {$enrollment->student?->full_name}!");
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $advisedSection = null;
        $enrollments = collect();
        $quarters = collect();
        $subjects = collect();
        $candidates = [];
        $targetStudent = null;

        if ($this->targetStudentId) {
            $targetStudent = Student::find($this->targetStudentId);
        }

        if ($teacher && $activeSy) {
            $quarters = Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get();

            $advisedSection = SchoolYearSection::where('school_year_id', $activeSy->id)
                ->where('adviser_id', $teacher->id)
                ->with(['section', 'yearLevel', 'schoolYear'])
                ->first();

            if ($advisedSection) {
                $subjects = Subject::whereHas('sectionSubjects', function ($q) use ($advisedSection) {
                    $q->where('school_year_section_id', $advisedSection->id);
                })->get();

                $enrollments = StudentEnrollment::where('school_year_section_id', $advisedSection->id)
                    ->with([
                        'student.user',
                        'student.achievements',
                        'student.parents.user',
                        'grades' => function ($q) {
                            if ($this->selectedQuarterId !== 'all') {
                                $q->where('quarter_id', $this->selectedQuarterId);
                            }
                        },
                        'grades.sectionSubject.subject',
                        'grades.quarter'
                    ])
                    ->get()
                    ->sortBy('student.last_name');

                // 1. Determine the maximum grade per subject in this advisory section
                // to identify who is the top performer in each subject
                $subjectMax = [];
                foreach ($enrollments as $enr) {
                    $qGrades = $enr->grades;
                    foreach ($qGrades as $g) {
                        $sub = $g->sectionSubject?->subject?->subject_name;
                        if ($sub && $g->grade !== null) {
                            $val = (float)$g->grade;
                            if (!isset($subjectMax[$sub]) || $val > $subjectMax[$sub]) {
                                $subjectMax[$sub] = $val;
                            }
                        }
                    }
                }

                // 2. Compute honors and "Best in [Subject]" achievers
                foreach ($enrollments as $enr) {
                    $grades = $enr->grades;
                    if ($grades->isEmpty()) continue;

                    $avg = $grades->avg('grade');
                    $min = $grades->min('grade');
                    $honorTitle = null;

                    // DepEd General Academic Excellence Honors
                    if ($avg >= 98.00 && $min >= 85.00) {
                        $honorTitle = 'With Highest Honors';
                    } elseif ($avg >= 95.00 && $min >= 85.00) {
                        $honorTitle = 'With High Honors';
                    } elseif ($avg >= 90.00 && $min >= 85.00) {
                        $honorTitle = 'With Honors';
                    }

                    // Best in Subject Awards
                    // Qualified if grade >= 95.00 OR (grade is highest in section AND grade >= 90.00)
                    $bestSubjects = [];
                    foreach ($grades as $g) {
                        $sub = $g->sectionSubject?->subject?->subject_name;
                        if ($sub && $g->grade !== null) {
                            $val = (float)$g->grade;
                            $isTopInSection = isset($subjectMax[$sub]) && $val >= $subjectMax[$sub] && $val >= 90.00;
                            if ($val >= 95.00 || $isTopInSection) {
                                $bestSubjects[$sub] = [
                                    'subject_name' => $sub,
                                    'award_title' => "Best in {$sub}",
                                    'grade' => $val,
                                    'is_top' => $isTopInSection,
                                ];
                            }
                        }
                    }

                    // Registered achievements from DB
                    $dbAchievements = $enr->student?->achievements?->pluck('title')->toArray() ?? [];

                    // Consolidate full award list for this student
                    $awards = [];
                    if ($honorTitle) {
                        $awards[] = [
                            'type' => 'honor',
                            'title' => $honorTitle,
                            'awarded' => in_array($honorTitle, $dbAchievements),
                        ];
                    }
                    foreach ($bestSubjects as $bs) {
                        $awards[] = [
                            'type' => 'subject',
                            'title' => $bs['award_title'],
                            'subject' => $bs['subject_name'],
                            'grade' => $bs['grade'],
                            'is_top' => $bs['is_top'],
                            'awarded' => in_array($bs['award_title'], $dbAchievements),
                        ];
                    }
                    foreach ($dbAchievements as $dba) {
                        if (!collect($awards)->contains('title', $dba)) {
                            $awards[] = [
                                'type' => 'citation',
                                'title' => $dba,
                                'awarded' => true,
                            ];
                        }
                    }

                    // Filter check
                    if ($this->filterAchieverCategory !== 'all') {
                        if ($this->filterAchieverCategory === 'honors' && !$honorTitle) continue;
                        if ($this->filterAchieverCategory === 'highest' && $honorTitle !== 'With Highest Honors') continue;
                        if ($this->filterAchieverCategory === 'high' && $honorTitle !== 'With High Honors') continue;
                        if ($this->filterAchieverCategory === 'with_honors' && $honorTitle !== 'With Honors') continue;
                        if ($this->filterAchieverCategory === 'best_subjects' && empty($bestSubjects)) continue;
                        if (str_starts_with($this->filterAchieverCategory, 'best_')) {
                            $targetSub = substr($this->filterAchieverCategory, 5);
                            if (!isset($bestSubjects[$targetSub])) continue;
                        }
                    }

                    if ($honorTitle || !empty($bestSubjects) || !empty($dbAchievements)) {
                        $candidates[] = [
                            'enrollment_id' => $enr->id,
                            'student' => $enr->student,
                            'average' => $avg,
                            'lowest' => $min,
                            'title' => $honorTitle,
                            'best_subjects' => $bestSubjects,
                            'awards' => $awards,
                        ];
                    }
                }

                // Sort candidates by average descending
                usort($candidates, fn($a, $b) => $b['average'] <=> $a['average']);
            }
        }

        // Available parents for linking modal
        $parentsQuery = ParentModel::with('user');
        if (!empty($this->parentSearch)) {
            $parentsQuery->where(function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->parentSearch . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->parentSearch . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('login_id', 'LIKE', '%' . $this->parentSearch . '%'));
            });
        }
        $availableParents = $parentsQuery->orderBy('last_name')->take(20)->get();

        return view('livewire.teacher.advisory-management', [
            'advisedSection' => $advisedSection,
            'enrollments' => $enrollments,
            'quarters' => $quarters,
            'subjects' => $subjects,
            'candidates' => $candidates,
            'availableParents' => $availableParents,
            'targetStudent' => $targetStudent,
            'activeSy' => $activeSy,
        ])->layout('layouts.teacher', [
            'title' => 'My Advisee Section',
            'subtitle' => 'Official class advisory management, consolidated grades, and academic achievers'
        ]);
    }
}
