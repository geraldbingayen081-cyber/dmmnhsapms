<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\SchoolYearSection;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;

echo "=== TestDataSeeder Verification ===\n\n";

echo "Teachers:         " . Teacher::count() . " (expected: 32)\n";
echo "Students:         " . Student::count() . " (expected: 480)\n";
echo "SY Sections:      " . SchoolYearSection::count() . " (expected: 32)\n";
echo "Section Subjects: " . SectionSubject::count() . " (expected: 256 = 32×8)\n";
echo "Enrollments:      " . StudentEnrollment::count() . " (expected: 480)\n";
echo "Grade Records:    " . Grade::count() . " (expected: 11520 = 480×8×3)\n";
echo "User (teacher):   " . User::where('role','teacher')->count() . " (expected: 32)\n";
echo "User (student):   " . User::where('role','student')->count() . " (expected: 480)\n";
echo "User (parent):    " . User::where('role','parent')->count() . " (expected: 10)\n";

// Spot-check: Grade completeness for first student
$firstStudent = Student::with(['enrollments.grades'])->first();
if ($firstStudent) {
    $grades = $firstStudent->enrollments->first()?->grades ?? collect();
    echo "\nSpot-check student '{$firstStudent->full_name}' ({$firstStudent->student_number}):\n";
    echo "  Grade records: " . $grades->count() . " (expected: 24 = 8 subjects × 3 quarters)\n";
    $quarters = $grades->pluck('quarter_id')->unique()->sort()->values();
    echo "  Quarter IDs present: [" . $quarters->implode(', ') . "] (expected: 3 distinct)\n";
}

// Spot-check: first teacher's subject assignment
$firstTeacher = Teacher::with('user')->first();
$teacherSubjects = SectionSubject::where('teacher_id', $firstTeacher?->id)->with('subject')->get()->pluck('subject.subject_name')->unique()->values();
echo "\nSpot-check teacher '{$firstTeacher?->full_name}':\n";
echo "  Subjects assigned: [" . $teacherSubjects->implode(', ') . "] (expected: 1 subject only)\n";

echo "\n=== Verification Complete ===\n";
