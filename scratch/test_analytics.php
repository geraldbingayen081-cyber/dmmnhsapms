<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$metrics1 = \App\Services\AcademicAnalyticsService::getSummaryMetrics("1", "all");
$metrics2 = \App\Services\AcademicAnalyticsService::getSummaryMetrics(null, null);

echo "Metrics with string IDs: " . json_encode($metrics1) . "\n";
echo "Metrics with null IDs: " . json_encode($metrics2) . "\n";
