<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'contact_number',
    ];

    public function getFullNameAttribute(): string
    {
        $name = "{$this->first_name} {$this->middle_name} {$this->last_name}";
        if ($this->suffix) {
            $name .= " {$this->suffix}";
        }
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advisedSections()
    {
        return $this->hasMany(SchoolYearSection::class, 'adviser_id');
    }

    public function adviserSections()
    {
        return $this->hasMany(SchoolYearSection::class, 'adviser_id');
    }

    public function sectionSubjects()
    {
        return $this->hasMany(SectionSubject::class, 'teacher_id');
    }
}
