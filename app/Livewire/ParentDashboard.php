<?php

namespace App\Livewire;

use App\Models\ParentModel;
use App\Models\StudentEnrollment;
use App\Services\AcademicContextService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ParentDashboard extends Component
{
    public $selectedStudentId = null;

    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->isParent() && $user->parent) {
            $firstChild = $user->parent->students()->first();
            if ($firstChild) {
                $this->selectedStudentId = $firstChild->id;
            }
        }
    }

    public function selectChild($studentId)
    {
        $this->selectedStudentId = $studentId;
    }

    public function render()
    {
        $user = Auth::user();
        $sy = AcademicContextService::getActiveSchoolYear();
        $parent = ($user && $user->isParent()) ? $user->parent : null;

        $children = $parent ? $parent->students : collect();
        $activeChild = $this->selectedStudentId ? $children->firstWhere('id', $this->selectedStudentId) ?? $children->first() : $children->first();

        $activeEnrollment = $activeChild ? StudentEnrollment::with([
            'schoolYearSection.section',
            'schoolYearSection.yearLevel',
            'schoolYearSection.adviser.user',
            'grades.sectionSubject.subject',
            'grades.quarter'
        ])->where('student_id', $activeChild->id)
          ->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $sy?->id))
          ->first() : null;

        return view('livewire.parent-dashboard', [
            'parent' => $parent,
            'children' => $children,
            'activeChild' => $activeChild,
            'activeEnrollment' => $activeEnrollment,
            'currentSy' => $sy,
        ])->layout('layouts.app', ['title' => 'Parent Portal - Academic Performance']);
    }
}
