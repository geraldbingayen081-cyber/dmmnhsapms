<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Achievement;

$ach = Achievement::first();
if ($ach && empty($ach->quarter)) {
    $ach->update(['quarter' => '1st Quarter']);
    echo "Updated Achievement ID {$ach->id} quarter to '1st Quarter'.\n";
} else {
    echo "Achievement already has quarter or none exists.\n";
}
