<?php

namespace App\Livewire\Student;

use App\Services\AcademicHistoryService;
use Livewire\Component;

class StudentAcademicHistory extends Component
{
    public ?string $selectedGradeLevel = null;

    public function mount()
    {
        $student = auth()->user()->student;
        if ($student) {
            $history = AcademicHistoryService::getStudentHistory($student->id);
            // Default to the first available grade level with records, or G7
            $firstAvailable = collect($history['year_levels'] ?? [])
                ->where('has_records', true)
                ->first();

            $this->selectedGradeLevel = $firstAvailable ? $firstAvailable['code'] : 'G7';
        } else {
            $this->selectedGradeLevel = 'G7';
        }
    }

    public function selectGradeLevel(string $code)
    {
        if (in_array($code, ['G7', 'G8', 'G9', 'G10'])) {
            $this->selectedGradeLevel = $code;
        }
    }

    public function render()
    {
        $student = auth()->user()->student;

        $historyData = $student
            ? AcademicHistoryService::getStudentHistory($student->id)
            : [
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

        $yearLevels = $historyData['year_levels'] ?? [];
        $selectedData = $yearLevels[$this->selectedGradeLevel] ?? null;

        return view('livewire.student.student-academic-history', [
            'student' => $student,
            'historyData' => $historyData,
            'yearLevels' => $yearLevels,
            'selectedData' => $selectedData,
            'summary' => $historyData['summary'] ?? [],
        ])->layout('layouts.student', [
            'title' => 'Student Academic History',
            'subtitle' => 'Permanent Academic Record & Grade History (Grade 7 – Grade 10)',
        ]);
    }
}
