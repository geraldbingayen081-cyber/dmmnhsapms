<?php

namespace Tests\Feature;

use App\Livewire\Parent\ParentAcademicHistory;
use App\Livewire\Student\StudentAcademicHistory;
use App\Livewire\Student\StudentGrades;
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
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Helper to set up standard Year Levels (G7 to G10).
     */
    protected function setupYearLevels(): array
    {
        return [
            'G7' => YearLevel::firstOrCreate(['code' => 'G7'], ['name' => 'Grade 7',  'sort_order' => 1]),
            'G8' => YearLevel::firstOrCreate(['code' => 'G8'], ['name' => 'Grade 8',  'sort_order' => 2]),
            'G9' => YearLevel::firstOrCreate(['code' => 'G9'], ['name' => 'Grade 9',  'sort_order' => 3]),
            'G10' => YearLevel::firstOrCreate(['code' => 'G10'], ['name' => 'Grade 10', 'sort_order' => 4]),
        ];
    }

    /**
     * Helper to create a student with user account.
     */
    protected function createStudent(string $lrn = '2026-0001', string $firstName = 'Juan', string $lastName = 'Dela Cruz'): Student
    {
        $user = User::factory()->create([
            'login_id' => $lrn,
            'name' => "{$firstName} {$lastName}",
            'role' => 'student',
        ]);

        return Student::create([
            'user_id' => $user->id,
            'student_number' => $lrn,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }

    /**
     * Helper to create a parent with user account.
     */
    protected function createParent(string $loginId = 'PARENT-0001', string $name = 'Maria Dela Cruz'): ParentModel
    {
        $user = User::factory()->create([
            'login_id' => $loginId,
            'name' => $name,
            'role' => 'parent',
        ]);

        return ParentModel::create([
            'user_id' => $user->id,
            'first_name' => explode(' ', $name)[0] ?? 'Maria',
            'last_name' => explode(' ', $name)[1] ?? 'Dela Cruz',
        ]);
    }

    /**
     * Helper to create a complete school year with terms and section.
     */
    protected function createSchoolYearEnvironment(string $syName, string $status, YearLevel $yearLevel, string $sectionName): array
    {
        $sy = SchoolYear::create([
            'school_year' => $syName,
            'start_date' => '2024-08-01',
            'end_date' => '2025-05-31',
            'status' => $status,
        ]);

        $q1 = Quarter::firstOrCreate(['school_year_id' => $sy->id, 'sort_order' => 1], ['name' => '1st Term', 'short_name' => 'T1', 'status' => 'completed']);
        $q2 = Quarter::firstOrCreate(['school_year_id' => $sy->id, 'sort_order' => 2], ['name' => '2nd Term', 'short_name' => 'T2', 'status' => 'completed']);
        $q3 = Quarter::firstOrCreate(['school_year_id' => $sy->id, 'sort_order' => 3], ['name' => '3rd Term', 'short_name' => 'T3', 'status' => 'completed']);

        $section = Section::firstOrCreate(['name' => $sectionName]);
        $sys = SchoolYearSection::firstOrCreate(
            ['school_year_id' => $sy->id, 'year_level_id' => $yearLevel->id, 'section_id' => $section->id],
            ['status' => 'active']
        );

        $math = Subject::firstOrCreate(['subject_code' => 'MAT'], ['subject_name' => 'Mathematics']);
        $eng = Subject::firstOrCreate(['subject_code' => 'ENG'], ['subject_name' => 'English']);
        $sci = Subject::firstOrCreate(['subject_code' => 'SCI'], ['subject_name' => 'Science']);

        $ssMath = SectionSubject::firstOrCreate(['school_year_section_id' => $sys->id, 'subject_id' => $math->id]);
        $ssEng = SectionSubject::firstOrCreate(['school_year_section_id' => $sys->id, 'subject_id' => $eng->id]);
        $ssSci = SectionSubject::firstOrCreate(['school_year_section_id' => $sys->id, 'subject_id' => $sci->id]);

        return [
            'sy' => $sy,
            'quarters' => [$q1, $q2, $q3],
            'section' => $section,
            'sys' => $sys,
            'subjects' => [$ssMath, $ssEng, $ssSci],
        ];
    }

    /**
     * Test guests cannot access academic history routes.
     */
    public function test_guests_cannot_access_academic_history(): void
    {
        $this->get(route('student.history'))->assertRedirect(route('login'));
        $this->get(route('parent.history'))->assertRedirect(route('login'));
    }

    /**
     * Test role-based protection: Student cannot access parent route and vice versa.
     */
    public function test_role_based_access_restriction_between_student_and_parent(): void
    {
        $this->setupYearLevels();
        $student = $this->createStudent();
        $parent = $this->createParent();

        // Student accessing parent route is blocked with 403 Forbidden
        $this->actingAs($student->user)
            ->get(route('parent.history'))
            ->assertForbidden();

        // Parent accessing student route is blocked with 403 Forbidden
        $this->actingAs($parent->user)
            ->get(route('student.history'))
            ->assertForbidden();
    }

    /**
     * Test student with records across all four grade levels (Grade 7 to 10).
     */
    public function test_student_with_all_four_grade_levels_displays_complete_history(): void
    {
        $yl = $this->setupYearLevels();
        $student = $this->createStudent();

        // Create 4 distinct school years for G7, G8, G9, G10
        $envG7 = $this->createSchoolYearEnvironment('2023-2024', 'completed', $yl['G7'], 'Sampaguita');
        $envG8 = $this->createSchoolYearEnvironment('2024-2025', 'completed', $yl['G8'], 'Rizal');
        $envG9 = $this->createSchoolYearEnvironment('2025-2026', 'completed', $yl['G9'], 'Lily');
        $envG10 = $this->createSchoolYearEnvironment('2026-2027', 'active', $yl['G10'], 'Cranberry');

        // Enroll student in all 4 levels
        $enrG7 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG7['sys']->id, 'status' => 'completed', 'enrollment_date' => '2023-08-01']);
        $enrG8 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG8['sys']->id, 'status' => 'completed', 'enrollment_date' => '2024-08-01']);
        $enrG9 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG9['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);
        $enrG10 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG10['sys']->id, 'status' => 'enrolled', 'enrollment_date' => '2026-08-01']);

        // Insert grades for G7 (Math: 88, 89, 90)
        foreach ($envG7['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG7->id, 'section_subject_id' => $envG7['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 88.00, 'status' => 'approved']);
        }
        // Insert grades for G8 (Math: 91, 92, 93)
        foreach ($envG8['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG8->id, 'section_subject_id' => $envG8['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 91.00, 'status' => 'approved']);
        }
        // Insert grades for G9 (Math: 94, 94, 94)
        foreach ($envG9['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG9->id, 'section_subject_id' => $envG9['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 94.00, 'status' => 'approved']);
        }
        // Insert grades for G10 (Math: 95, 95, 95)
        foreach ($envG10['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG10->id, 'section_subject_id' => $envG10['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 95.00, 'status' => 'approved']);
        }

        // Test Livewire component
        $component = Livewire::actingAs($student->user)
            ->test(StudentAcademicHistory::class);

        $component->assertStatus(200);
        $component->assertSee('Student Academic History');
        $component->assertSee('Grade 7');
        $component->assertSee('Grade 8');
        $component->assertSee('Grade 9');
        $component->assertSee('Grade 10');
        $component->assertSee('2023-2024');
        $component->assertSee('2024-2025');
        $component->assertSee('2025-2026');
        $component->assertSee('2026-2027');

        // Test switching between grade levels
        $component->call('selectGradeLevel', 'G7')
            ->assertSet('selectedGradeLevel', 'G7')
            ->assertSee('2023-2024')
            ->assertSee('Sampaguita');

        $component->call('selectGradeLevel', 'G10')
            ->assertSet('selectedGradeLevel', 'G10')
            ->assertSee('2026-2027')
            ->assertSee('Cranberry');
    }

    /**
     * Test student with records from only one or two grade levels.
     * Ensures unreached grade levels (e.g. G9 & G10) clearly indicate unavailable without fake grades.
     */
    public function test_student_with_partial_records_does_not_fabricate_unreached_grade_levels(): void
    {
        $yl = $this->setupYearLevels();
        $student = $this->createStudent('2026-0002', 'Maria', 'Santos');

        // Only Grade 7 and Grade 8 exist
        $envG7 = $this->createSchoolYearEnvironment('2025-2026', 'completed', $yl['G7'], 'Hermes');
        $envG8 = $this->createSchoolYearEnvironment('2026-2027', 'active', $yl['G8'], 'Diamond');

        $enrG7 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG7['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);
        $enrG8 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG8['sys']->id, 'status' => 'enrolled', 'enrollment_date' => '2026-08-01']);

        // Only G7 has recorded grades
        foreach ($envG7['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG7->id, 'section_subject_id' => $envG7['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 85.00, 'status' => 'approved']);
        }

        $component = Livewire::actingAs($student->user)
            ->test(StudentAcademicHistory::class);

        $component->assertStatus(200);

        // Grade 7 has records
        $component->call('selectGradeLevel', 'G7')
            ->assertSee('Grade 7')
            ->assertSee('Hermes')
            ->assertSee('85.0');

        // Grade 9 has no records: verify clear unavailable message
        $component->call('selectGradeLevel', 'G9')
            ->assertSee('No Records Available for Grade 9')
            ->assertSee('The learner has not yet enrolled in or completed this grade level');

        // Grade 10 has no records: verify clear unavailable message
        $component->call('selectGradeLevel', 'G10')
            ->assertSee('No Records Available for Grade 10')
            ->assertSee('The learner has not yet enrolled in or completed this grade level');
    }

    /**
     * Test parent can view linked child's academic history.
     */
    public function test_parent_can_view_linked_child_academic_history(): void
    {
        $yl = $this->setupYearLevels();
        $student = $this->createStudent('2026-0003', 'Noah', 'Reyes');
        $parent = $this->createParent('PARENT-0002', 'Elena Reyes');

        // Link student to parent
        $parent->students()->attach($student->id, ['relationship' => 'Parent']);

        $envG7 = $this->createSchoolYearEnvironment('2025-2026', 'completed', $yl['G7'], 'Zues');
        $enrG7 = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envG7['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);

        foreach ($envG7['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrG7->id, 'section_subject_id' => $envG7['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 89.00, 'status' => 'approved']);
        }

        Livewire::actingAs($parent->user)
            ->test(ParentAcademicHistory::class)
            ->assertStatus(200)
            ->assertSee('Academic History — Noah Reyes')
            ->assertSee('Grade 7')
            ->assertSee('Zues')
            ->assertSee('89.0');
    }

    /**
     * Test parent with multiple linked children can switch between children.
     */
    public function test_parent_with_multiple_children_can_switch_children(): void
    {
        $yl = $this->setupYearLevels();
        $student1 = $this->createStudent('2026-0004', 'Child One', 'Torres');
        $student2 = $this->createStudent('2026-0005', 'Child Two', 'Torres');
        $parent = $this->createParent('PARENT-0003', 'Bernard Torres');

        $parent->students()->attach($student1->id, ['relationship' => 'Father']);
        $parent->students()->attach($student2->id, ['relationship' => 'Father']);

        $env1 = $this->createSchoolYearEnvironment('2025-2026', 'completed', $yl['G7'], 'SectionAlpha');
        $env2 = $this->createSchoolYearEnvironment('2026-2027', 'active', $yl['G8'], 'SectionBeta');

        $enr1 = StudentEnrollment::create(['student_id' => $student1->id, 'school_year_section_id' => $env1['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);
        $enr2 = StudentEnrollment::create(['student_id' => $student2->id, 'school_year_section_id' => $env2['sys']->id, 'status' => 'enrolled', 'enrollment_date' => '2026-08-01']);

        foreach ($env1['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enr1->id, 'section_subject_id' => $env1['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 91.00, 'status' => 'approved']);
        }

        $component = Livewire::actingAs($parent->user)
            ->test(ParentAcademicHistory::class);

        $component->assertStatus(200);
        $component->assertSee('Child One Torres');
        $component->assertSee('Child Two Torres');

        // Select child 1
        $component->call('selectChild', $student1->id)
            ->assertSet('selectedStudentId', $student1->id)
            ->assertSee('Child One Torres')
            ->assertSee('SectionAlpha');

        // Select child 2
        $component->call('selectChild', $student2->id)
            ->assertSet('selectedStudentId', $student2->id)
            ->assertSee('Child Two Torres')
            ->assertSee('SectionBeta');
    }

    /**
     * Test parent CANNOT access academic records of an unlinked student (authorization check).
     */
    public function test_parent_cannot_access_unlinked_student_history(): void
    {
        $this->setupYearLevels();
        $studentLinked = $this->createStudent('2026-0006', 'My Child', 'Smith');
        $studentUnlinked = $this->createStudent('2026-0007', 'Stranger Child', 'Johnson');
        $parent = $this->createParent('PARENT-0004', 'Parent Smith');

        $parent->students()->attach($studentLinked->id, ['relationship' => 'Parent']);

        // Attempting to select unlinked student triggers 403 Forbidden
        Livewire::actingAs($parent->user)
            ->test(ParentAcademicHistory::class)
            ->call('selectChild', $studentUnlinked->id)
            ->assertForbidden();
    }

    /**
     * Test that Report Cards module remains strictly displaying active school year grades.
     * Must not be merged with or affected by Academic History.
     */
    public function test_report_cards_module_remains_active_school_year_only(): void
    {
        $yl = $this->setupYearLevels();
        $student = $this->createStudent('2026-0008', 'Lucas', 'Bautista');

        // Past school year 2025-2026
        $envPast = $this->createSchoolYearEnvironment('2025-2026', 'completed', $yl['G7'], 'PastSection');
        // Active school year 2026-2027
        $envActive = $this->createSchoolYearEnvironment('2026-2027', 'active', $yl['G8'], 'ActiveSection');

        $enrPast = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envPast['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);
        $enrActive = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envActive['sys']->id, 'status' => 'enrolled', 'enrollment_date' => '2026-08-01']);

        // Past grades: 80.00
        foreach ($envPast['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrPast->id, 'section_subject_id' => $envPast['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 80.00, 'status' => 'approved']);
        }
        // Active grades: 92.00
        foreach ($envActive['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrActive->id, 'section_subject_id' => $envActive['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 92.00, 'status' => 'approved']);
        }

        // Test Student Report Card (student.grades): displays ONLY active school year 2026-2027
        Livewire::actingAs($student->user)
            ->test(StudentGrades::class)
            ->assertStatus(200)
            ->assertSee('ActiveSection')
            ->assertSee('92.0')
            ->assertDontSee('PastSection')
            ->assertDontSee('80.0');

        // Meanwhile, Academic History displays both years
        Livewire::actingAs($student->user)
            ->test(StudentAcademicHistory::class)
            ->assertStatus(200)
            ->assertSee('PastSection')
            ->assertSee('ActiveSection');
    }

    /**
     * Test historical grade records remain accessible when a new school year is activated.
     */
    public function test_historical_grades_remain_accessible_after_new_school_year_activated(): void
    {
        $yl = $this->setupYearLevels();
        $student = $this->createStudent('2026-0009', 'Sophia', 'Velasquez');

        $envInitial = $this->createSchoolYearEnvironment('2025-2026', 'active', $yl['G7'], 'InitialSection');
        $enrInitial = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envInitial['sys']->id, 'status' => 'completed', 'enrollment_date' => '2025-08-01']);

        foreach ($envInitial['quarters'] as $q) {
            Grade::create(['student_enrollment_id' => $enrInitial->id, 'section_subject_id' => $envInitial['subjects'][0]->id, 'quarter_id' => $q->id, 'grade' => 87.00, 'status' => 'approved']);
        }

        // Now advance / activate new school year 2026-2027
        $envInitial['sy']->update(['status' => 'completed']);
        $envNew = $this->createSchoolYearEnvironment('2026-2027', 'active', $yl['G8'], 'NewSection');
        $enrNew = StudentEnrollment::create(['student_id' => $student->id, 'school_year_section_id' => $envNew['sys']->id, 'status' => 'enrolled', 'enrollment_date' => '2026-08-01']);

        // Historical grade records for 2025-2026 remain fully intact and accessible in Academic History
        Livewire::actingAs($student->user)
            ->test(StudentAcademicHistory::class)
            ->call('selectGradeLevel', 'G7')
            ->assertSee('2025-2026')
            ->assertSee('InitialSection')
            ->assertSee('87.0');
    }
}
