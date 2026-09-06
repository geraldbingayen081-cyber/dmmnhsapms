<?php

namespace App\Livewire;

use App\Models\AchievementRule;
use App\Models\SchoolYear;
use App\Services\AcademicContextService;
use Livewire\Component;

class AchievementTracker extends Component
{
    public $name = '';
    public $type = 'With Honors';
    public $minimumAverage = 90.00;
    public $maximumAverage = 94.99;
    public $minimumSubjectGrade = 85.00;

    public function createRule()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if (!$activeSy) return;

        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'minimumAverage' => 'nullable|numeric|min:50|max:100',
            'maximumAverage' => 'nullable|numeric|min:50|max:100',
            'minimumSubjectGrade' => 'nullable|numeric|min:50|max:100',
        ]);

        AchievementRule::create([
            'school_year_id' => $activeSy->id,
            'name' => $this->name,
            'type' => $this->type,
            'minimum_average' => $this->minimumAverage,
            'maximum_average' => $this->maximumAverage,
            'minimum_subject_grade' => $this->minimumSubjectGrade,
            'is_active' => true,
        ]);

        $this->reset(['name']);
        session()->flash('message', 'Achievement Rule created successfully!');
    }

    public function toggleRule($id)
    {
        $rule = AchievementRule::find($id);
        if ($rule) {
            $rule->update(['is_active' => !$rule->is_active]);
            session()->flash('message', 'Achievement Rule status updated.');
        }
    }

    public function render()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();

        $rules = $activeSy ? AchievementRule::where('school_year_id', $activeSy->id)->get() : collect();

        return view('livewire.achievement-tracker', [
            'rules' => $rules,
            'currentSy' => $activeSy,
        ])->layout('layouts.admin', ['title' => 'Academic Achievements']);
    }
}
