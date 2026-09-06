<?php

namespace App\Livewire\Teacher;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GradeEncoding extends Component
{
    public $selectedClassId = null;
    public $selectedQuarterId = null;

    // Keyed by enrollment_id => grade value string (for selected quarter only)
    public $grades = [];
    // Keyed by enrollment_id => remarks string
    public $remarks = [];

    public function mount($classId = null)
    {
        $teacher = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        if ($teacher && $activeSy) {
            $myClasses = SectionSubject::where('teacher_id', $teacher->id)
                ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject'])
                ->get();

            if ($classId && $myClasses->contains('id', (int)$classId)) {
                $this->selectedClassId = (int)$classId;
            } else {
                // Pick first class that has enrolled students
                $preferred = $myClasses->first(fn($c) =>
                    StudentEnrollment::where('school_year_section_id', $c->school_year_section_id)->exists()
                ) ?? $myClasses->first();
                $this->selectedClassId = $preferred?->id;
            }

            // Default to the active quarter, fall back to first quarter
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            $this->selectedQuarterId = $currentQuarter?->id
                ?? Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->first()?->id;
        }

        $this->loadGrades();
    }

    public function updatedSelectedClassId()
    {
        $this->loadGrades();
    }

    public function updatedSelectedQuarterId()
    {
        $this->loadGrades();
    }

    /**
     * Load existing grades from the shared `grades` table for the selected class+quarter.
     * Both admin-entered and teacher-entered grades live here — this is the single source of truth.
     */
    public function loadGrades(): void
    {
        $this->grades = [];
        $this->remarks = [];

        if (!$this->selectedClassId || !$this->selectedQuarterId) {
            return;
        }

        $class = SectionSubject::find($this->selectedClassId);
        if (!$class) {
            return;
        }

        $enrollmentIds = StudentEnrollment::where('school_year_section_id', $class->school_year_section_id)
            ->pluck('id');

        // Fetch existing grades for this class + quarter (shared table — same records admin sees)
        $existing = Grade::where('section_subject_id', $this->selectedClassId)
            ->where('quarter_id', $this->selectedQuarterId)
            ->whereIn('student_enrollment_id', $enrollmentIds)
            ->get()
            ->keyBy('student_enrollment_id');

        foreach ($enrollmentIds as $eid) {
            $g = $existing->get($eid);
            // Display grade with 2 decimal places if it exists, blank if not yet encoded
            $this->grades[$eid]  = $g ? number_format((float)$g->grade, 2, '.', '') : '';
            $this->remarks[$eid] = $g ? ($g->remarks ?? '') : '';
        }
    }

    /**
     * Save grades to the shared `grades` table.
     * Admin can see these immediately in Grade Monitoring because it is the same table.
     * Only permitted when the quarter status is 'active'.
     */
    public function saveGrades(): void
    {
        if (!$this->selectedClassId || !$this->selectedQuarterId) {
            session()->flash('error', 'Please select a valid subject class and quarter period.');
            return;
        }

        $quarter = Quarter::findOrFail($this->selectedQuarterId);

        // Strict admin-policy lock: only 'active' quarters may be encoded
        if ($quarter->status !== 'active') {
            session()->flash(
                'error',
                "Grade Encoding is LOCKED: {$quarter->name} is marked as "
                . strtoupper($quarter->status)
                . " by the administration. You may only encode grades for the currently active quarter."
            );
            return;
        }

        $class = SectionSubject::with([
            'subject',
            'schoolYearSection.section',
            'schoolYearSection.yearLevel',
        ])->findOrFail($this->selectedClassId);

        $user = auth()->user();

        // Validate every non-empty grade value
        $hasErrors = false;
        foreach ($this->grades as $eid => $val) {
            if ($val !== '' && $val !== null) {
                if (!is_numeric($val) || (float)$val < 60.00 || (float)$val > 99.00) {
                    $this->addError("grades.{$eid}", 'Grade must be between 60.00 and 99.00 (maximum allowed is 99).');
                    $hasErrors = true;
                }
            }
        }

        if ($hasErrors) {
            return;
        }

        $savedCount = 0;

        DB::transaction(function () use ($class, $quarter, $user, &$savedCount) {
            foreach ($this->grades as $eid => $val) {
                if ($val !== '' && $val !== null && is_numeric($val)) {
                    $gradeVal = round((float)$val, 2);
                    $autoRem  = $gradeVal >= 75.00 ? 'PASSED' : 'FAILED';
                    $remarks  = !empty($this->remarks[$eid]) ? $this->remarks[$eid] : $autoRem;

                    // updateOrCreate keeps a single canonical record per (enrollment, class, quarter)
                    // Admin Grade Monitoring reads the same rows — changes are immediately reflected.
                    Grade::updateOrCreate(
                        [
                            'student_enrollment_id' => (int)$eid,
                            'section_subject_id'    => $class->id,
                            'quarter_id'            => $quarter->id,
                        ],
                        [
                            'grade'      => $gradeVal,
                            'remarks'    => $remarks,
                            'status'     => 'approved',
                            'created_by' => $user->id,
                        ]
                    );
                    $savedCount++;
                }
            }

            ActivityLog::create([
                'user_id'      => $user->id,
                'action'       => 'Teacher Encoded Subject Grades',
                'subject_type' => SectionSubject::class,
                'subject_id'   => $class->id,
                'description'  => "Teacher saved {$savedCount} grades for {$class->subject?->subject_name}"
                    . " ({$class->schoolYearSection?->yearLevel?->name}"
                    . " - {$class->schoolYearSection?->section?->name})"
                    . " — {$quarter->name}.",
            ]);
        });

        // Reload from DB so the displayed values match the persisted records
        $this->loadGrades();

        session()->flash(
            'message',
            "✅ {$savedCount} grade(s) saved for {$quarter->name}. Changes are now visible in Admin Grade Monitoring."
        );
    }

