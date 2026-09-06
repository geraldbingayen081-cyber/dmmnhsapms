<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Services\AcademicAnalyticsService;
use App\Services\AcademicContextService;
use Livewire\Component;

class DashboardAnalytics extends Component
{
    public $selectedYearId = null;
    public $selectedQuarterId = null;

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedYearId = $activeSy->id;
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->selectedQuarterId = $currentQuarter->id;
            }
        }
    }

    public function updatedSelectedYearId()
    {
        $quarter = AcademicContextService::getCurrentQuarter($this->selectedYearId);
        $this->selectedQuarterId = $quarter ? $quarter->id : null;
    }

    public function render()
    {
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = $this->selectedYearId ? Quarter::where('school_year_id', $this->selectedYearId)->orderBy('sort_order')->get() : collect();

        $metrics = AcademicAnalyticsService::getSummaryMetrics($this->selectedYearId, $this->selectedQuarterId);
        $yearLevelChart = AcademicAnalyticsService::getYearLevelPerformance($this->selectedYearId, $this->selectedQuarterId);
        $quarterlyChart = AcademicAnalyticsService::getQuarterlyTrend($this->selectedYearId);
        $subjectChart = AcademicAnalyticsService::getSubjectPerformance($this->selectedYearId, $this->selectedQuarterId);
        $achievementChart = AcademicAnalyticsService::getAchievementDistribution($this->selectedYearId, $this->selectedQuarterId);


        $recentActivities = ActivityLog::with('user')->orderBy('created_at', 'desc')->take(6)->get();

        return view('livewire.dashboard-analytics', [
            'schoolYears' => $schoolYears,
            'quarters' => $quarters,
            'metrics' => $metrics,
            'yearLevelChart' => $yearLevelChart,
            'quarterlyChart' => $quarterlyChart,
            'subjectChart' => $subjectChart,
            'achievementChart' => $achievementChart,
            'recentActivities' => $recentActivities,
        ])->layout('layouts.admin', ['title' => 'Admin Dashboard', 'subtitle' => 'Academic Performance Monitoring System']);
    }
}
