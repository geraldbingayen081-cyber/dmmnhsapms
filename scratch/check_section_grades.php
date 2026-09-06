<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\Quarter;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;

$sys = SchoolYearSection::where('school_year_id', 1)->first();
echo "SYS ID: {$sys->id}, Section: {$sys->section?->name}\n";

$secSubjs = SectionSubject::where('school_year_section_id', $sys->id)->with('subject')->get();
foreach ($secSubjs as $ss) {
    echo "SS ID: {$ss->id} ({$ss->subject?->subject_name}), Teacher ID: {$ss->teacher_id}\n";
    foreach (Quarter::where('school_year_id', 1)->get() as $q) {
        $cnt = Grade::where('section_subject_id', $ss->id)->where('quarter_id', $q->id)->count();
        echo "   {$q->short_name} (ID {$q->id}): {$cnt} grades\n";
    }
}
