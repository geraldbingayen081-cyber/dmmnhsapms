<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$yearLevels = \App\Models\YearLevel::orderBy('sort_order')->pluck('name', 'code');
foreach ($yearLevels as $code => $name) {
    echo "{$code}: {$name}\n";
}
