<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\GradeLevel;
use App\Models\ParentModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DMMNHSSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Academic Years
        $syPast = AcademicYear::create([
            'year' => '2024-2025',
            'is_current' => false,
            'status' => 'LOCKED',
        ]);

        $syCurrent = AcademicYear::create([
            'year' => '2025-2026',
            'is_current' => true,
            'status' => 'Q3_OPEN',
        ]);

        // 2. Create Grade Levels (Grades 7 to 10)
        $gl7 = GradeLevel::create(['level' => 7, 'name' => 'Grade 7']);
        $gl8 = GradeLevel::create(['level' => 8, 'name' => 'Grade 8']);
        $gl9 = GradeLevel::create(['level' => 9, 'name' => 'Grade 9']);
        $gl10 = GradeLevel::create(['level' => 10, 'name' => 'Grade 10']);

        // 3. Create Admin Account
        $admin = User::create([
            'name' => 'Dr. Mariano Administrator',
            'email' => 'admin@dmmnhs.edu.ph',
            'user_id' => 'ADM-0001',
            'role' => 'administrator',
            'password' => Hash::make('admin123'),
        ]);

        // 4. Create Teacher Accounts
        $teacher1 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@dmmnhs.edu.ph',
            'user_id' => 'EMP-0001',
            'role' => 'teacher',
            'password' => Hash::make('password'),
        ]);

        $teacher2 = User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@dmmnhs.edu.ph',
            'user_id' => 'EMP-0002',
            'role' => 'teacher',
            'password' => Hash::make('password'),
        ]);

        $teacher3 = User::create([
            'name' => 'Elena Reyes',
            'email' => 'elena.reyes@dmmnhs.edu.ph',
            'user_id' => 'EMP-0003',
            'role' => 'teacher',
            'password' => Hash::make('password'),
        ]);

        $teacher4 = User::create([
            'name' => 'Roberto Garcia',
            'email' => 'roberto.garcia@dmmnhs.edu.ph',
            'user_id' => 'EMP-0004',
            'role' => 'teacher',
            'password' => Hash::make('password'),
        ]);

        // 5. Create Sections
        $sec7A = Section::create(['grade_level_id' => $gl7->id, 'name' => '7-Sampaguita', 'adviser_id' => $teacher1->id]);
        $sec7B = Section::create(['grade_level_id' => $gl7->id, 'name' => '7-Jasmin', 'adviser_id' => $teacher2->id]);
        $sec8A = Section::create(['grade_level_id' => $gl8->id, 'name' => '8-Rizal', 'adviser_id' => $teacher3->id]);
        $sec8B = Section::create(['grade_level_id' => $gl8->id, 'name' => '8-Bonifacio', 'adviser_id' => $teacher4->id]);
        $sec9A = Section::create(['grade_level_id' => $gl9->id, 'name' => '9-Lily', 'adviser_id' => $teacher1->id]);
        $sec9B = Section::create(['grade_level_id' => $gl9->id, 'name' => '9-Rose', 'adviser_id' => $teacher2->id]);
        $sec10A = Section::create(['grade_level_id' => $gl10->id, 'name' => '10-Kamagong', 'adviser_id' => $teacher3->id]);
        $sec10B = Section::create(['grade_level_id' => $gl10->id, 'name' => '10-Narra', 'adviser_id' => $teacher4->id]);

        // 6. Create Core JHS Subjects per Grade Level
        $subjectNames = [
            'MATH' => 'Mathematics',
            'SCI' => 'Science',
            'ENG' => 'English',
            'FIL' => 'Filipino',
            'AP' => 'Araling Panlipunan',
            'MAPEH' => 'MAPEH',
            'ESP' => 'Edukasyon sa Pagpapakakatao',
            'TLE' => 'Technology and Livelihood Education',
        ];

        $subjectsByGrade = [];
        foreach ([$gl7, $gl8, $gl9, $gl10] as $gl) {
            foreach ($subjectNames as $codePrefix => $name) {
                $subj = Subject::create([
                    'grade_level_id' => $gl->id,
                    'code' => "{$codePrefix}{$gl->level}",
                    'name' => $name,
                ]);
                $subjectsByGrade[$gl->id][] = $subj;
            }
        }

        // 7. Assign Teacher Teaching Loads
        $teachersList = [$teacher1, $teacher2, $teacher3, $teacher4];
        $allSections = [$sec7A, $sec7B, $sec8A, $sec8B, $sec9A, $sec9B, $sec10A, $sec10B];
        foreach ($allSections as $index => $sec) {
            $t = $teachersList[$index % count($teachersList)];
            $secSubjs = $subjectsByGrade[$sec->grade_level_id];
            foreach ($secSubjs as $subj) {
                TeacherSubject::create([
                    'teacher_id' => $t->id,
                    'subject_id' => $subj->id,
                    'section_id' => $sec->id,
                    'academic_year_id' => $syCurrent->id,
                ]);
            }
        }

        // 8. Create Parent Accounts
        $parents = [];
        for ($p = 1; $p <= 5; $p++) {
            $pUser = User::create([
                'name' => "Parent Guardian {$p}",
                'email' => "parent{$p}@dmmnhs.edu.ph",
                'user_id' => sprintf('PARENT-%04d', $p),
                'role' => 'parent',
                'password' => Hash::make('password'),
            ]);

            $parents[] = ParentModel::create([
                'user_id' => $pUser->id,
                'custom_parent_id' => $pUser->user_id,
                'contact_number' => '0917123450' . $p,
                'address' => "Barangay San Jose, City near DMMNHS #{$p}",
            ]);
        }

        // 9. Create 40 Students across Sections
        $firstNames = ['Juan', 'Maria', 'Angela', 'Gabriel', 'Carlos', 'Sofia', 'Mateo', 'Isabella', 'Liam', 'Claire', 'Ethan', 'Chloe', 'Noah', 'Hannah', 'Lucas', 'Mia', 'Benjamin', 'Ava', 'Alexander', 'Grace'];
        $lastNames = ['Dela Cruz', 'Santos', 'Reyes', 'Gonzales', 'Bautista', 'Villanueva', 'Ramos', 'Mendoza', 'Castro', 'Aquino', 'Torres', 'Navarro', 'Flores', 'Corpuz', 'Tolentino', 'Mercado'];

        $studentCount = 40;
        for ($i = 1; $i <= $studentCount; $i++) {
            $fn = $firstNames[($i - 1) % count($firstNames)];
            $ln = $lastNames[($i - 1) % count($lastNames)];
            $customId = sprintf('2026-%04d', $i);
            $lrn = sprintf('10982345%04d', $i);

            $assignedSec = $allSections[($i - 1) % count($allSections)];

            $stUser = User::create([
                'name' => "{$fn} {$ln}",
                'email' => "student{$i}@dmmnhs.edu.ph",
                'user_id' => $customId,
                'role' => 'student',
                'password' => Hash::make('password'),
            ]);

            $student = Student::create([
                'user_id' => $stUser->id,
                'custom_student_id' => $customId,
                'lrn' => $lrn,
                'first_name' => $fn,
                'last_name' => $ln,
                'gender' => ($i % 2 === 0) ? 'Female' : 'Male',
                'section_id' => $assignedSec->id,
                'birthdate' => '2010-05-15',
                'guardian_name' => $parents[($i - 1) % count($parents)]->user->name,
                'guardian_contact' => $parents[($i - 1) % count($parents)]->contact_number,
                'status' => 'Active',
            ]);

            // Link parent
            $parent = $parents[($i - 1) % count($parents)];
            $parent->students()->attach($student->id);

            // Enrollment History
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $syCurrent->id,
                'grade_level_id' => $assignedSec->grade_level_id,
                'section_id' => $assignedSec->id,
                'promotion_status' => 'Pending',
            ]);

            // 10. Generate Realistic Grades for Q1, Q2, Q3
            $subjs = $subjectsByGrade[$assignedSec->grade_level_id];

            // Vary performance levels (Honor student, Average student, At-Risk student)
            $tier = $i % 5; // 0 = High Honors (94-98), 1 = Honors (90-93), 2 & 3 = Average (80-89), 4 = At-Risk (72-78)

            foreach ($subjs as $sub) {
                if ($tier === 0) {
                    $q1 = rand(94, 98);
                    $q2 = rand(95, 99);
                    $q3 = rand(94, 98);
                } elseif ($tier === 1) {
                    $q1 = rand(89, 93);
                    $q2 = rand(90, 94);
                    $q3 = rand(90, 93);
                } elseif ($tier === 2 || $tier === 3) {
                    $q1 = rand(80, 88);
                    $q2 = rand(82, 89);
                    $q3 = rand(81, 87);
                } else {
                    // At Risk
                    $q1 = rand(70, 76);
                    $q2 = rand(68, 75);
                    $q3 = rand(71, 74);
                }

                $stGrade = StudentGrade::create([
                    'student_id' => $student->id,
                    'subject_id' => $sub->id,
                    'academic_year_id' => $syCurrent->id,
                    'q1' => $q1,
                    'q2' => $q2,
                    'q3' => $q3,
                ]);
            }

            // Calculate General Average & Award Achievements if eligible
            $ga = $student->calculateGeneralAverage($syCurrent->id);

            // Update enrollment record with calculated average
            StudentEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $syCurrent->id)
                ->update(['general_average' => $ga]);

            // Award Honor Standing
            $honorStanding = $student->honor_standing;
            if ($honorStanding) {
                Achievement::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $syCurrent->id,
                    'type' => 'Honor Roll',
                    'title' => $honorStanding,
                    'quarter' => 'Q3',
                    'remarks' => "Calculated General Average of {$ga}% with no quarter subject grade below 85.",
                ]);
            }

            // Award Best in Subject for top performers
            if ($tier === 0 && $i <= 5) {
                Achievement::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $syCurrent->id,
                    'type' => 'Best in Subject',
                    'title' => 'Best in Science',
                    'quarter' => 'Q3',
                    'remarks' => 'Highest Q3 subject performance rating in Science.',
                ]);
                Achievement::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $syCurrent->id,
                    'type' => 'Best in Subject',
                    'title' => 'Best in Mathematics',
                    'quarter' => 'Q3',
                    'remarks' => 'Highest Q3 subject performance rating in Mathematics.',
                ]);
            }
        }
    }
}
