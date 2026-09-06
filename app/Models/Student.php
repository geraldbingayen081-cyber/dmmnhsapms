<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthdate',
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

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'student_id');
    }

    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'parent_student', 'student_id', 'parent_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class, 'student_id');
    }
}
