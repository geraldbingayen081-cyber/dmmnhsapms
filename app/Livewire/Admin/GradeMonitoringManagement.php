<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class GradeMonitoringManagement extends Component
{
    use WithPagination;

    // Filters
    public $search = '';
    public $filterSchoolYearId = 'all';
    public $filterQuarterId = 'all';
    public $filterYearLevelId = 'all';
    public $filterSectionId = 'all';
    public $filterSubjectId = 'all';
    public $filterTeacherId = 'all';
    public $filterStatus = 'all';

    // Edit Modal Fields
    public $showEditModal = false;
    public $editGradeId = null;
    public $editStudentName = '';
    public $editStudentNumber = '';
    public $editSubjectCode = '';
    public $editSubjectName = '';
    public $editQuarterName = '';
    public $editGradeValue = '';
    public $editRemarks = '';
    public $editStatus = 'approved';

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->filterSchoolYearId = $activeSy->id;
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->filterQuarterId = $currentQuarter->id;
            }
        }
    }

    public function openEditGradeModal($id)
    {
        $this->resetValidation();
        $grade = Grade::with([
            'studentEnrollment.student',
            'sectionSubject.subject',
            'quarter'
        ])->findOrFail($id);

        $this->editGradeId = $grade->id;
        $this->editStudentName = $grade->studentEnrollment?->student?->full_name ?? 'N/A';
        $this->editStudentNumber = $grade->studentEnrollment?->student?->student_number ?? 'N/A';
        $this->editSubjectCode = $grade->sectionSubject?->subject?->subject_code ?? 'N/A';
        $this->editSubjectName = $grade->sectionSubject?->subject?->subject_name ?? 'N/A';
        $this->editQuarterName = $grade->quarter?->name ?? 'N/A';
        $this->editGradeValue = number_format($grade->grade, 2, '.', '');
        $this->editRemarks = (float)$grade->grade >= 75.00 ? 'PASSED' : 'FAILED';
        $this->editStatus = $grade->status ?? 'approved';

        $this->showEditModal = true;
    }

    public function updatedEditGradeValue($value)
    {
        if (is_numeric($value)) {
            $this->editRemarks = (float)$value >= 75.00 ? 'PASSED' : 'FAILED';
        } else {
            $this->editRemarks = '';
        }
    }

    public function updateGrade()
    {
        $this->validate([
            'editGradeValue' => 'required|numeric|min:60|max:99',
            'editStatus' => 'required|in:draft,approved',
        ], [
            'editGradeValue.min' => 'Grade rating must be at least 60.00.',
            'editGradeValue.max' => 'Grade rating cannot exceed 99.00 (maximum allowed is 99).',
        ]);

        $grade = Grade::with([
            'studentEnrollment.student',
            'sectionSubject.subject'
        ])->findOrFail($this->editGradeId);

        $oldVal = number_format($grade->grade, 2);
        $newVal = number_format((float)$this->editGradeValue, 2);
        $autoRemarks = (float)$this->editGradeValue >= 75.00 ? 'PASSED' : 'FAILED';
        $this->editRemarks = $autoRemarks;

        DB::transaction(function () use ($grade, $newVal, $oldVal, $autoRemarks) {
            $grade->update([
                'grade' => (float)$this->editGradeValue,
                'remarks' => $autoRemarks,
                'status' => $this->editStatus,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Admin Updated Student Grade',
                'subject_type' => Grade::class,
                'subject_id' => $grade->id,
                'description' => "Admin updated grade for student {$grade->studentEnrollment?->student?->full_name} ({$grade->studentEnrollment?->student?->student_number}) in {$grade->sectionSubject?->subject?->subject_code} from {$oldVal} to {$newVal} (Remarks: {$autoRemarks}).",
            ]);
        });

        $this->showEditModal = false;
        session()->flash('message', "Student grade rating updated successfully from {$oldVal} to {$newVal} ({$autoRemarks})!");
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function approveGrade($id)
    {
        $grade = Grade::with(['studentEnrollment.student', 'sectionSubject.subject'])->findOrFail($id);

        DB::transaction(function () use ($grade) {
            $grade->update(['status' => 'approved']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Approved Grade',
                'subject_type' => Grade::class,
                'subject_id' => $grade->id,
                'description' => "Approved grade {$grade->grade} for student {$grade->studentEnrollment?->student?->full_name} in {$grade->sectionSubject?->subject?->subject_code}.",
            ]);
        });

        session()->flash('message', "Grade approved successfully!");
    }

    public function approveFilteredGrades()
    {
        DB::transaction(function () {
            Grade::where('status', 'draft')->update(['status' => 'approved']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Batch Approved Grades',
                'subject_type' => Grade::class,
                'subject_id' => 0,
                'description' => "Batch approved all pending draft subject grades.",
            ]);
        });

        session()->flash('message', "All pending draft grades approved successfully!");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = $this->filterSchoolYearId !== 'all' ? Quarter::where('school_year_id', $this->filterSchoolYearId)->orderBy('sort_order')->get() : Quarter::orderBy('sort_order')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = Section::all();
        $subjects = Subject::all();
        $teachers = Teacher::with('user')->get();

        $query = Grade::with([
            'studentEnrollment.student.user',
            'studentEnrollment.schoolYearSection.yearLevel',
            'studentEnrollment.schoolYearSection.section',
            'studentEnrollment.schoolYearSection.schoolYear',
            'sectionSubject.subject',
            'sectionSubject.teacher.user',
            'quarter'
        ]);

        if (!empty($this->search)) {
            $query->whereHas('studentEnrollment.student', function ($q) {
                $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('student_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filterSchoolYearId !== 'all') {
            $query->whereHas('studentEnrollment.schoolYearSection', fn($q) => $q->where('school_year_id', $this->filterSchoolYearId));
        }

        if ($this->filterQuarterId !== 'all') {
            $query->where('quarter_id', $this->filterQuarterId);
        }

        if ($this->filterYearLevelId !== 'all') {
            $query->whereHas('studentEnrollment.schoolYearSection', fn($q) => $q->where('year_level_id', $this->filterYearLevelId));
        }

        if ($this->filterSectionId !== 'all') {
            $query->whereHas('studentEnrollment.schoolYearSection', fn($q) => $q->where('section_id', $this->filterSectionId));
        }

        if ($this->filterSubjectId !== 'all') {
            $query->whereHas('sectionSubject', fn($q) => $q->where('subject_id', $this->filterSubjectId));
        }

        if ($this->filterTeacherId !== 'all') {
            $query->whereHas('sectionSubject', fn($q) => $q->where('teacher_id', $this->filterTeacherId));
        }

        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'passed') {
                $query->where('grade', '>=', 75.00);
            } elseif ($this->filterStatus === 'failed') {
                $query->where('grade', '<', 75.00);
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        return view('livewire.admin.grade-monitoring-management', [
            'grades' => $query->orderBy('created_at', 'desc')->paginate(12),
            'schoolYears' => $schoolYears,
            'quarters' => $quarters,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'subjects' => $subjects,
            'teachers' => $teachers,
        ])->layout('layouts.admin', ['title' => 'Grade Monitoring & Review', 'subtitle' => 'Monitor, review, edit, and approve quarterly subject grades across JHS sections']);
    }
}
