<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYearSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'section_id',
        'year_level_id',
        'adviser_id',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (SchoolYearSection $sys) {
            $sys->loadMissing('section');
            $sectionName = $sys->section ? $sys->section->name : null;

            $subjects = Subject::all();
            foreach ($subjects as $sub) {
                if ($sub->subject_code === 'JOURN' && strtoupper(trim($sectionName ?? '')) !== 'SPJ') {
                    continue;
                }
                SectionSubject::firstOrCreate([
                    'school_year_section_id' => $sys->id,
                    'subject_id' => $sub->id,
                ]);
            }
        });
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class, 'year_level_id');
    }

    public function adviser()
    {
        return $this->belongsTo(Teacher::class, 'adviser_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'school_year_section_id');
    }

    public function sectionSubjects()
    {
        return $this->hasMany(SectionSubject::class, 'school_year_section_id');
    }
}
