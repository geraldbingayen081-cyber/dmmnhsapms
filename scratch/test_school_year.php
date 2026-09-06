<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schoolYears = \App\Models\SchoolYear::with('quarters')->get();
echo "School Years Count: " . $schoolYears->count() . "\n";
foreach ($schoolYears as $sy) {
    echo "SY {$sy->school_year} (Status: {$sy->status}) - Quarters Count: " . $sy->quarters->count() . "\n";
}
