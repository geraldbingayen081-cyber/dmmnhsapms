<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;

$count100 = Grade::where('grade', '>', 99)->count();
echo "Grades with grade > 99 in database: {$count100}\n";

if ($count100 > 0) {
    Grade::where('grade', '>', 99)->update(['grade' => 99.00]);
    echo "Successfully capped {$count100} grades to 99.00!\n";
}

$max = Grade::max('grade');
$min = Grade::min('grade');
echo "Current Database Grade Range: Min = {$min}, Max = {$max}\n";

$countExceeding = Grade::where('grade', '>', 99)->count();
echo "Grades > 99 remaining: {$countExceeding}\n";
