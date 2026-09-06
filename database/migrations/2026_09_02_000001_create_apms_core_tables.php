<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Students Table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        // 2. Teachers Table
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        // 3. Parents Table
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        // 4. Parent - Student Relationship Pivot Table
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship')->default('Guardian');
            $table->timestamps();

            $table->unique(['parent_id', 'student_id'], 'parent_student_unique');
        });

        // 5. School Years Table
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('school_year')->unique(); // e.g. '2026-2027'
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->timestamps();
        });

        // 6. Quarters Table
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->restrictOnDelete();
            $table->string('name'); // e.g. '1st Quarter'
            $table->string('short_name'); // e.g. 'Q1'
            $table->integer('sort_order');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();

            $table->unique(['school_year_id', 'sort_order'], 'sy_quarter_sort_unique');
        });

        // 7. Year Levels Table (Grade 7 - Grade 10)
        Schema::create('year_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 'Grade 7'
            $table->string('code')->unique(); // e.g. 'G7'
            $table->integer('sort_order');
            $table->timestamps();
        });

        // 8. Sections Table (Reusable Section Names)
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. 'Rizal', 'Mabini'
            $table->timestamps();
        });

        // 9. School Year Sections Table (Actual Section Instances)
        Schema::create('school_year_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->restrictOnDelete();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->foreignId('year_level_id')->constrained('year_levels')->restrictOnDelete();
            $table->foreignId('adviser_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['school_year_id', 'section_id'], 'sy_section_unique');
        });

        // 10. Student Enrollments Table (Source of Truth for Academic Placement)
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('school_year_section_id')->constrained('school_year_sections')->restrictOnDelete();
            $table->enum('status', ['enrolled', 'completed', 'promoted', 'retained', 'transferred', 'dropped'])->default('enrolled');
            $table->date('enrollment_date');
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });

        // 11. Subjects Table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code')->unique(); // e.g. 'FIL', 'ENG'
            $table->string('subject_name'); // e.g. 'Filipino', 'English'
            $table->timestamps();
        });

        // 12. Section Subjects Table (Specific Subject + Section + Teacher Assignment)
        Schema::create('section_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_section_id')->constrained('school_year_sections')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['school_year_section_id', 'subject_id'], 'sy_section_subject_unique');
        });

        // 13. Grades Table
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_enrollment_id')->constrained('student_enrollments')->restrictOnDelete();
            $table->foreignId('section_subject_id')->constrained('section_subjects')->restrictOnDelete();
            $table->foreignId('quarter_id')->constrained('quarters')->restrictOnDelete();
            $table->decimal('grade', 5, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->enum('status', ['draft', 'approved', 'locked'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['student_enrollment_id', 'section_subject_id', 'quarter_id'], 'grades_enrollment_subject_quarter_unique');
        });

        // 14. Achievement Rules Table
        Schema::create('achievement_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->restrictOnDelete();
            $table->string('name');
            $table->string('type'); // e.g. 'With Honors', 'Best in Subject'
            $table->decimal('minimum_average', 5, 2)->nullable();
            $table->decimal('maximum_average', 5, 2)->nullable();
            $table->decimal('minimum_subject_grade', 5, 2)->nullable();
            $table->json('criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 15. Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('achievement_rules');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('section_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('school_year_sections');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('year_levels');
        Schema::dropIfExists('quarters');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
    }
};
