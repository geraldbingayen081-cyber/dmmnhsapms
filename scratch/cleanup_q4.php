<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deleted = \App\Models\Quarter::where('short_name', 'Q4')
    ->orWhere('name', 'LIKE', '%4th%')
    ->orWhere('sort_order', '>', 3)
    ->delete();

echo "Deleted {$deleted} Q4 / 4th quarter records from DB.\n";

$quarters = \App\Models\Quarter::all();
echo "Remaining Quarters count: " . $quarters->count() . "\n";
foreach ($quarters as $q) {
    echo "ID: {$q->id} | Name: {$q->name} | Short: {$q->short_name} | SY ID: {$q->school_year_id}\n";
}
