<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clean test data using DELETE safely
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('grades')) Grade::query()->delete();
        if (Schema::hasTable('parent_student')) DB::table('parent_student')->delete();
        if (Schema::hasTable('parents')) ParentModel::query()->delete();
        if (Schema::hasTable('student_enrollments')) StudentEnrollment::query()->delete();
        if (Schema::hasTable('students')) Student::query()->delete();
        if (Schema::hasTable('users')) User::whereIn('role', ['parent', 'student'])->delete();
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            $now = now();
            $hashedPassword = Hash::make('password');

            // ─── 1. Year Levels ───────────────────────────────────────────────────────
            $y7  = YearLevel::firstOrCreate(['code' => 'G7'],  ['name' => 'Grade 7',  'sort_order' => 1]);
            $y8  = YearLevel::firstOrCreate(['code' => 'G8'],  ['name' => 'Grade 8',  'sort_order' => 2]);
            $y9  = YearLevel::firstOrCreate(['code' => 'G9'],  ['name' => 'Grade 9',  'sort_order' => 3]);
            $y10 = YearLevel::firstOrCreate(['code' => 'G10'], ['name' => 'Grade 10', 'sort_order' => 4]);
            $ylModels = ['G7' => $y7, 'G8' => $y8, 'G9' => $y9, 'G10' => $y10];

            // ─── 2. Subjects (8 Core JHS Areas) ──────────────────────────────────────
            $subjectDefs = [
                ['subject_code' => 'FIL',   'subject_name' => 'Filipino'],
                ['subject_code' => 'ENG',   'subject_name' => 'English'],
                ['subject_code' => 'MAT',   'subject_name' => 'Mathematics'],
                ['subject_code' => 'SCI',   'subject_name' => 'Science'],
                ['subject_code' => 'AP',    'subject_name' => 'Araling Panlipunan'],
                ['subject_code' => 'ESP',   'subject_name' => 'Edukasyon sa Pagpapakatao'],
                ['subject_code' => 'MAPEH', 'subject_name' => 'MAPEH'],
                ['subject_code' => 'TLE',   'subject_name' => 'Technology and Livelihood Education'],
            ];
            $subjectsArr = [];
            foreach ($subjectDefs as $s) {
                $subjectsArr[] = Subject::firstOrCreate(['subject_code' => $s['subject_code']], $s);
            }

            // ─── 3. School Year & Quarters ────────────────────────────────────────────
            $sy = SchoolYear::updateOrCreate(
                ['school_year' => '2026-2027'],
                ['start_date' => '2026-08-01', 'end_date' => '2027-05-31', 'status' => 'active']
            );
            $q1 = Quarter::updateOrCreate(['school_year_id' => $sy->id, 'short_name' => 'Q1'], ['name' => '1st Quarter', 'sort_order' => 1, 'status' => 'completed']);
            $q2 = Quarter::updateOrCreate(['school_year_id' => $sy->id, 'short_name' => 'Q2'], ['name' => '2nd Quarter', 'sort_order' => 2, 'status' => 'completed']);
            $q3 = Quarter::updateOrCreate(['school_year_id' => $sy->id, 'short_name' => 'Q3'], ['name' => '3rd Quarter', 'sort_order' => 3, 'status' => 'active']);
            $quarters = [$q1, $q2, $q3];

            // ─── 4. Section Names (8 sections per year level) ─────────────────────────
            $sectionThemes = [
                'G7'  => ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
                'G8'  => ['Pacific', 'Atlantic', 'Indian', 'Arctic', 'Southern', 'Caribbean', 'Coral', 'Bering'],
                'G9'  => ['Apo',    'Pulag', 'Halcon', 'Dulang', 'Kalatungan', 'Tabayoc', 'Mayon', 'Pinatubo'],
                'G10' => ['Philippines', 'Japan', 'Singapore', 'Malaysia', 'Thailand', 'Indonesia', 'Vietnam', 'Cambodia'],
            ];
            $sectionsByYL = [];
            foreach ($sectionThemes as $ylCode => $names) {
                $sectionsByYL[$ylCode] = [];
                foreach ($names as $name) {
                    $sectionsByYL[$ylCode][] = Section::firstOrCreate(['name' => $name]);
                }
            }

            // ─── 5. Teachers: 8 per year level (1 per subject, 1 advisee section) ─────
            $teacherDefs = [
                'G7' => [
                    ['emp' => 'EMP-0001', 'fn' => 'Maria',     'mn' => 'C', 'ln' => 'Santos',     'contact' => '+639171110001'],
                    ['emp' => 'EMP-0002', 'fn' => 'Juan',      'mn' => 'R', 'ln' => 'Dela Cruz',  'contact' => '+639171110002'],
                    ['emp' => 'EMP-0003', 'fn' => 'Elena',     'mn' => 'M', 'ln' => 'Reyes',      'contact' => '+639171110003'],
                    ['emp' => 'EMP-0004', 'fn' => 'Roberto',   'mn' => 'A', 'ln' => 'Garcia',     'contact' => '+639171110004'],
                    ['emp' => 'EMP-0005', 'fn' => 'Liza',      'mn' => 'L', 'ln' => 'Bautista',   'contact' => '+639171110005'],
                    ['emp' => 'EMP-0006', 'fn' => 'Ferdinand', 'mn' => 'T', 'ln' => 'Ramos',      'contact' => '+639171110006'],
                    ['emp' => 'EMP-0007', 'fn' => 'Aurora',    'mn' => 'U', 'ln' => 'Magsaysay',  'contact' => '+639171110007'],
                    ['emp' => 'EMP-0008', 'fn' => 'Danilo',    'mn' => 'V', 'ln' => 'Marcos',     'contact' => '+639171110008'],
                ],
                'G8' => [
                    ['emp' => 'EMP-0009',  'fn' => 'Cynthia',  'mn' => 'D', 'ln' => 'Navarro',    'contact' => '+639171110009'],
                    ['emp' => 'EMP-0010',  'fn' => 'Bernard',  'mn' => 'F', 'ln' => 'Torres',     'contact' => '+639171110010'],
                    ['emp' => 'EMP-0011',  'fn' => 'Rosario',  'mn' => 'G', 'ln' => 'Mendoza',    'contact' => '+639171110011'],
                    ['emp' => 'EMP-0012',  'fn' => 'Antonio',  'mn' => 'B', 'ln' => 'Villanueva', 'contact' => '+639171110012'],
                    ['emp' => 'EMP-0013',  'fn' => 'Patricia', 'mn' => 'E', 'ln' => 'Flores',     'contact' => '+639171110013'],
                    ['emp' => 'EMP-0014',  'fn' => 'Domingo',  'mn' => 'S', 'ln' => 'Corpuz',     'contact' => '+639171110014'],
                    ['emp' => 'EMP-0015',  'fn' => 'Leticia',  'mn' => 'H', 'ln' => 'Agoncillo',  'contact' => '+639171110015'],
                    ['emp' => 'EMP-0016',  'fn' => 'Renato',   'mn' => 'I', 'ln' => 'Rizal',      'contact' => '+639171110016'],
                ],
                'G9' => [
                    ['emp' => 'EMP-0017',  'fn' => 'Maricel',  'mn' => 'J', 'ln' => 'Aquino',     'contact' => '+639171110017'],
                    ['emp' => 'EMP-0018',  'fn' => 'Ernesto',  'mn' => 'K', 'ln' => 'Castro',     'contact' => '+639171110018'],
                    ['emp' => 'EMP-0019',  'fn' => 'Virgie',   'mn' => 'N', 'ln' => 'Tolentino',  'contact' => '+639171110019'],
                    ['emp' => 'EMP-0020',  'fn' => 'Ricardo',  'mn' => 'O', 'ln' => 'Soriano',    'contact' => '+639171110020'],
                    ['emp' => 'EMP-0021',  'fn' => 'Carmelita','mn' => 'P', 'ln' => 'Mercado',    'contact' => '+639171110021'],
                    ['emp' => 'EMP-0022',  'fn' => 'Gregorio', 'mn' => 'Q', 'ln' => 'Manalo',     'contact' => '+639171110022'],
                    ['emp' => 'EMP-0023',  'fn' => 'Nimfa',    'mn' => 'W', 'ln' => 'Gonzales',   'contact' => '+639171110023'],
                    ['emp' => 'EMP-0024',  'fn' => 'Armando',  'mn' => 'X', 'ln' => 'Pascual',    'contact' => '+639171110024'],
                ],
                'G10' => [
                    ['emp' => 'EMP-0025',  'fn' => 'Leonora',  'mn' => 'Z', 'ln' => 'Bernardo',   'contact' => '+639171110025'],
                    ['emp' => 'EMP-0026',  'fn' => 'Oscar',    'mn' => 'A', 'ln' => 'Perez',      'contact' => '+639171110026'],
                    ['emp' => 'EMP-0027',  'fn' => 'Milagros', 'mn' => 'B', 'ln' => 'Cruz',       'contact' => '+639171110027'],
                    ['emp' => 'EMP-0028',  'fn' => 'Eduardo',  'mn' => 'C', 'ln' => 'Luna',       'contact' => '+639171110028'],
                    ['emp' => 'EMP-0029',  'fn' => 'Felicitas','mn' => 'D', 'ln' => 'Valdez',     'contact' => '+639171110029'],
                    ['emp' => 'EMP-0030',  'fn' => 'Isidro',   'mn' => 'E', 'ln' => 'Peralta',    'contact' => '+639171110030'],
                    ['emp' => 'EMP-0031',  'fn' => 'Remedios', 'mn' => 'F', 'ln' => 'Diaz',       'contact' => '+639171110031'],
                    ['emp' => 'EMP-0032',  'fn' => 'Herminio',  'mn' => 'G', 'ln' => 'Salazar',   'contact' => '+639171110032'],
                ],
            ];

            $teachersByYL = [];
            $allSys = [];

            foreach ($teacherDefs as $ylCode => $tGroup) {
                $teachersByYL[$ylCode] = [];
                $yl       = $ylModels[$ylCode];
                $sections = $sectionsByYL[$ylCode];

                foreach ($tGroup as $idx => $td) {
                    $user = User::updateOrCreate(['login_id' => $td['emp']], [
                        'name'     => "{$td['fn']} {$td['ln']}",
                        'email'    => strtolower(str_replace('-', '', $td['emp'])) . '@faculty.dmmnhs.edu.ph',
                        'password' => $hashedPassword,
                        'role'     => 'teacher',
                        'status'   => 'active',
                    ]);
                    $teacher = Teacher::updateOrCreate(['user_id' => $user->id], [
                        'employee_number' => $td['emp'],
                        'first_name'      => $td['fn'],
                        'middle_name'     => $td['mn'],
                        'last_name'       => $td['ln'],
                        'contact_number'  => $td['contact'],
                    ]);
                    $teachersByYL[$ylCode][] = $teacher;

                    $sys = SchoolYearSection::updateOrCreate(
                        ['school_year_id' => $sy->id, 'year_level_id' => $yl->id, 'section_id' => $sections[$idx]->id],
                        ['adviser_id' => $teacher->id, 'status' => 'active']
                    );
                    $allSys[] = $sys;
                }
            }

            // ─── 6. Section Subjects ──────────────────────────────────────────────────
            $sectionSubjectMap = [];
            foreach (['G7', 'G8', 'G9', 'G10'] as $ylCode) {
                $yl        = $ylModels[$ylCode];
                $tGroup    = $teachersByYL[$ylCode];
                $ylSysList = SchoolYearSection::where('school_year_id', $sy->id)
                    ->where('year_level_id', $yl->id)
                    ->get();

                foreach ($ylSysList as $sys) {
                    $sectionSubjectMap[$sys->id] = [];
                    foreach ($subjectsArr as $sIdx => $subject) {
                        $assignedTeacher = $tGroup[$sIdx];
                        $ss = SectionSubject::updateOrCreate(
                            ['school_year_section_id' => $sys->id, 'subject_id' => $subject->id],
                            ['teacher_id' => $assignedTeacher->id]
                        );
                        $sectionSubjectMap[$sys->id][] = $ss;
                    }
                }
            }

            // ─── 7. Students & Dynamic Performance Distribution ───────────────────────
            // Unique profiles per year level so analytics and averages are distinct:
            // 0=Highest Honors (98-99), 1=High Honors (95-97), 2=Honors (90-94), 3=Average (81-88), 4=Fair (76-79)
            $ylProfiles = [
                'G7' => [
                    // High performing intake: 8 achievers/section (~64 achievers, ~88.8% GWA)
                    'slots' => [0, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4],
                ],
                'G8' => [
                    // Transition challenges: 3 achievers/section (~24 achievers, ~83.9% GWA)
                    'slots' => [1, 2, 2, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4],
                ],
                'G9' => [
                    // Balanced intermediate: 5 achievers/section (~40 achievers, ~86.5% GWA)
                    'slots' => [0, 1, 1, 2, 2, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4],
                ],
                'G10' => [
                    // Graduating completers push: 9 achievers/section (~72 achievers, ~90.4% GWA)
                    'slots' => [0, 0, 1, 1, 1, 1, 2, 2, 2, 3, 3, 3, 3, 3, 4],
                ],
            ];

            $firstNames = [
                'Juan','Maria','Angela','Gabriel','Carlos',
                'Sofia','Mateo','Isabella','Liam','Claire',
                'Ethan','Chloe','Noah','Hannah','Lucas',
                'Mia','Benjamin','Ava','Alexander','Grace',
                'Patrick','Katrina','Miguel','Andrea','Jose',
                'Kristine','Marco','Bianca','Paolo','Camille',
                'Joshua','Althea','Justin','Danielle','Christian',
                'Kaye','Dominic','Nicole','Francis','Angelica',
            ];
            $lastNames = [
                'Dela Cruz','Santos','Reyes','Gonzales','Bautista',
                'Villanueva','Ramos','Mendoza','Castro','Aquino',
                'Torres','Navarro','Flores','Corpuz','Tolentino',
                'Mercado','Soriano','Manalo','Rizal','Agoncillo',
                'Pascual','Valdez','Peralta','Ocampo','Fernandez',
                'Velasquez','Guevara','Diaz','Salazar','Morales',
                'Villamor','Alvarez','Castillo','Bernardo','Vergara',
            ];

            $adminUser = User::where('role', 'admin')->first();
            $adminId   = $adminUser?->id ?? 1;

            $allCreatedStudents = [];
            $allCreatedEnrollments = [];
            $gradeRowsToInsert = [];
            $studentCounter = 1;

            foreach ($allSys as $sys) {
                // Find year level code for this section
                $yl = YearLevel::find($sys->year_level_id);
                $ylCode = $yl?->code ?? 'G7';
                $slotsPattern = $ylProfiles[$ylCode]['slots'] ?? $ylProfiles['G7']['slots'];

                for ($slot = 0; $slot < 15; $slot++) {
                    $stId = sprintf('2026-%04d', $studentCounter);
                    $fn   = $firstNames[($studentCounter - 1) % count($firstNames)];
                    $ln   = $lastNames[($studentCounter - 1)  % count($lastNames)];

                    $stUser = User::create([
                        'login_id' => $stId,
                        'name'     => "{$fn} {$ln}",
                        'email'    => strtolower($stId) . '@student.dmmnhs.edu.ph',
                        'password' => $hashedPassword,
                        'role'     => 'student',
                        'status'   => 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $student = Student::create([
                        'user_id'        => $stUser->id,
                        'student_number' => $stId,
                        'first_name'     => $fn,
                        'last_name'      => $ln,
                        'gender'         => ($studentCounter % 2 === 0) ? 'Female' : 'Male',
                        'birthdate'      => '2011-06-15',
                        'contact_number' => sprintf('+63917%07d', $studentCounter),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                    $allCreatedStudents[] = $student;

                    $enrollment = StudentEnrollment::create([
                        'student_id'             => $student->id,
                        'school_year_section_id' => $sys->id,
                        'enrollment_date'        => '2026-08-01',
                        'status'                 => 'enrolled',
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]);
                    $allCreatedEnrollments[$student->id] = $enrollment;

                    // Tier for this student
                    $tier = $slotsPattern[$slot];
                    $secSubjs = $sectionSubjectMap[$sys->id] ?? [];

                    // Generate distinct grades across Q1, Q2, Q3 (never identical)
                    foreach ($secSubjs as $ss) {
                        $qMarks = $this->generateDistinctQuarterMarks($tier);

                        foreach ($quarters as $qIdx => $quarter) {
                            $gradeVal = $qMarks[$qIdx];
                            $gradeRowsToInsert[] = [
                                'student_enrollment_id' => $enrollment->id,
                                'section_subject_id'    => $ss->id,
                                'quarter_id'            => $quarter->id,
                                'grade'                 => $gradeVal,
                                'remarks'               => $gradeVal >= 75 ? 'PASSED' : 'FAILED',
                                'status'                => 'approved',
                                'created_by'            => $adminId,
                                'created_at'            => $now,
                                'updated_at'            => $now,
                            ];
                        }
                    }

                    $studentCounter++;
                }
            }

            // Bulk insert all grades in efficient chunks of 1000
            foreach (array_chunk($gradeRowsToInsert, 1000) as $chunk) {
                Grade::insert($chunk);
            }

            // ─── 8. Parents (Strictly 1 or 2 Children Linked Each) ────────────────────
            $parentFirstNames = [
                'Rodrigo','Leni','Benigno','Gloria','Joseph','Fidel','Corazon','Ferdinand','Diosdado','Elpidio',
                'Ramon','Manuel','Carlos','Jose','Sergio','Emilio','Andres','Marcelo','Apolinario','Melchora',
                'Gabriela','Teresa','Teodora','Gregoria','Trinidad','Loren','Grace','Cynthia','Risa','Imee',
                'Pia','Leila','Vilma','Nora','Sharon','Lea','Manny','Francisco','Antonio','Salvador',
                'Edgardo','Vicente','Panfilo','Richard','Alan','Sonny','Sherwin','Joel','Francis','Jinggoy',
                'Robin','Mark','Win','Chiz','Tito','Kiko','Migz','Aquilino','Franklin','Miriam',
                'Arturo','Alfredo','Rodolfo','Rolando','Rogelio','Jaime','Benjamin','Alberto','Romeo','Roberto',
            ];

            $totalStudents = count($allCreatedStudents); // 480
            $sIdx = 0;
            $parentCounter = 1;

            while ($sIdx < $totalStudents) {
                // Determine children count: strictly 1 or 2
                $remaining = $totalStudents - $sIdx;
                $numChildren = ($remaining === 1) ? 1 : rand(1, 2);

                $pid = sprintf('PARENT-%04d', $parentCounter);
                $fn  = $parentFirstNames[($parentCounter - 1) % count($parentFirstNames)];
                $primaryStudent = $allCreatedStudents[$sIdx];
                $ln  = $primaryStudent->last_name; // Family continuity

                $pUser = User::create([
                    'login_id'   => $pid,
                    'name'       => "{$fn} {$ln}",
                    'email'      => strtolower(str_replace('-', '', $pid)) . '@parent.dmmnhs.edu.ph',
                    'password'   => $hashedPassword,
                    'role'       => 'parent',
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $parent = ParentModel::create([
                    'user_id'        => $pUser->id,
                    'first_name'     => $fn,
                    'last_name'      => $ln,
                    'contact_number' => sprintf('+63917%07d', 8000000 + $parentCounter),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                for ($c = 0; $c < $numChildren; $c++) {
                    $st = $allCreatedStudents[$sIdx + $c];
                    $parent->students()->attach($st->id, [
                        'relationship' => 'Parent',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }

                $sIdx += $numChildren;
                $parentCounter++;
            }

            // ─── 9. Activity Log ──────────────────────────────────────────────────────
            ActivityLog::create([
                'user_id'      => $adminId,
                'action'       => 'Seeded Test Data',
                'subject_type' => TestDataSeeder::class,
                'subject_id'   => null,
                'description'  => "SY 2026-2027 | 32 teachers | 32 sections | {$totalStudents} students (15/section) | "
                    . ($parentCounter - 1) . " parents (1-2 children each) | Distinct Q1-Q3 grades (max 99) | "
                    . "Differentiated cohort averages & achievers per year level.",
            ]);
        });
    }

    /**
     * Generate 3 distinct quarterly grades for Q1, Q2, and Q3 (never all identical).
     * Maximum allowed grade is strictly 99.00.
     */
    private function generateDistinctQuarterMarks(int $tier): array
    {
        // 0=Highest (98-99), 1=High (95-97), 2=Honors (90-94), 3=Average (81-88), 4=Fair (76-79)
        $ranges = [
            0 => [98, 99],
            1 => [95, 97],
            2 => [90, 94],
            3 => [81, 88],
            4 => [76, 79],
        ];

        [$min, $max] = $ranges[$tier];

        if ($tier === 0) {
            // Alternating pattern ensuring no identical adjacent quarters, capped strictly at 99
            return (rand(0, 1) === 1) ? [98.00, 99.00, 98.00] : [99.00, 98.00, 99.00];
        }

        $q1 = rand($min, $max);

        // Q2 differs from Q1
        $candidatesQ2 = array_values(array_filter(range($min, $max), fn($v) => $v !== $q1));
        $q2 = !empty($candidatesQ2) ? $candidatesQ2[array_rand($candidatesQ2)] : (($q1 >= $max) ? $q1 - 1 : $q1 + 1);

        // Q3 differs from Q2
        $candidatesQ3 = array_values(array_filter(range($min, $max), fn($v) => $v !== $q2 && $v !== $q1));
        if (empty($candidatesQ3)) {
            $candidatesQ3 = array_values(array_filter(range($min, $max), fn($v) => $v !== $q2));
        }
        $q3 = !empty($candidatesQ3) ? $candidatesQ3[array_rand($candidatesQ3)] : (($q2 >= $max) ? $q2 - 1 : $q2 + 1);

        return [
            min(99.00, max(60.00, (float)$q1)),
            min(99.00, max(60.00, (float)$q2)),
            min(99.00, max(60.00, (float)$q3)),
        ];
    }
}