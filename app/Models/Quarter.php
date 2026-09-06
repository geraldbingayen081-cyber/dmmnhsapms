<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'name',
        'short_name',
        'sort_order',
        'start_date',
        'end_date',
        'status',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'quarter_id');
    }
}
