<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filters = [
    'school_year_id' => 'all',
    'quarter_id' => 'all',
    'year_level_id' => 'all',
    'section_id' => 'all',
];

$stats = \App\Services\AcademicAnalyticsService::getSummaryStats($filters);
$ylData = \App\Services\AcademicAnalyticsService::getYearLevelPerformanceData($filters);
$qTrend = \App\Services\AcademicAnalyticsService::getQuarterlyTrendData($filters);
$subData = \App\Services\AcademicAnalyticsService::getSubjectMasteryData($filters);
$dist = \App\Services\AcademicAnalyticsService::getGradeDistributionData($filters);
$breakdown = \App\Services\AcademicAnalyticsService::getSubjectMasteryBreakdown($filters);
$atRisk = \App\Services\AcademicAnalyticsService::getAtRiskStudents($filters);

echo "Summary Stats: " . json_encode($stats) . "\n";
echo "Year Level Data: " . json_encode($ylData) . "\n";
echo "Quarterly Trend: " . json_encode($qTrend) . "\n";
echo "Subject Mastery: " . json_encode($subData) . "\n";
echo "Grade Distribution: " . json_encode($dist) . "\n";
echo "Subject Breakdown Count: " . count($breakdown) . "\n";
echo "At Risk Count: " . count($atRisk) . "\n";
