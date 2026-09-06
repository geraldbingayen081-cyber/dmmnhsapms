<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];

    public function schoolYearSections()
    {
        return $this->hasMany(SchoolYearSection::class, 'year_level_id');
    }
}
