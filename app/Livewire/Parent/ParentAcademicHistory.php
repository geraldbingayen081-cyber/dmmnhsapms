<?php

namespace App\Livewire\Parent;

use App\Models\Student;
use App\Services\AcademicHistoryService;
use Livewire\Component;

class ParentAcademicHistory extends Component
{
    public ?int $selectedStudentId = null;

    public ?string $selectedGradeLevel = null;

    public function mount()
    {
        $parent = auth()->user()->parent;
        if ($parent) {
            $firstChild = $parent->students()->first();
            if ($firstChild) {
                $this->selectedStudentId = $firstChild->id;
                $this->initGradeLevelForChild($firstChild->id);
            }
        }
    }

    public function selectChild(int $studentId)
    {
        $parent = auth()->user()->parent;
        if (! $parent) {
            abort(403, 'Unauthorized access.');
        }

        // Strictly verify that the student is linked to this parent
        $isLinked = $parent->students()->where('students.id', $studentId)->exists();
        if (! $isLinked) {
            abort(403, 'Unauthorized access to student academic history.');
        }

        $this->selectedStudentId = $studentId;
        $this->initGradeLevelForChild($studentId);
    }

    public function selectGradeLevel(string $code)
    {
        if (in_array($code, ['G7', 'G8', 'G9', 'G10'])) {
            $this->selectedGradeLevel = $code;
        }
    }

    protected function initGradeLevelForChild(int $studentId): void
    {
        $history = AcademicHistoryService::getStudentHistory($studentId);
        $firstAvailable = collect($history['year_levels'] ?? [])
            ->where('has_records', true)
            ->first();

        $this->selectedGradeLevel = $firstAvailable ? $firstAvailable['code'] : 'G7';
    }

    public function render()
    {
        $parent = auth()->user()->parent;
        $children = collect();
        $selectedStudent = null;
        $historyData = [
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

        if ($parent) {
            $children = $parent->students()
                ->with(['enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section'])
                ->get();

            if ($children->isNotEmpty()) {
                // Ensure selected student belongs to this parent
                if (! $this->selectedStudentId || ! $children->contains('id', (int) $this->selectedStudentId)) {
                    $this->selectedStudentId = $children->first()->id;
                }

                $selectedStudent = $children->firstWhere('id', (int) $this->selectedStudentId);

                if ($selectedStudent) {
                    $historyData = AcademicHistoryService::getStudentHistory($selectedStudent->id);
                }
            }
        }

        $yearLevels = $historyData['year_levels'] ?? [];
        $selectedData = $yearLevels[$this->selectedGradeLevel] ?? null;

        return view('livewire.parent.parent-academic-history', [
            'parent' => $parent,
            'children' => $children,
            'selectedStudent' => $selectedStudent,
            'historyData' => $historyData,
            'yearLevels' => $yearLevels,
            'selectedData' => $selectedData,
            'summary' => $historyData['summary'] ?? [],
        ])->layout('layouts.parent', [
            'title' => 'Student Academic History',
            'subtitle' => 'Permanent Academic Record & Grade History (Grade 7 – Grade 10)',
        ]);
    }
}
