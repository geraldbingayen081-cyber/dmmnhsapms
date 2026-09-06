<?php

namespace Database\Seeders;

use App\Models\YearLevel;
use Illuminate\Database\Seeder;

class YearLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Grade 7', 'code' => 'G7', 'sort_order' => 1],
            ['name' => 'Grade 8', 'code' => 'G8', 'sort_order' => 2],
            ['name' => 'Grade 9', 'code' => 'G9', 'sort_order' => 3],
            ['name' => 'Grade 10', 'code' => 'G10', 'sort_order' => 4],
        ];

        foreach ($levels as $l) {
            YearLevel::updateOrCreate(['code' => $l['code']], $l);
        }
    }
}
