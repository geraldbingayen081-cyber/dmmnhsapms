<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function schoolYearSections()
    {
        return $this->hasMany(SchoolYearSection::class, 'section_id');
    }

    public function studentEnrollments()
    {
        return $this->hasManyThrough(
            StudentEnrollment::class,
            SchoolYearSection::class,
            'section_id',            // FK on SchoolYearSection
            'school_year_section_id', // FK on StudentEnrollment
            'id',                    // Local key on Section
            'id'                     // Local key on SchoolYearSection
        );
    }
}
