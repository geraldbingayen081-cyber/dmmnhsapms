<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Achievement;

$achs = Achievement::all();
echo "Total Achievements in DB: " . $achs->count() . "\n";
foreach ($achs as $a) {
    echo "  - ID: {$a->id}, student_id: {$a->student_id}, type: {$a->type}, title: {$a->title}, quarter: '{$a->quarter}', date: {$a->date_awarded}\n";
}
