<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StudentEnrollmentManagement extends Component
{
    use WithPagination;

    // Filters
    public $search = '';
    public $filterSchoolYearId = 'all';
    public $filterYearLevelId = 'all';
    public $filterSectionId = 'all';
    public $filterStatus = 'all';

    // Modal Controls
    public $showEnrollModal = false;
    public $showTransitionModal = false;
    public $showAutoEnrollModal = false;
    public $showBatchTransitionModal = false;

    // Enroll Form Fields
    public $student_id = '';
    public $school_year_section_id = '';
    public $enrollment_date = '';
    public $studentSearch = '';

    // Auto-Enroll Form Fields
    public $autoEnrollSchoolYearId = '';
    public $autoEnrollYearLevelId = 'all';

    // Batch Transition Form Fields
    public $batchSourceSchoolYearId = '';
    public $batchTargetSchoolYearId = '';

    // Transition Form Fields (Promote / Retain / Transfer)
    public $enrollmentId = null;
    public $transitionType = 'promote'; // promote, retain, transfer
    public $target_school_year_section_id = '';
    public $selectedEnrollment = null;

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->filterSchoolYearId = $activeSy->id;
            $this->autoEnrollSchoolYearId = $activeSy->id;
        }
        $this->enrollment_date = now()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAutoEnrollModal()
    {
        $this->resetValidation();
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $this->autoEnrollSchoolYearId = $activeSy ? $activeSy->id : '';
        $this->autoEnrollYearLevelId = 'all';
        $this->showAutoEnrollModal = true;
    }

    public function processAutoEnroll()
    {
        $this->validate([
            'autoEnrollSchoolYearId' => 'required|exists:school_years,id',
        ]);

        $syId = $this->autoEnrollSchoolYearId;

        // Fetch active sections for target school year
        $sectionsQuery = SchoolYearSection::where('school_year_id', $syId)->where('status', 'active');
        if ($this->autoEnrollYearLevelId !== 'all') {
            $sectionsQuery->where('year_level_id', $this->autoEnrollYearLevelId);
        }
        $availableSections = $sectionsQuery->get();

        if ($availableSections->isEmpty()) {
            $this->addError('autoEnrollSchoolYearId', 'No active sections found for the selected school year. Please configure active sections first.');
            return;
        }

        // Find unenrolled students for this school year
        $unenrolledStudents = Student::whereDoesntHave('enrollments', function ($q) use ($syId) {
            $q->whereHas('schoolYearSection', fn($sq) => $sq->where('school_year_id', $syId))
              ->where('status', 'enrolled');
        })->get();

        if ($unenrolledStudents->isEmpty()) {
            $this->addError('autoEnrollSchoolYearId', 'All registered students are already enrolled in this school year.');
            return;
        }

        $enrolledCount = 0;

        DB::transaction(function () use ($unenrolledStudents, $availableSections, &$enrolledCount, $syId) {
            foreach ($unenrolledStudents as $student) {
                // Randomly pick an active section load
                $targetSection = $availableSections->random();

                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'school_year_section_id' => $targetSection->id,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'enrolled',
                ]);
                $enrolledCount++;
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Batch Auto-Enrolled Students',
                'subject_type' => StudentEnrollment::class,
                'subject_id' => null,
                'description' => "Automatically auto-enrolled {$enrolledCount} unenrolled students into active section loads for SY ID {$syId}.",
            ]);
        });

        $this->showAutoEnrollModal = false;
        session()->flash('message', "Successfully auto-enrolled {$enrolledCount} student(s) into active section loads!");
    }

    public function openBatchTransitionModal()
    {
        $this->resetValidation();
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $this->batchSourceSchoolYearId = $activeSy ? $activeSy->id : '';
        $nextSy = SchoolYear::where('id', '>', $activeSy?->id ?? 0)->orderBy('id')->first();
        $this->batchTargetSchoolYearId = $nextSy ? $nextSy->id : '';

        $this->showBatchTransitionModal = true;
    }

    public function processBatchTransition()
    {
        $this->validate([
            'batchSourceSchoolYearId' => 'required|exists:school_years,id',
            'batchTargetSchoolYearId' => 'required|exists:school_years,id|different:batchSourceSchoolYearId',
        ], [
            'batchTargetSchoolYearId.different' => 'Target School Year must be different from Source School Year.',
        ]);

        $sourceSyId = $this->batchSourceSchoolYearId;
        $targetSyId = $this->batchTargetSchoolYearId;

        // Fetch active enrollments in source school year
        $sourceEnrollments = StudentEnrollment::with([
            'student',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section'
        ])
        ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sourceSyId))
        ->where('status', 'enrolled')
        ->get();

        if ($sourceEnrollments->isEmpty()) {
            $this->addError('batchSourceSchoolYearId', 'No active enrolled students found in the selected source school year.');
            return;
        }

        // Fetch active sections for target school year grouped by year_level_id
        $targetSections = SchoolYearSection::where('school_year_id', $targetSyId)
            ->where('status', 'active')
            ->get()
            ->groupBy('year_level_id');

        if ($targetSections->isEmpty()) {
            $this->addError('batchTargetSchoolYearId', 'No active sections configured in the target school year. Please set up target sections first.');
            return;
        }

        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $yearLevelOrderMap = $yearLevels->pluck('sort_order', 'id')->toArray();
        $orderToYearLevelMap = $yearLevels->pluck('id', 'sort_order')->toArray();

        $promotedCount = 0;
        $retainedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $sourceEnrollments,
            $targetSections,
            $yearLevelOrderMap,
            $orderToYearLevelMap,
            $targetSyId,
            &$promotedCount,
            &$retainedCount,
            &$skippedCount
        ) {
            foreach ($sourceEnrollments as $enrollment) {
                $student = $enrollment->student;
                $currentYlId = $enrollment->schoolYearSection?->year_level_id;
                $currentOrder = $yearLevelOrderMap[$currentYlId] ?? 1;

                $eval = $this->evaluateAcademicPerformance($enrollment);
                $isPassed = $eval['has_grades'] ? $eval['is_passed'] : true;

                if ($isPassed) {
                    $nextOrder = $currentOrder + 1;
                    $targetYlId = $orderToYearLevelMap[$nextOrder] ?? null;

                    if (!$targetYlId) {
                        // Grade 10 Completion
                        $enrollment->update(['status' => 'promoted']);
                        $promotedCount++;
                        continue;
                    }

                    $availableTargetSections = $targetSections->get($targetYlId);
                    if (!$availableTargetSections || $availableTargetSections->isEmpty()) {
                        $skippedCount++;
                        continue;
                    }

                    $targetSection = $availableTargetSections->random();

                    $enrollment->update(['status' => 'promoted']);
                    StudentEnrollment::create([
                        'student_id' => $student->id,
                        'school_year_section_id' => $targetSection->id,
                        'enrollment_date' => now()->toDateString(),
                        'status' => 'enrolled',
                    ]);
                    $promotedCount++;
                } else {
                    // Retention in same Grade Level
                    $availableTargetSections = $targetSections->get($currentYlId);
                    if (!$availableTargetSections || $availableTargetSections->isEmpty()) {
                        $skippedCount++;
                        continue;
                    }

                    $targetSection = $availableTargetSections->random();

                    $enrollment->update(['status' => 'retained']);
                    StudentEnrollment::create([
                        'student_id' => $student->id,
                        'school_year_section_id' => $targetSection->id,
                        'enrollment_date' => now()->toDateString(),
                        'status' => 'enrolled',
                    ]);
                    $retainedCount++;
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Batch Auto-Promoted/Retained Students',
                'subject_type' => StudentEnrollment::class,
                'subject_id' => null,
                'description' => "Processed batch academic transitions from Source SY ID {$this->batchSourceSchoolYearId} to Target SY ID {$targetSyId}: {$promotedCount} Promoted, {$retainedCount} Retained.",
            ]);
        });

        $this->showBatchTransitionModal = false;
        session()->flash('message', "Batch transition completed! {$promotedCount} student(s) Promoted to next Grade Level, {$retainedCount} student(s) Retained.");
    }

    public function openEnrollModal()
    {
        $this->resetValidation();
        $this->reset(['student_id', 'school_year_section_id', 'studentSearch']);
        $this->enrollment_date = now()->toDateString();
        $this->showEnrollModal = true;
    }

    public function enrollStudent()
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
            'school_year_section_id' => 'required|exists:school_year_sections,id',
            'enrollment_date' => 'required|date',
        ]);

        $sys = SchoolYearSection::with(['schoolYear', 'yearLevel', 'section'])->findOrFail($this->school_year_section_id);
        $student = Student::findOrFail($this->student_id);

        // Check if student already has an active enrollment in this school year
        $existing = StudentEnrollment::where('student_id', $this->student_id)
            ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sys->school_year_id))
            ->where('status', 'enrolled')
            ->exists();

        if ($existing) {
            $this->addError('student_id', "This student already has an active enrollment in SY {$sys->schoolYear?->school_year}.");
            return;
        }

        DB::transaction(function () use ($sys, $student) {
            $enrollment = StudentEnrollment::create([
                'student_id' => $this->student_id,
                'school_year_section_id' => $this->school_year_section_id,
                'enrollment_date' => $this->enrollment_date,
                'status' => 'enrolled',
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Enrolled Student',
                'subject_type' => StudentEnrollment::class,
                'subject_id' => $enrollment->id,
                'description' => "Enrolled student {$student->full_name} ({$student->student_number}) into {$sys->yearLevel?->name} - {$sys->section?->name} for SY {$sys->schoolYear?->school_year}.",
            ]);
        });

        $this->showEnrollModal = false;
        session()->flash('message', "Student {$student->full_name} successfully enrolled!");
    }

    public $studentEval = null;

    public function openTransitionModal($enrollmentId, $type = 'promote')
    {
        $this->resetValidation();
        $this->selectedEnrollment = StudentEnrollment::with([
            'student',
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section'
        ])->findOrFail($enrollmentId);

        $this->enrollmentId = $this->selectedEnrollment->id;
        $this->transitionType = $type;
        $this->target_school_year_section_id = '';
        $this->studentEval = $this->evaluateAcademicPerformance($this->selectedEnrollment);
        $this->showTransitionModal = true;
    }

    private function evaluateAcademicPerformance(StudentEnrollment $enrollment): array
    {
        $grades = \App\Models\Grade::where('student_enrollment_id', $enrollment->id)->get();
        $subjectGrades = $grades->groupBy('section_subject_id');

        $failedSubjectsCount = 0;
        $totalGradesSum = 0;
        $evaluatedSubjectsCount = 0;

        foreach ($subjectGrades as $sectionSubjectId => $gList) {
            $avg = $gList->avg('grade');
            if ($avg !== null && $avg > 0) {
                $totalGradesSum += $avg;
                $evaluatedSubjectsCount++;
                if ($avg < 75.00) {
                    $failedSubjectsCount++;
                }
            }
        }

        $generalAverage = $evaluatedSubjectsCount > 0 ? round($totalGradesSum / $evaluatedSubjectsCount, 2) : null;
        $isPassed = ($generalAverage !== null && $generalAverage >= 75.00 && $failedSubjectsCount < 3);

        return [
            'has_grades' => $evaluatedSubjectsCount > 0,
            'evaluated_subjects' => $evaluatedSubjectsCount,
            'general_average' => $generalAverage,
            'failed_subjects_count' => $failedSubjectsCount,
            'is_passed' => $isPassed,
        ];
    }

    public function processTransition()
    {
        $currentEnrollment = StudentEnrollment::with([
            'student',
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section'
        ])->findOrFail($this->enrollmentId);

        $student = $currentEnrollment->student;
        $currentYearLevelOrder = $currentEnrollment->schoolYearSection?->yearLevel?->sort_order ?? 1;
        $currentYearLevelName = $currentEnrollment->schoolYearSection?->yearLevel?->name ?? 'Grade Level';

        if ($this->transitionType !== 'transfer') {
            $this->validate([
                'target_school_year_section_id' => 'required|exists:school_year_sections,id',
            ]);

            $targetSys = SchoolYearSection::with(['schoolYear', 'yearLevel', 'section'])->findOrFail($this->target_school_year_section_id);
            $targetYearLevelOrder = $targetSys->yearLevel?->sort_order ?? 1;

            $eval = $this->evaluateAcademicPerformance($currentEnrollment);

            if (!$eval['has_grades']) {
                $this->addError('target_school_year_section_id', "Cannot process {$this->transitionType}. No academic grades recorded for {$student->full_name} in SY {$currentEnrollment->schoolYearSection?->schoolYear?->school_year}. Grades are required to evaluate promotion or retention.");
                return;
            }

            if ($this->transitionType === 'promote') {
                // Check DepEd Passing Requirements for Promotion
                if (!$eval['is_passed']) {
                    $avgStr = $eval['general_average'] ? number_format($eval['general_average'], 2) : 'N/A';
                    $this->addError('target_school_year_section_id', "Cannot promote student {$student->full_name}. General Average is {$avgStr} (< 75.00) or student has {$eval['failed_subjects_count']} failing subject(s). Student is NOT eligible for Promotion.");
                    return;
                }

                // Check Grade Level progression (Target Year Level must be higher than current Year Level)
                if ($targetYearLevelOrder <= $currentYearLevelOrder) {
                    $this->addError('target_school_year_section_id', "Invalid promotion target section. Promotion target section must be for a higher grade level than {$currentYearLevelName}.");
                    return;
                }
            } elseif ($this->transitionType === 'retain') {
                // Check DepEd Failing Requirements for Retention
                if ($eval['is_passed']) {
                    $avgStr = number_format($eval['general_average'], 2);
                    $this->addError('target_school_year_section_id', "Cannot retain student {$student->full_name}. Student has passed all learning areas with a General Average of {$avgStr} (>= 75.00). Student is eligible for Promotion, not Retention.");
                    return;
                }

                // Check Grade Level consistency (Target Year Level must be the same Grade Level)
                if ($targetYearLevelOrder !== $currentYearLevelOrder) {
                    $this->addError('target_school_year_section_id', "Invalid retention target section. Retention target section must be in the same grade level ({$currentYearLevelName}).");
                    return;
                }
            }
        }

        DB::transaction(function () use ($currentEnrollment, $student) {
            if ($this->transitionType === 'transfer') {
                $currentEnrollment->update(['status' => 'transferred']);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Transferred Student',
                    'subject_type' => StudentEnrollment::class,
                    'subject_id' => $currentEnrollment->id,
                    'description' => "Marked student {$student->full_name} ({$student->student_number}) enrollment as Transferred.",
                ]);
            } else {
                $targetSys = SchoolYearSection::with(['schoolYear', 'yearLevel', 'section'])->findOrFail($this->target_school_year_section_id);

                $newStatus = ($this->transitionType === 'promote') ? 'promoted' : 'retained';
                $currentEnrollment->update(['status' => $newStatus]);

                // Create new enrollment entry for target school year section
                $newEnrollment = StudentEnrollment::create([
                    'student_id' => $student->id,
                    'school_year_section_id' => $targetSys->id,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'enrolled',
                ]);

                $actionTitle = ucfirst($this->transitionType) . "d Student";
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => $actionTitle,
                    'subject_type' => StudentEnrollment::class,
                    'subject_id' => $newEnrollment->id,
                    'description' => "{$actionTitle} student {$student->full_name} from SY {$currentEnrollment->schoolYearSection?->schoolYear?->school_year} ({$currentEnrollment->schoolYearSection?->section?->name}) to SY {$targetSys->schoolYear?->school_year} ({$targetSys->yearLevel?->name} - {$targetSys->section?->name}).",
                ]);
            }
        });

        $this->showTransitionModal = false;
        session()->flash('message', "Student academic transition successfully processed!");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = Section::all();

        $query = StudentEnrollment::with([
            'student.user',
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section'
        ]);

        if (!empty($this->search)) {
            $query->whereHas('student', function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filterSchoolYearId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $this->filterSchoolYearId));
        }

        if ($this->filterYearLevelId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('year_level_id', $this->filterYearLevelId));
        }

        if ($this->filterSectionId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('section_id', $this->filterSectionId));
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $availableStudents = !empty($this->studentSearch)
            ? Student::with('user')
                ->where('first_name', 'LIKE', '%' . $this->studentSearch . '%')
                ->orWhere('last_name', 'LIKE', '%' . $this->studentSearch . '%')
                ->orWhere('student_number', 'LIKE', '%' . $this->studentSearch . '%')
                ->take(10)->get()
            : Student::with('user')->take(10)->get();

        $availableSectionInstances = SchoolYearSection::with(['schoolYear', 'yearLevel', 'section'])->where('status', 'active')->get();

        return view('livewire.admin.student-enrollment-management', [
            'enrollments' => $query->orderBy('created_at', 'desc')->paginate(10),
            'schoolYears' => $schoolYears,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'availableStudents' => $availableStudents,
            'availableSectionInstances' => $availableSectionInstances,
        ])->layout('layouts.admin', ['title' => 'Student Enrollment & Promotion', 'subtitle' => 'Manage student placement history, promotion, retention, and transfer decisions']);
    }
}
