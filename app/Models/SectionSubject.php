<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_section_id',
        'subject_id',
        'teacher_id',
    ];

    public function schoolYearSection()
    {
        return $this->belongsTo(SchoolYearSection::class, 'school_year_section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'section_subject_id');
    }
}
