<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'name',
        'type',
        'minimum_average',
        'maximum_average',
        'minimum_subject_grade',
        'criteria',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }
}
