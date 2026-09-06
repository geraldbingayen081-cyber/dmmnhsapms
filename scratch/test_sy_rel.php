<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sy = \App\Models\SchoolYear::with(['sections', 'schoolYearSections', 'quarters'])->first();
if ($sy) {
    echo "SY: {$sy->school_year} | Sections count: " . $sy->schoolYearSections->count() . " | Quarters count: " . $sy->quarters->count() . "\n";
} else {
    echo "No school years found.\n";
}
