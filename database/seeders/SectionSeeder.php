<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'Rizal',
            'Mabini',
            'Bonifacio',
            'Luna',
            'Aguinaldo',
            'Del Pilar',
        ];

        foreach ($sections as $name) {
            Section::updateOrCreate(['name' => $name]);
        }
    }
}