    public function render()
    {
        $teacher  = auth()->user()->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $myClasses       = collect();
        $quarters        = collect();
        $studentsList    = collect();
        $selectedQuarter = null;
        $isEncodingActive = false;

        // Full Q×Student matrix: [enrollment_id => [quarter_id => grade|null]]
        $gradeMatrix = [];

        // Computed stats for the selected quarter (from the in-memory $grades[] array)
        $stats = [
            'totalStudents' => 0,
            'encodedCount'  => 0,
            'average'       => null,
            'passingRate'   => 0,
            'highest'       => null,
            'lowest'        => null,
            'generalAvg'    => null, // average across all quarters that have data
        ];

        if ($teacher && $activeSy) {
            $myClasses = SectionSubject::where('teacher_id', $teacher->id)
                ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))
                ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject'])
                ->get()
                ->sortBy(fn($l) =>
                    ($l->schoolYearSection?->yearLevel?->sort_order ?? 0)
                    . '-' . ($l->schoolYearSection?->section?->name ?? '')
                    . '-' . ($l->subject?->subject_name ?? '')
                )
                ->values();

            $quarters = Quarter::where('school_year_id', $activeSy->id)
                ->orderBy('sort_order')
                ->get();

            if ($this->selectedQuarterId) {
                $selectedQuarter  = $quarters->firstWhere('id', (int)$this->selectedQuarterId);
                $isEncodingActive = $selectedQuarter?->status === 'active';
            }

            if ($this->selectedClassId) {
                $class = $myClasses->firstWhere('id', (int)$this->selectedClassId);

                if ($class) {
                    $studentsList = StudentEnrollment::where('school_year_section_id', $class->school_year_section_id)
                        ->with(['student'])
                        ->get()
                        ->sortBy(fn($e) => $e->student?->last_name . ' ' . $e->student?->first_name)
                        ->values();

                    $stats['totalStudents'] = $studentsList->count();

                    // Load ALL grades for this class across every quarter (shared table read — same rows admin sees)
                    $allGrades = Grade::where('section_subject_id', (int)$this->selectedClassId)
                        ->whereIn('student_enrollment_id', $studentsList->pluck('id'))
                        ->get();

                    // Pre-index by [enrollment_id][quarter_id] for reliable O(1) string-key lookup.
                    // Using string keys avoids PHP integer/string coercion issues when Blade reads the array.
                    $gradesIndex = [];
                    foreach ($allGrades as $gr) {
                        $gradesIndex[(string)$gr->student_enrollment_id][(string)$gr->quarter_id] = (float)$gr->grade;
                    }

                    // Build Q×Student matrix with explicit string keys
                    foreach ($studentsList as $enr) {
                        $eid = (string)$enr->id;
                        $gradeMatrix[$eid] = [];

                        foreach ($quarters as $q) {
                            $qid = (string)$q->id;

                            // For the actively-selected quarter, prefer the live Livewire $grades[] value
                            // (so the matrix stays in sync while the teacher is typing before saving)
                            if ((int)$q->id === (int)$this->selectedQuarterId
                                && isset($this->grades[$enr->id])
                                && $this->grades[$enr->id] !== '') {
                                $live = is_numeric($this->grades[$enr->id]) ? (float)$this->grades[$enr->id] : null;
                                $gradeMatrix[$eid][$qid] = $live;
                            } else {
                                $gradeMatrix[$eid][$qid] = $gradesIndex[$eid][$qid] ?? null;
                            }
                        }
                    }

                    // Stats for the currently selected quarter
                    $valid = [];
                    foreach ($this->grades as $gVal) {
                        if ($gVal !== '' && $gVal !== null && is_numeric($gVal)) {
                            $valid[] = (float)$gVal;
                        }
                    }

                    $stats['encodedCount'] = count($valid);
                    if (count($valid) > 0) {
                        $stats['average']     = round(array_sum($valid) / count($valid), 2);
                        $stats['highest']     = max($valid);
                        $stats['lowest']      = min($valid);
                        $passing              = count(array_filter($valid, fn($g) => $g >= 75.00));
                        $stats['passingRate'] = round(($passing / count($valid)) * 100, 1);
                    }
                }
            }
        }

        return view('livewire.teacher.grade-encoding', [
            'myClasses'        => $myClasses,
            'quarters'         => $quarters,
            'selectedQuarter'  => $selectedQuarter,
            'isEncodingActive' => $isEncodingActive,
            'studentsList'     => $studentsList,
            'gradeMatrix'      => $gradeMatrix,
            'stats'            => $stats,
            'activeSy'         => $activeSy,
        ])->layout('layouts.teacher', [
            'title'    => 'Grade Encoding Sheet',
            'subtitle' => 'View existing grades from Q1–Q3 and encode marks for the active quarter',
        ]);
    }
}
