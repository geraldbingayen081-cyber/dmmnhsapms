<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['subject_code' => 'FIL', 'subject_name' => 'Filipino'],
            ['subject_code' => 'ENG', 'subject_name' => 'English'],
            ['subject_code' => 'MAT', 'subject_name' => 'Mathematics'],
            ['subject_code' => 'SCI', 'subject_name' => 'Science'],
            ['subject_code' => 'AP', 'subject_name' => 'Araling Panlipunan'],
            ['subject_code' => 'ESP', 'subject_name' => 'Edukasyon sa Pagpapakatao'],
            ['subject_code' => 'MAPEH', 'subject_name' => 'MAPEH'],
            ['subject_code' => 'TLE', 'subject_name' => 'Technology and Livelihood Education'],
        ];

        foreach ($subjects as $s) {
            Subject::updateOrCreate(['subject_code' => $s['subject_code']], $s);
        }
    }
}
