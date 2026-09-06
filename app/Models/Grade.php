<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_enrollment_id',
        'section_subject_id',
        'quarter_id',
        'grade',
        'remarks',
        'status',
        'created_by',
    ];

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function sectionSubject()
    {
        return $this->belongsTo(SectionSubject::class, 'section_subject_id');
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
