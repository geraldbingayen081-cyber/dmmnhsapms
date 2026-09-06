<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SubjectTeacherAssignment extends Component
{
    public $selectedSchoolYearId = null;
    public $selectedSchoolYearSectionId = 'all';

    public $showAssignModal = false;
    public $showAutoAssignModal = false;
    public $sectionSubjectId = null;
    public $school_year_section_id = '';
    public $subject_id = '';
    public $teacher_id = '';

    public $autoAssignSchoolYearId = '';
    public $autoAssignOption = 'all';

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedSchoolYearId = $activeSy->id;
            $this->autoAssignSchoolYearId = $activeSy->id;
        } else {
            $firstSy = SchoolYear::orderBy('school_year', 'desc')->first();
            $this->selectedSchoolYearId = $firstSy?->id;
            $this->autoAssignSchoolYearId = $firstSy?->id;
        }
    }

    public function openAutoAssignModal()
    {
        $this->resetValidation();
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $this->autoAssignSchoolYearId = $activeSy ? $activeSy->id : ($this->selectedSchoolYearId ?: '');
        $this->autoAssignOption = 'all';
        $this->showAutoAssignModal = true;
    }

    public function processAutoAssign()
    {
        $this->validate([
            'autoAssignSchoolYearId' => 'required|exists:school_years,id',
        ]);

        $syId = $this->autoAssignSchoolYearId;
        $sections = SchoolYearSection::where('school_year_id', $syId)->get();

        if ($sections->isEmpty()) {
            $this->addError('autoAssignSchoolYearId', 'No section instances configured for the selected school year. Please configure school year sections first.');
            return;
        }

        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            $this->addError('autoAssignSchoolYearId', 'No DepEd subjects found in the system. Please add core subjects first.');
            return;
        }

        $teachers = Teacher::with('user')->whereHas('user', fn($q) => $q->where('status', 'active'))->get();

        $createdLoadsCount = 0;
        $assignedTeachersCount = 0;

        DB::transaction(function () use ($sections, $subjects, $teachers, &$createdLoadsCount, &$assignedTeachersCount, $syId) {
            $teacherIndex = 0;
            $teachersTotal = $teachers->count();

            foreach ($sections as $sec) {
                foreach ($subjects as $sub) {
                    $ss = SectionSubject::firstOrCreate([
                        'school_year_section_id' => $sec->id,
                        'subject_id' => $sub->id,
                    ]);

                    if ($ss->wasRecentlyCreated) {
                        $createdLoadsCount++;
                    }

                    if (in_array($this->autoAssignOption, ['all', 'teachers_only']) && !$ss->teacher_id && $teachersTotal > 0) {
                        $assignedTeacher = $teachers->get($teacherIndex % $teachersTotal);
                        $ss->update(['teacher_id' => $assignedTeacher->id]);
                        $assignedTeachersCount++;
                        $teacherIndex++;
                    }
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Batch Auto-Assigned Subject Loads',
                'subject_type' => SectionSubject::class,
                'subject_id' => null,
                'description' => "Auto-assigned subject loads for SY ID {$syId}: {$createdLoadsCount} new subject loads created, {$assignedTeachersCount} teachers assigned.",
            ]);
        });

        $this->showAutoAssignModal = false;
        session()->flash('message', "Auto-assignment completed! Created {$createdLoadsCount} subject load(s) and assigned {$assignedTeachersCount} teacher workload(s).");
    }

    public function openAssignModal()
    {
        $this->resetValidation();
        $this->reset(['sectionSubjectId', 'school_year_section_id', 'subject_id', 'teacher_id']);
        $this->showAssignModal = true;
    }

    public function assignSubjectTeacher()
    {
        $this->validate([
            'school_year_section_id' => 'required|exists:school_year_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $exists = SectionSubject::where('school_year_section_id', $this->school_year_section_id)
            ->where('subject_id', $this->subject_id)
            ->where('id', '!=', $this->sectionSubjectId)
            ->exists();

        if ($exists) {
            $this->addError('subject_id', 'This subject is already assigned to the selected section instance.');
            return;
        }

        $sys = SchoolYearSection::with(['section', 'yearLevel', 'schoolYear'])->findOrFail($this->school_year_section_id);
        $sub = Subject::findOrFail($this->subject_id);
        $teacher = $this->teacher_id ? Teacher::find($this->teacher_id) : null;

        DB::transaction(function () use ($sys, $sub, $teacher) {
            $ss = SectionSubject::updateOrCreate(
                [
                    'school_year_section_id' => $this->school_year_section_id,
                    'subject_id' => $this->subject_id,
                ],
                [
                    'teacher_id' => $this->teacher_id ?: null,
                ]
            );

            $teacherName = $teacher ? "{$teacher->full_name} ({$teacher->employee_number})" : 'Unassigned';

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Assigned Subject Teacher',
                'subject_type' => SectionSubject::class,
                'subject_id' => $ss->id,
                'description' => "Assigned subject {$sub->subject_code} ({$sub->subject_name}) in section {$sys->yearLevel?->name} - {$sys->section?->name} to teacher {$teacherName}.",
            ]);
        });

        $this->showAssignModal = false;
        session()->flash('message', "Subject teacher load successfully assigned!");
    }

    public function openEditTeacherModal($sectionSubjectId)
    {
        $this->resetValidation();
        $ss = SectionSubject::findOrFail($sectionSubjectId);
        $this->sectionSubjectId = $ss->id;
        $this->school_year_section_id = $ss->school_year_section_id;
        $this->subject_id = $ss->subject_id;
        $this->teacher_id = $ss->teacher_id ?: '';

        $this->showAssignModal = true;
    }

    public function removeSubjectLoad($sectionSubjectId)
    {
        $ss = SectionSubject::with(['subject', 'schoolYearSection.section', 'schoolYearSection.yearLevel'])->findOrFail($sectionSubjectId);

        DB::transaction(function () use ($ss) {
            $subName = $ss->subject?->subject_name;
            $secName = $ss->schoolYearSection?->section?->name;
            $ss->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Removed Subject Load',
                'subject_type' => SectionSubject::class,
                'subject_id' => $sectionSubjectId,
                'description' => "Removed subject load {$subName} from section {$secName}.",
            ]);
        });

        session()->flash('message', "Subject load removed from section.");
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        $sections = $this->selectedSchoolYearId 
            ? SchoolYearSection::with(['section', 'yearLevel'])
                ->where('school_year_id', $this->selectedSchoolYearId)
                ->get() 
            : collect();

        $query = SectionSubject::with([
            'schoolYearSection.schoolYear',
            'schoolYearSection.yearLevel',
            'schoolYearSection.section',
            'subject',
            'teacher.user'
        ])->whereHas('schoolYearSection', function ($q) {
            if ($this->selectedSchoolYearId) {
                $q->where('school_year_id', $this->selectedSchoolYearId);
            }
            if ($this->selectedSchoolYearSectionId !== 'all') {
                $q->where('id', $this->selectedSchoolYearSectionId);
            }
        });

        $subjects = Subject::all();
        $teachers = Teacher::with('user')->get();

        return view('livewire.admin.subject-teacher-assignment', [
            'schoolYears' => $schoolYears,
            'sections' => $sections,
            'sectionSubjects' => $query->get(),
            'subjects' => $subjects,
            'teachers' => $teachers,
        ])->layout('layouts.admin', ['title' => 'Subject Teacher Assignment', 'subtitle' => 'Assign subject teachers to section learning loads (section_subjects)']);
    }
}
