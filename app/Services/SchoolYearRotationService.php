<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Quarter;
use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\YearLevel;
use Illuminate\Support\Facades\DB;

class SchoolYearRotationService
{
    /**
     * Generate Grade Levels, rotated Sections, default Quarters, and Subject loads for a new School Year.
     * 
     * Rotation logic:
     * - Grade 7 gets section type previously assigned to Grade 10 (last grade level)
     * - Grade 8 gets section type previously assigned to Grade 7
     * - Grade 9 gets section type previously assigned to Grade 8
     * - Grade 10 gets section type previously assigned to Grade 9
     */
    public static function generateStructureForSchoolYear(SchoolYear $sy): void
    {
        DB::transaction(function () use ($sy) {
            // 1. Ensure Standard Grade Levels exist in year_levels table
            $defaultGradeLevels = [
                ['code' => 'G7',  'name' => 'Grade 7',  'sort_order' => 1],
                ['code' => 'G8',  'name' => 'Grade 8',  'sort_order' => 2],
                ['code' => 'G9',  'name' => 'Grade 9',  'sort_order' => 3],
                ['code' => 'G10', 'name' => 'Grade 10', 'sort_order' => 4],
            ];

            foreach ($defaultGradeLevels as $glDef) {
                YearLevel::firstOrCreate(['code' => $glDef['code']], $glDef);
            }

            $yearLevels = YearLevel::orderBy('sort_order', 'asc')->get();
            $n = $yearLevels->count();

            if ($n === 0) {
                return;
            }

            // 2. Ensure Core DepEd JHS Subjects exist in subjects table
            $defaultSubjects = [
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

            foreach ($defaultSubjects as $subDef) {
                Subject::firstOrCreate(['subject_code' => $subDef['subject_code']], $subDef);
            }

            $allSubjects = Subject::all();

            // 3. Identify the previous School Year relative to $sy
            $previousSy = SchoolYear::where('id', '!=', $sy->id)
                ->where('school_year', '<', $sy->school_year)
                ->orderBy('school_year', 'desc')
                ->first();

            if (!$previousSy) {
                $previousSy = SchoolYear::where('id', '!=', $sy->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // 4. Define Default Section Themes per Grade Level Code
            $defaultThemes = [
                'G7'  => ['SPJ', 'Hermes', 'Zues', 'Apollo'],
                'G8'  => ['SPJ', 'Diamond', 'Saphire', 'Amethyst', 'Jade'],
                'G9'  => ['SPJ', 'Poinsettia', 'Chrysanthmem', 'Adenium'],
                'G10' => ['SPJ', 'Cranberry', 'Strawberry', 'Blueberry'],
            ];

            $createdSectionInstances = 0;

            // 5. Create Section Instances for each Grade Level and attach Core Subject Teacher Loads
            foreach ($yearLevels as $yl) {
                $sectionNames = $defaultThemes[$yl->code] ?? [];

                if ($previousSy) {
                    $prevSysList = SchoolYearSection::where('school_year_id', $previousSy->id)
                        ->where('year_level_id', $yl->id)
                        ->with('section')
                        ->orderBy('id')
                        ->get();

                    if ($prevSysList->isNotEmpty()) {
                        $sectionNames = $prevSysList->map(fn($sys) => $sys->section?->name)->filter()->values()->all();
                    }
                }

                foreach ($sectionNames as $secName) {
                    // Get or create section master record
                    $sectionModel = Section::firstOrCreate(['name' => $secName]);

                    // Create independent SchoolYearSection instance for $sy
                    $sys = SchoolYearSection::firstOrCreate(
                        [
                            'school_year_id' => $sy->id,
                            'year_level_id'  => $yl->id,
                            'section_id'     => $sectionModel->id,
                        ],
                        [
                            'adviser_id' => null,
                            'status'     => 'active',
                        ]
                    );

                    if ($sys->wasRecentlyCreated) {
                        $createdSectionInstances++;
                    }

                    // Auto-populate core subject loads for Subject Teacher Loads table
                    foreach ($allSubjects as $sub) {
                        if ($sub->subject_code === 'JOURN' && strtoupper(trim($secName ?? '')) !== 'SPJ') {
                            continue;
                        }
                        SectionSubject::firstOrCreate([
                            'school_year_section_id' => $sys->id,
                            'subject_id'             => $sub->id,
                        ]);
                    }
                }
            }

            // 5. Ensure Terms are created for $sy
            $quarters = [
                ['name' => '1st Term', 'short_name' => 'T1', 'sort_order' => 1],
                ['name' => '2nd Term', 'short_name' => 'T2', 'sort_order' => 2],
                ['name' => '3rd Term', 'short_name' => 'T3', 'sort_order' => 3],
            ];

            foreach ($quarters as $q) {
                Quarter::firstOrCreate(
                    [
                        'school_year_id' => $sy->id,
                        'sort_order' => $q['sort_order'],
                    ],
                    [
                        'name' => $q['name'],
                        'short_name' => $q['short_name'],
                        'status' => $q['sort_order'] === 1 ? 'active' : 'upcoming',
                    ]
                );
            }

            // 6. Log Activity
            $prevSyLabel = $previousSy ? "previous SY {$previousSy->school_year}" : "default section themes";
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Auto-Configured School Year Sections & Rotation',
                'subject_type' => SchoolYear::class,
                'subject_id' => $sy->id,
                'description' => "Automatically generated Grade Levels and {$createdSectionInstances} rotated section instances for SY {$sy->school_year} based on {$prevSyLabel}.",
            ]);
        });
    }
}
