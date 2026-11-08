<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year',
        'start_date',
        'end_date',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (SchoolYear $schoolYear) {
            \App\Services\SchoolYearRotationService::generateStructureForSchoolYear($schoolYear);
        });
    }

    public static function active()
    {
        return static::where('status', 'active')->first();
    }

    public static function latestSchoolYear(): ?self
    {
        return \App\Services\SchoolYearService::getLatestSchoolYear();
    }

    public static function nextAvailableSchoolYear(): string
    {
        return \App\Services\SchoolYearService::getNextAvailableSchoolYear();
    }

    public function quarters()
    {
        return $this->hasMany(Quarter::class, 'school_year_id')->orderBy('sort_order');
    }

    public function terms()
    {
        return $this->quarters();
    }

    public function sections()
    {
        return $this->hasMany(SchoolYearSection::class, 'school_year_id');
    }

    public function schoolYearSections()
    {
        return $this->hasMany(SchoolYearSection::class, 'school_year_id');
    }

    public function achievementRules()
    {
        return $this->hasMany(AchievementRule::class, 'school_year_id');
    }
}
