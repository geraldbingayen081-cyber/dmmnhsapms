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

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $hashedPassword = Hash::make('password');

            // ─── 1. Year Levels ───────────────────────────────────────────────────────
            $y7  = YearLevel::firstOrCreate(['code' => 'G7'],  ['name' => 'Grade 7',  'sort_order' => 1]);
            $y8  = YearLevel::firstOrCreate(['code' => 'G8'],  ['name' => 'Grade 8',  'sort_order' => 2]);
            $y9  = YearLevel::firstOrCreate(['code' => 'G9'],  ['name' => 'Grade 9',  'sort_order' => 3]);
            $y10 = YearLevel::firstOrCreate(['code' => 'G10'], ['name' => 'Grade 10', 'sort_order' => 4]);
            $ylModels = ['G7' => $y7, 'G8' => $y8, 'G9' => $y9, 'G10' => $y10];

            // ─── 2. Subjects (Core JHS Areas + Journalism) ──────────────────────────
            $subjectDefs = [
                ['subject_code' => 'FIL',   'subject_name' => 'Filipino'],
                ['subject_code' => 'ENG',   'subject_name' => 'English'],
                ['subject_code' => 'MAT',   'subject_name' => 'Mathematics'],
                ['subject_code' => 'SCI',   'subject_name' => 'Science'],
                ['subject_code' => 'AP',    'subject_name' => 'Araling Panlipunan'],
                ['subject_code' => 'ESP',   'subject_name' => 'Edukasyon sa Pagpapakatao'],
                ['subject_code' => 'MAPEH', 'subject_name' => 'MAPEH'],
                ['subject_code' => 'TLE',   'subject_name' => 'Technology and Livelihood Education'],
                ['subject_code' => 'JOURN', 'subject_name' => 'Journalism'],
            ];
            $subjectsArr = [];
            foreach ($subjectDefs as $s) {
                $subjectsArr[] = Subject::firstOrCreate(['subject_code' => $s['subject_code']], $s);
            }

            // ─── 3. School Year & Terms ───────────────────────────────────────────────
            $sy = SchoolYear::updateOrCreate(
                ['school_year' => '2026-2027'],
                ['start_date' => '2026-08-01', 'end_date' => '2027-05-31', 'status' => 'active']
            );
            $q1 = Quarter::updateOrCreate(
                ['school_year_id' => $sy->id, 'sort_order' => 1],
                ['name' => '1st Term', 'short_name' => 'T1', 'start_date' => '2026-08-03', 'end_date' => '2026-10-30', 'status' => 'completed']
            );
            $q2 = Quarter::updateOrCreate(
                ['school_year_id' => $sy->id, 'sort_order' => 2],
                ['name' => '2nd Term', 'short_name' => 'T2', 'start_date' => '2026-11-03', 'end_date' => '2027-02-12', 'status' => 'completed']
            );
            $q3 = Quarter::updateOrCreate(
                ['school_year_id' => $sy->id, 'sort_order' => 3],
                ['name' => '3rd Term', 'short_name' => 'T3', 'start_date' => '2027-02-15', 'end_date' => '2027-05-28', 'status' => 'active']
            );
            $quarters = [$q1, $q2, $q3];

            // ─── 4. Section Names per year level ───────────────────────────────────────
            $sectionThemes = [
                'G7'  => ['SPJ', 'Hermes', 'Zues', 'Apollo'],
                'G8'  => ['SPJ', 'Diamond', 'Saphire', 'Amethyst', 'Jade'],
                'G9'  => ['SPJ', 'Poinsettia', 'Chrysanthmem', 'Adenium'],
                'G10' => ['SPJ', 'Cranberry', 'Strawberry', 'Blueberry'],
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
                    ['emp' => 'EMP-0032',  'fn' => 'Herminio', 'mn' => 'G', 'ln' => 'Salazar',    'contact' => '+639171110032'],
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
                }

                foreach ($sections as $sIdx => $secModel) {
                    $adviser = $teachersByYL[$ylCode][$sIdx % count($teachersByYL[$ylCode])];
                    $sys = SchoolYearSection::updateOrCreate(
                        ['school_year_id' => $sy->id, 'year_level_id' => $yl->id, 'section_id' => $secModel->id],
                        ['adviser_id' => $adviser->id, 'status' => 'active']
                    );
                    $allSys[] = [
                        'model' => $sys,
                        'ylCode' => $ylCode,
                        'sectionOrder' => $sIdx,
                        'sectionName' => $secModel->name,
                    ];
                }
            }

            // ─── 6. Section Subjects ──────────────────────────────────────────────────
            $sectionSubjectMap = [];
            foreach (['G7', 'G8', 'G9', 'G10'] as $ylCode) {
                $yl        = $ylModels[$ylCode];
                $tGroup    = $teachersByYL[$ylCode];
                $ylSysList = SchoolYearSection::with('section')
                    ->where('school_year_id', $sy->id)
                    ->where('year_level_id', $yl->id)
                    ->get();

                foreach ($ylSysList as $sys) {
                    $sectionSubjectMap[$sys->id] = [];
                    $secName = $sys->section?->name;

                    foreach ($subjectsArr as $sIdx => $subject) {
                        if ($subject->subject_code === 'JOURN' && strtoupper(trim($secName ?? '')) !== 'SPJ') {
                            continue;
                        }

                        $assignedTeacher = $tGroup[$sIdx % count($tGroup)];
                        $ss = SectionSubject::updateOrCreate(
                            ['school_year_section_id' => $sys->id, 'subject_id' => $subject->id],
                            ['teacher_id' => $assignedTeacher->id]
                        );
                        $sectionSubjectMap[$sys->id][] = [
                            'model' => $ss,
                            'code'  => $subject->subject_code,
                        ];
                    }
                }
            }

            // ─── 7. Students & High-Contrast Visual Analytics Distribution ────────────
            // Designed specifically so that charts comparing Year Levels, Sections,
            // Subjects, Terms (T1-T3), and Honor/Mastery Tiers display clear, realistic
            // contrast instead of flat averages:
            //
            // • Year Level Contrast:
            //   - Grade 10: Highest performing cohort (~89.5 avg, senior mastery, Highest/High Honors)
            //   - Grade 9 : Strong upper-year cohort (~86.2 avg, High Honors & Honors)
            //   - Grade 7 : Solid freshman cohort (~83.4 avg, enthusiastic transition)
            //   - Grade 8 : Challenging sophomore dip (~78.6 avg, 5 sections incl. Jade intervention)
            //
            // • Section-to-Section Contrast within each Year Level:
            //   - Section 0 (SPJ)              : Premier Journalism / Star Section (~91.5 - 94.5 avg)
            //   - Section 1 (Hermes/Diamond/..): High-Achieving Regular Section (~86.5 - 89.0 avg)
            //   - Section 2 (Zues/Saphire/...) : Mid-Performing Section (~82.0 - 85.0 avg)
            //   - Section 3 (Apollo/Amethyst/.): Developing Section (~77.5 - 80.5 avg)
            //   - Section 4 (Jade in G8)       : Intensive Intervention Section (~74.8 - 76.5 avg)
            //
            // • Subject Mastery Contrast:
            //   - High Mastery  : Journalism (~93), MAPEH (~89), EsP (~88), TLE (~86)
            //   - Moderate      : Filipino (~85), Araling Panlipunan (~83), English (~81)
            //   - Challenging   : Science (~79), Mathematics (~77)

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
            $middleNames = [
                'Cruz','Reyes','Santos','Garcia','Mendoza',
                'Bautista','Ramos','Aquino','Castro','Torres',
                'Flores','Navarro','Villanueva','Soriano','Mercado',
                'Pascual','Valdez','Diaz','Salazar','Morales',
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

            $birthYearByYL = [
                'G7'  => 2014,
                'G8'  => 2013,
                'G9'  => 2012,
                'G10' => 2011,
            ];

            $adminUser = User::where('role', 'admin')->first();
            $adminId   = $adminUser?->id ?? 1;

            $allCreatedStudents = [];
            $gradeRowsToUpsert = [];
            $studentCounter = 1;

            foreach ($allSys as $sIdx => $sysInfo) {
                $sys          = $sysInfo['model'];
                $ylCode       = $sysInfo['ylCode'];
                $sectionOrder = $sysInfo['sectionOrder'];
                $secSubjs     = $sectionSubjectMap[$sys->id] ?? [];
                $totalSubjects = count($secSubjs);
                $birthYear    = $birthYearByYL[$ylCode] ?? 2012;

                for ($slot = 0; $slot < 15; $slot++) {
                    $stId = sprintf('2026-%04d', $studentCounter);
                    $fn   = $firstNames[($studentCounter - 1) % count($firstNames)];
                    $mn   = $middleNames[($studentCounter + $sIdx) % count($middleNames)];
                    $ln   = $lastNames[($studentCounter - 1) % count($lastNames)];
                    $bMonth = (($studentCounter % 12) + 1);
                    $bDay   = (($studentCounter % 27) + 1);
                    $bDate  = sprintf('%04d-%02d-%02d', $birthYear, $bMonth, $bDay);

                    $stUser = User::updateOrCreate(['login_id' => $stId], [
                        'name'       => "{$fn} {$ln}",
                        'email'      => strtolower($stId) . '@student.dmmnhs.edu.ph',
                        'password'   => $hashedPassword,
                        'role'       => 'student',
                        'status'     => 'active',
                    ]);

                    $student = Student::updateOrCreate(['user_id' => $stUser->id], [
                        'student_number' => $stId,
                        'first_name'     => $fn,
                        'middle_name'    => $mn,
                        'last_name'      => $ln,
                        'gender'         => ($studentCounter % 2 === 0) ? 'Female' : 'Male',
                        'birthdate'      => $bDate,
                        'contact_number' => sprintf('+63917%07d', $studentCounter),
                    ]);
                    $allCreatedStudents[] = $student;

                    $enrollment = StudentEnrollment::updateOrCreate([
                        'student_id'             => $student->id,
                        'school_year_section_id' => $sys->id,
                    ], [
                        'enrollment_date'        => '2026-08-01',
                        'status'                 => 'enrolled',
                    ]);

                    foreach ($secSubjs as $subIdx => $ssInfo) {
                        $ss      = $ssInfo['model'];
                        $subCode = $ssInfo['code'];

                        $tMarks = $this->generateDynamicTermMarks(
                            $ylCode,
                            $sectionOrder,
                            $slot,
                            $subCode,
                            $subIdx,
                            $totalSubjects,
                            $sIdx
                        );

                        foreach ($quarters as $qIdx => $quarter) {
                            $gradeVal = $tMarks[$qIdx];
                            $gradeRowsToUpsert[] = [
                                'student_enrollment_id' => $enrollment->id,
                                'section_subject_id'    => $ss->id,
                                'quarter_id'            => $quarter->id,
                                'grade'                 => $gradeVal,
                                'remarks'               => $gradeVal >= 75.00 ? 'PASSED' : 'FAILED',
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

            // Bulk upsert all grades in chunks of 500
            foreach (array_chunk($gradeRowsToUpsert, 500) as $chunk) {
                Grade::upsert(
                    $chunk,
                    ['student_enrollment_id', 'section_subject_id', 'quarter_id'],
                    ['grade', 'remarks', 'status', 'created_by', 'updated_at']
                );
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

            $totalStudents = count($allCreatedStudents);
            $sIdx = 0;
            $parentCounter = 1;

            while ($sIdx < $totalStudents) {
                $remaining = $totalStudents - $sIdx;
                $numChildren = ($remaining === 1) ? 1 : (($parentCounter % 3 === 0) ? 2 : 1);

                $pid = sprintf('PARENT-%04d', $parentCounter);
                $fn  = $parentFirstNames[($parentCounter - 1) % count($parentFirstNames)];
                $primaryStudent = $allCreatedStudents[$sIdx];
                $ln  = $primaryStudent->last_name;

                $pUser = User::updateOrCreate(['login_id' => $pid], [
                    'name'       => "{$fn} {$ln}",
                    'email'      => strtolower(str_replace('-', '', $pid)) . '@parent.dmmnhs.edu.ph',
                    'password'   => $hashedPassword,
                    'role'       => 'parent',
                    'status'     => 'active',
                ]);

                $parent = ParentModel::updateOrCreate(['user_id' => $pUser->id], [
                    'first_name'     => $fn,
                    'last_name'      => $ln,
                    'contact_number' => sprintf('+63917%07d', 8000000 + $parentCounter),
                ]);

                for ($c = 0; $c < $numChildren; $c++) {
                    $st = $allCreatedStudents[$sIdx + $c];
                    $parent->students()->syncWithoutDetaching([
                        $st->id => [
                            'relationship' => 'Parent',
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ],
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
                'description'  => "SY 2026-2027 | 32 teachers | 17 sections | {$totalStudents} students | High-contrast analytics across Year Levels, Sections, Subjects, and Terms.",
            ]);
        });
    }

    /**
     * Generate realistic, high-contrast term grades [T1, T2, T3] that create
     * clear visual differentiation across:
     *   1. Year Levels (G10 > G9 > G7 > G8)
     *   2. Sections within a Year Level (SPJ > Sec 1 > Sec 2 > Sec 3 > Sec 4)
     *   3. Subjects (JOURN/MAPEH/ESP > TLE/FIL/AP > ENG > SCI > MAT)
     *   4. Terms (T1 -> T2 -> T3 with section-specific trajectory curves)
     *   5. Honor Tiers (Highest Honors >=98, High Honors 95-97, Honors 90-94)
     */
    private function generateDynamicTermMarks(
        string $ylCode,
        int $sectionOrder,
        int $slot,
        string $subCode,
        int $subIdx,
        int $totalSubjects,
        int $globalSectionIndex
    ): array {
        // 1. Year-Level Academic Offset (creates clear contrast in Year Level Bar Chart)
        $ylOffsetMap = [
            'G10' => +3.6, // Senior mastery (~89-90 overall)
            'G9'  => +1.6, // Strong upper-year (~86-87 overall)
            'G7'  => -0.4, // Freshman baseline (~83-84 overall)
            'G8'  => -3.4, // Sophomore curriculum dip (~78-79 overall)
        ];
        $ylOffset = $ylOffsetMap[$ylCode] ?? 0.0;

        // 2. Section Archetype Offset (creates strong contrast when comparing sections)
        //    0 = SPJ (Star Section), 1 = High-Performing, 2 = Mid-Tier, 3 = Developing, 4 = Intervention
        $sectionOffsetMap = [
            0 => +6.5, // SPJ Star Section (~91 - 94 avg)
            1 => +2.8, // High-Performing Regular Section (~86 - 89 avg)
            2 => -0.5, // Mid-Performing Section (~82 - 85 avg)
            3 => -4.2, // Developing Section (~77 - 80 avg)
            4 => -6.8, // Intensive Intervention Section (G8 Jade ~75 - 76 avg)
        ];
        $secOffset = $sectionOffsetMap[$sectionOrder] ?? 0.0;

        // 3. Subject Difficulty / Mastery Offset (creates clear steps in Subject Performance Chart)
        $subjectOffsetMap = [
            'JOURN' => +3.2,
            'MAPEH' => +3.4,
            'ESP'   => +2.6,
            'TLE'   => +1.5,
            'FIL'   => +0.6,
            'AP'    => -0.4,
            'ENG'   => -1.4,
            'SCI'   => -2.8,
            'MAT'   => -4.2,
        ];
        $subOffset = $subjectOffsetMap[$subCode] ?? 0.0;

        // 4. Special Top-Achiever Slots (Guarantees varied Highest Honors, High Honors, and Honors counts per Year Level)
        //    - G10 SPJ has 2 With Highest Honors (Slots 0, 1) and 3 With High Honors (Slots 2, 3, 4)
        //    - G9 SPJ has 1 With Highest Honors (Slot 0) and 2 With High Honors (Slots 1, 2)
        //    - G7 SPJ has 2 With High Honors (Slots 0, 1)
        //    - G8 SPJ has 1 With High Honors (Slot 0)
        if ($sectionOrder === 0) {
            if ($ylCode === 'G10' && ($slot === 0 || $slot === 1)) {
                // With Highest Honors (GWA 98.2 - 99.0, all grades >= 97)
                $b = 98.0 + (($subIdx + $slot) % 2);
                return [max(97.0, $b - 1.0), $b, min(99.0, $b + 1.0)];
            }
            if ($ylCode === 'G9' && $slot === 0) {
                // With Highest Honors (GWA ~98.3)
                $b = 98.0 + ($subIdx % 2);
                return [97.0, $b, min(99.0, $b + 1.0)];
            }
            if (
                ($ylCode === 'G10' && in_array($slot, [2, 3, 4])) ||
                ($ylCode === 'G9' && in_array($slot, [1, 2])) ||
                ($ylCode === 'G7' && in_array($slot, [0, 1])) ||
                ($ylCode === 'G8' && $slot === 0)
            ) {
                // With High Honors (GWA 95.3 - 96.8, all grades >= 92)
                $b = 95.0 + (($subIdx + $slot) % 3);
                return [max(93.0, $b - 1.0), $b, min(98.0, $b + 1.0)];
            }
        }

        // Also place 1 With High Honors in Section 1 of G10 and G9
        if ($sectionOrder === 1 && (($ylCode === 'G10' && $slot === 0) || ($ylCode === 'G9' && $slot === 0))) {
            $b = 95.0 + ($subIdx % 2);
            return [94.0, $b, min(97.0, $b + 1.0)];
        }

        // 5. Base Student Slot Profile within the Section (Slots 0..14)
        //    Produces a natural bell-curve spread inside each section
        $slotBaseMap = [
            0  => 91.5, // Top of class (With Honors in regular sections)
            1  => 90.2, // Second in class (With Honors in high/mid sections)
            2  => 88.0, // Strong performer
            3  => 86.8,
            4  => 85.6,
            5  => 84.4,
            6  => 83.5,
            7  => 82.6, // Median student
            8  => 81.8,
            9  => 80.8,
            10 => 79.6,
            11 => 78.5,
            12 => 77.2, // Borderline passing
            13 => 75.5, // Struggling / Single-subject at-risk in lower sections
            14 => 73.5, // Multi-subject at-risk in lower sections
        ];
        $slotBase = $slotBaseMap[$slot] ?? 82.0;

        // Combine offsets with a dampened weight so grades stay within realistic DepEd bounds (68 - 98)
        $rawTarget = $slotBase + ($ylOffset * 0.65) + ($secOffset * 0.70) + ($subOffset * 0.65);

        // Add deterministic individual variation per student-subject so rows aren't robotic
        $deterministicJitter = ((($slot * 5 + $subIdx * 3 + $globalSectionIndex) % 5) - 2) * 0.4;
        $base = round($rawTarget + $deterministicJitter, 0);

        // Enforce struggling / failing behavior only in non-SPJ sections for slots 13 & 14
        if ($sectionOrder >= 2 && $slot === 13) {
            $failSubj = ($globalSectionIndex * 3 + 2) % $totalSubjects;
            if ($subIdx === $failSubj) {
                return [70.0, 72.0, 71.0];
            }
        }
        if ($sectionOrder >= 2 && $slot === 14) {
            $failSubj1 = ($globalSectionIndex * 2 + 1) % $totalSubjects;
            $failSubj2 = ($globalSectionIndex * 2 + 3) % $totalSubjects;
            if ($subIdx === $failSubj1) {
                return [68.0, 70.0, 69.0];
            }
            if ($subIdx === $failSubj2 && $sectionOrder >= 3) {
                return [71.0, 69.0, 72.0];
            }
        }

        // Keep SPJ students strictly passing (>= 83)
        if ($sectionOrder === 0) {
            $base = max(85.0, min(94.0, $base));
        } else {
            $base = max(73.0, min(94.0, $base));
        }

        // 6. Section-Specific Term Trajectory (T1 -> T2 -> T3)
        //    Ensures multi-section line charts show distinct, non-parallel curves
        switch ($sectionOrder) {
            case 0: // SPJ: Steady high climb (+1.5 per term)
                $t1 = $base - 1.0;
                $t2 = $base + 0.5;
                $t3 = $base + 2.0;
                break;
            case 1: // Section 1: Strong T2-T3 surge
                $t1 = $base - 2.0;
                $t2 = $base + 0.5;
                $t3 = $base + 2.5;
                break;
            case 2: // Section 2: Mid-year dip in T2 then strong T3 recovery
                $t1 = $base;
                $t2 = $base - 1.5;
                $t3 = $base + 2.0;
                break;
            case 3: // Section 3: Gradual improvement from low T1 start
                $t1 = $base - 2.5;
                $t2 = $base - 0.5;
                $t3 = $base + 1.5;
                break;
            default: // Section 4 (Jade): Early struggle then modest recovery
                $t1 = $base - 2.0;
                $t2 = $base - 1.0;
                $t3 = $base + 1.0;
                break;
        }

        $clamp = fn(float $v) => round(max(67.0, min(99.0, $v)), 0);

        return [$clamp($t1), $clamp($t2), $clamp($t3)];
    }
}
