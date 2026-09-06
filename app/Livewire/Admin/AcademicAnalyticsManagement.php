<?php

namespace App\Livewire\Admin;

use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\YearLevel;
use App\Services\AcademicAnalyticsService;
use App\Services\AcademicContextService;
use Livewire\Component;

class AcademicAnalyticsManagement extends Component
{
    public $selectedSchoolYearId = 'all';
    public $selectedQuarterId = 'all';
    public $selectedYearLevelId = 'all';
    public $selectedSectionId = 'all';

    public function mount()
    {
        $activeSy = AcademicContextService::getActiveSchoolYear();
        if ($activeSy) {
            $this->selectedSchoolYearId = $activeSy->id;
            $currentQuarter = AcademicContextService::getCurrentQuarter($activeSy->id);
            if ($currentQuarter) {
                $this->selectedQuarterId = $currentQuarter->id;
            }
        }
    }

    public function updatedSelectedYearLevelId()
    {
        $this->selectedSectionId = 'all';
    }

    public function updatedSelectedSchoolYearId()
    {
        $this->selectedSectionId = 'all';
    }

    public function exportCsv()
    {
        $analytics = AcademicAnalyticsService::getSummaryStats([
            'school_year_id' => $this->selectedSchoolYearId,
            'quarter_id' => $this->selectedQuarterId,
            'year_level_id' => $this->selectedYearLevelId,
            'section_id' => $this->selectedSectionId,
        ]);

        $subjectBreakdown = AcademicAnalyticsService::getSubjectMasteryBreakdown([
            'school_year_id' => $this->selectedSchoolYearId,
            'quarter_id' => $this->selectedQuarterId,
            'year_level_id' => $this->selectedYearLevelId,
            'section_id' => $this->selectedSectionId,
        ]);

        $csvData = "Academic Analytics Report - Don Mariano Marcos National High School\n";
        $csvData .= "Overall General Average,Honor Candidates,Passing Rate,At-Risk Students\n";
        $csvData .= "{$analytics['overall_average']},{$analytics['honor_students']},{$analytics['passing_rate']}%,{$analytics['at_risk_students']}\n\n";
        $csvData .= "Subject Code,Subject Name,Students Assessed,Average Grade,Passing Rate\n";

        foreach ($subjectBreakdown as $sb) {
            $csvData .= "{$sb['subject_code']},\"{$sb['subject_name']}\",{$sb['students_count']},{$sb['average']},{$sb['passing_rate']}%\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'APMS_Academic_Analytics_Report_' . date('Y_m_d') . '.csv');
    }

    public function render()
    {
        $filters = [
            'school_year_id' => $this->selectedSchoolYearId,
            'quarter_id' => $this->selectedQuarterId,
            'year_level_id' => $this->selectedYearLevelId,
            'section_id' => $this->selectedSectionId,
        ];

        $summaryStats = AcademicAnalyticsService::getSummaryStats($filters);
        $yearLevelChart = AcademicAnalyticsService::getYearLevelPerformanceData($filters);
        $quarterTrendChart = AcademicAnalyticsService::getQuarterlyTrendData($filters);
        $subjectChart = AcademicAnalyticsService::getSubjectMasteryData($filters);
        $gradeDistributionChart = AcademicAnalyticsService::getGradeDistributionData($filters);
        $subjectBreakdown = AcademicAnalyticsService::getSubjectMasteryBreakdown($filters);
        $atRiskStudents = AcademicAnalyticsService::getAtRiskStudents($filters);

        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $quarters = $this->selectedSchoolYearId !== 'all' ? Quarter::where('school_year_id', $this->selectedSchoolYearId)->orderBy('sort_order')->get() : Quarter::orderBy('sort_order')->get();
        $yearLevels = YearLevel::orderBy('sort_order')->get();
        $sections = \App\Models\SchoolYearSection::query()
            ->when($this->selectedSchoolYearId !== 'all', fn($q) => $q->where('school_year_id', $this->selectedSchoolYearId))
            ->when($this->selectedYearLevelId !== 'all', fn($q) => $q->where('year_level_id', $this->selectedYearLevelId))
            ->with('section')
            ->get()
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('livewire.admin.academic-analytics-management', [
            'summaryStats' => $summaryStats,
            'yearLevelChart' => $yearLevelChart,
            'quarterTrendChart' => $quarterTrendChart,
            'subjectChart' => $subjectChart,
            'gradeDistributionChart' => $gradeDistributionChart,
            'subjectBreakdown' => $subjectBreakdown,
            'atRiskStudents' => $atRiskStudents,
            'schoolYears' => $schoolYears,
            'quarters' => $quarters,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
        ])->layout('layouts.admin', ['title' => 'Academic Analytics & Performance Intelligence', 'subtitle' => 'Comprehensive visual analytics, subject mastery metrics, and early warning indicators']);
    }
}
