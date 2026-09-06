<?php

namespace App\Livewire\Teacher;

use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SectionSubject;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use App\Services\AcademicContextService;
use Livewire\Component;

class SubjectClasses extends Component
{
    public $search = '';
    public $filterYearLevelId = 'all';

    public function render()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $activeSy = AcademicContextService::getActiveSchoolYear();
        $quarters = $activeSy ? Quarter::where('school_year_id', $activeSy->id)->orderBy('sort_order')->get() : collect();

        $query = SectionSubject::where('teacher_id', $teacher?->id)
            ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy?->id))
            ->with(['schoolYearSection.section', 'schoolYearSection.yearLevel', 'subject']);

        if ($this->filterYearLevelId !== 'all') {
            $query->whereHas('schoolYearSection', fn($q) => $q->where('year_level_id', $this->filterYearLevelId));
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('subject', function ($sq) {
                    $sq->where('subject_name', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('subject_code', 'LIKE', '%' . $this->search . '%');
                })->orWhereHas('schoolYearSection.section', function ($sq) {
                    $sq->where('name', 'LIKE', '%' . $this->search . '%');
                });
            });
        }

        $classes = $query->get()->map(function ($load) use ($quarters) {
            $enrollmentsCount = StudentEnrollment::where('school_year_section_id', $load->school_year_section_id)->count();

            // Check grade counts per quarter
            $quarterStats = [];
            foreach ($quarters as $q) {
                $encodedCount = \App\Models\Grade::where('section_subject_id', $load->id)
                    ->where('quarter_id', $q->id)
                    ->whereNotNull('grade')
                    ->count();

                $quarterStats[$q->id] = [
                    'quarter' => $q->short_name,
                    'encoded' => $encodedCount,
                    'total' => $enrollmentsCount,
                    'is_complete' => $enrollmentsCount > 0 && $encodedCount >= $enrollmentsCount,
                ];
            }

            $load->enrollments_count = $enrollmentsCount;
            $load->quarter_stats = $quarterStats;
            return $load;
        });

        return view('livewire.teacher.subject-classes', [
            'classes' => $classes,
            'yearLevels' => YearLevel::orderBy('sort_order')->get(),
            'activeSy' => $activeSy,
            'quarters' => $quarters,
        ])->layout('layouts.teacher', [
            'title' => 'My Subject Classes & Loads',
            'subtitle' => 'Official teaching assignments and student loads for SY ' . ($activeSy?->school_year ?? 'Current')
        ]);
    }
}
