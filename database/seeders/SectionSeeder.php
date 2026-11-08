<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            // Grade 7
            'SPJ', 'Hermes', 'Zues', 'Apollo',
            // Grade 8
            'SPJ', 'Diamond', 'Saphire', 'Amethyst', 'Jade',
            // Grade 9
            'SPJ', 'Poinsettia', 'Chrysanthmem', 'Adenium',
            // Grade 10
            'SPJ', 'Cranberry', 'Strawberry', 'Blueberry',
        ];

        foreach ($sections as $name) {
            Section::firstOrCreate(['name' => $name]);
        }
    }
}
