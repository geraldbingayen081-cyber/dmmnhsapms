<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subjects = \App\Models\Subject::all();
echo "Subjects Count: " . $subjects->count() . "\n";
foreach ($subjects as $s) {
    echo "{$s->subject_code}: {$s->subject_name}\n";
}
