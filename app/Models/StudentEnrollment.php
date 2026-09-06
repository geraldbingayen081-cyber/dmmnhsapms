<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_year_section_id',
        'status',
        'enrollment_date',
        'completion_date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolYearSection()
    {
        return $this->belongsTo(SchoolYearSection::class, 'school_year_section_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_enrollment_id');
    }
}
