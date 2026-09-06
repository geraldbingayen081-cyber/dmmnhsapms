<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quarter;
use App\Models\SchoolYear;

foreach (Quarter::with('schoolYear')->get() as $q) {
    echo "Quarter ID: {$q->id}, SY: {$q->school_year_id} ({$q->schoolYear?->school_year}), Name: {$q->name}, Short: {$q->short_name}, Status: {$q->status}\n";
}

echo "\nSchool Years:\n";
foreach (SchoolYear::all() as $sy) {
    echo "SY ID: {$sy->id}, School Year: {$sy->school_year}, Status: {$sy->status}\n";
}
