<?php

namespace Tests\Feature;

use App\Livewire\Admin\SchoolYearManagement;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\SchoolYearService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SequentialSchoolYearValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_initial_school_year_can_be_established_when_database_is_empty(): void
    {
        $this->assertDatabaseCount('school_years', 0);

        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2026-2027')
            ->set('start_date', '2026-08-01')
            ->set('end_date', '2027-05-31')
            ->set('status', 'active')
            ->call('createSchoolYear')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('school_years', [
            'school_year' => '2026-2027',
            'status' => 'active',
        ]);
    }

    public function test_rejects_non_consecutive_calendar_years_for_initial_school_year(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2026-2028')
            ->set('start_date', '2026-08-01')
            ->set('end_date', '2028-05-31')
            ->call('createSchoolYear')
            ->assertHasErrors(['school_year']);

        $this->assertDatabaseCount('school_years', 0);
    }

    public function test_rejects_same_calendar_year(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2026-2026')
            ->set('start_date', '2026-01-01')
            ->set('end_date', '2026-12-31')
            ->call('createSchoolYear')
            ->assertHasErrors(['school_year']);

        $this->assertDatabaseCount('school_years', 0);
    }

    public function test_subsequent_school_year_must_follow_chronological_sequence(): void
    {
        // Setup initial school year
        SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);

        // Valid consecutive school year
        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2027-2028')
            ->set('start_date', '2027-08-01')
            ->set('end_date', '2028-05-31')
            ->set('status', 'draft')
            ->call('createSchoolYear')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('school_years', [
            'school_year' => '2027-2028',
            'status' => 'draft',
        ]);
    }

    public function test_rejects_previous_school_year_with_clear_validation_message(): void
    {
        SchoolYear::create([
            'school_year' => '2027-2028',
            'start_date' => '2027-08-01',
            'end_date' => '2028-05-31',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2026-2027')
            ->set('start_date', '2026-08-01')
            ->set('end_date', '2027-05-31')
            ->call('createSchoolYear');

        $component->assertHasErrors(['school_year']);

        $errors = $component->errors()->get('school_year');
        $this->assertStringContainsString('You cannot create a previous school year. The next available school year is 2028-2029.', $errors[0]);
    }

    public function test_rejects_duplicate_school_year(): void
    {
        SchoolYear::create([
            'school_year' => '2027-2028',
            'start_date' => '2027-08-01',
            'end_date' => '2028-05-31',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2027-2028')
            ->set('start_date', '2027-08-01')
            ->set('end_date', '2028-05-31')
            ->call('createSchoolYear')
            ->assertHasErrors(['school_year']);
    }

    public function test_rejects_skipped_school_year(): void
    {
        SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2028-2029')
            ->set('start_date', '2028-08-01')
            ->set('end_date', '2029-05-31')
            ->call('createSchoolYear');

        $component->assertHasErrors(['school_year']);

        $errors = $component->errors()->get('school_year');
        $this->assertStringContainsString('School years must follow chronological sequence. The next available school year is 2027-2028.', $errors[0]);
    }

    public function test_automatic_next_school_year_determination(): void
    {
        SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);

        $this->assertEquals('2027-2028', SchoolYearService::getNextAvailableSchoolYear());

        SchoolYear::create([
            'school_year' => '2027-2028',
            'start_date' => '2027-08-01',
            'end_date' => '2028-05-31',
            'status' => 'draft',
        ]);

        $this->assertEquals('2028-2029', SchoolYearService::getNextAvailableSchoolYear());
    }

    public function test_modal_auto_suggests_next_school_year_and_dates(): void
    {
        SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->assertSet('school_year', '2027-2028')
            ->assertSet('start_date', '2027-08-01')
            ->assertSet('end_date', '2028-05-31')
            ->assertSet('isInitial', false);
    }

    public function test_historical_school_years_are_preserved_when_new_school_year_is_created(): void
    {
        $sy2026 = SchoolYear::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-05-31',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(SchoolYearManagement::class)
            ->call('openCreateModal')
            ->set('school_year', '2027-2028')
            ->set('start_date', '2027-08-01')
            ->set('end_date', '2028-05-31')
            ->set('status', 'active')
            ->call('createSchoolYear')
            ->assertHasNoErrors();

        // 2026-2027 is still in database and its status transitioned safely to completed
        $this->assertDatabaseHas('school_years', [
            'id' => $sy2026->id,
            'school_year' => '2026-2027',
            'status' => 'completed',
        ]);

        // 2027-2028 is the new active school year
        $this->assertDatabaseHas('school_years', [
            'school_year' => '2027-2028',
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('school_years', 2);
    }
}
