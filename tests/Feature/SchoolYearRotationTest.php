<?php

namespace Tests\Feature;

use App\Models\SchoolYear;
use App\Models\SchoolYearSection;
use App\Models\Section;
use App\Models\YearLevel;
use App\Services\SchoolYearRotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolYearRotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_year_creation_automatically_generates_grade_levels_and_rotates_sections(): void
    {
        // 1. Create Base School Year 2025-2026 with explicit section themes
        $sy2025 = SchoolYear::create([
            'school_year' => '2025-2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-05-31',
            'status' => 'completed',
        ]);

        $g7 = YearLevel::firstOrCreate(['code' => 'G7'], ['name' => 'Grade 7', 'sort_order' => 1]);
        $g8 = YearLevel::firstOrCreate(['code' => 'G8'], ['name' => 'Grade 8', 'sort_order' => 2]);
        $g9 = YearLevel::firstOrCreate(['code' => 'G9'], ['name' => 'Grade 9', 'sort_order' => 3]);
        $g10 = YearLevel::firstOrCreate(['code' => 'G10'], ['name' => 'Grade 10', 'sort_order' => 4]);

        $planetA = Section::create(['name' => '7-Planet A']);
        $planetB = Section::create(['name' => '8-Planet B']);
        $planetC = Section::create(['name' => '9-Planet C']);
        $planetD = Section::create(['name' => '10-Planet D']);

        SchoolYearSection::create(['school_year_id' => $sy2025->id, 'year_level_id' => $g7->id, 'section_id' => $planetA->id]);
        SchoolYearSection::create(['school_year_id' => $sy2025->id, 'year_level_id' => $g8->id, 'section_id' => $planetB->id]);
        SchoolYearSection::create(['school_year_id' => $sy2025->id, 'year_level_id' => $g9->id, 'section_id' => $planetC->id]);
        SchoolYearSection::create(['school_year_id' => $sy2025->id, 'year_level_id' => $g10->id, 'section_id' => $planetD->id]);

        // 2. Create SY 2026-2027 and trigger rotation
        $sy2026 = SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);
        SchoolYearRotationService::generateStructureForSchoolYear($sy2026);

        // Assert SY 2026-2027 rotation:
        // Grade 7 -> Planet D (from prev Grade 10)
        // Grade 8 -> Planet A (from prev Grade 7)
        // Grade 9 -> Planet B (from prev Grade 8)
        // Grade 10 -> Planet C (from prev Grade 9)
        $this->assertEquals(['10-Planet D'], $this->getSectionNamesForSY($sy2026->id, $g7->id));
        $this->assertEquals(['7-Planet A'], $this->getSectionNamesForSY($sy2026->id, $g8->id));
        $this->assertEquals(['8-Planet B'], $this->getSectionNamesForSY($sy2026->id, $g9->id));
        $this->assertEquals(['9-Planet C'], $this->getSectionNamesForSY($sy2026->id, $g10->id));

        // 3. Create SY 2027-2028 and trigger rotation
        $sy2027 = SchoolYear::create([
            'school_year' => '2027-2028',
            'start_date' => '2027-08-01',
            'end_date' => '2028-05-31',
            'status' => 'draft',
        ]);
        SchoolYearRotationService::generateStructureForSchoolYear($sy2027);

        // Assert SY 2027-2028 rotation:
        // Grade 7 -> Planet C (from prev Grade 10)
        // Grade 8 -> Planet D (from prev Grade 7)
        // Grade 9 -> Planet A (from prev Grade 8)
        // Grade 10 -> Planet B (from prev Grade 9)
        $this->assertEquals(['9-Planet C'], $this->getSectionNamesForSY($sy2027->id, $g7->id));
        $this->assertEquals(['10-Planet D'], $this->getSectionNamesForSY($sy2027->id, $g8->id));
        $this->assertEquals(['7-Planet A'], $this->getSectionNamesForSY($sy2027->id, $g9->id));
        $this->assertEquals(['8-Planet B'], $this->getSectionNamesForSY($sy2027->id, $g10->id));

        // 4. Verify previous School Year data is unchanged
        $this->assertEquals(['7-Planet A'], $this->getSectionNamesForSY($sy2025->id, $g7->id));
        $this->assertEquals(['8-Planet B'], $this->getSectionNamesForSY($sy2025->id, $g8->id));
        $this->assertEquals(['9-Planet C'], $this->getSectionNamesForSY($sy2025->id, $g9->id));
        $this->assertEquals(['10-Planet D'], $this->getSectionNamesForSY($sy2025->id, $g10->id));
    }

    private function getSectionNamesForSY(int $syId, int $yearLevelId): array
    {
        return SchoolYearSection::where('school_year_id', $syId)
            ->where('year_level_id', $yearLevelId)
            ->with('section')
            ->get()
            ->map(fn($sys) => $sys->section->name)
            ->values()
            ->toArray();
    }
}
