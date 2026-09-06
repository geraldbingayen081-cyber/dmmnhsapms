<?php

namespace Database\Seeders;

use App\Models\Quarter;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    public function run(): void
    {
        $sy = SchoolYear::updateOrCreate(
            ['school_year' => '2026-2027'],
            [
                'start_date' => '2026-08-01',
                'end_date' => '2027-05-31',
                'status' => 'active',
            ]
        );

        $quarters = [
            ['name' => '1st Quarter', 'short_name' => 'Q1', 'sort_order' => 1, 'status' => 'active'],
            ['name' => '2nd Quarter', 'short_name' => 'Q2', 'sort_order' => 2, 'status' => 'upcoming'],
            ['name' => '3rd Quarter', 'short_name' => 'Q3', 'sort_order' => 3, 'status' => 'upcoming'],
        ];

        foreach ($quarters as $q) {
            Quarter::updateOrCreate(
                [
                    'school_year_id' => $sy->id,
                    'short_name' => $q['short_name'],
                ],
                $q
            );
        }
    }
}
