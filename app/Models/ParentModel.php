<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
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

    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }
}
