<?php

namespace App\Services;

use App\Models\Quarter;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Session;

class AcademicContextService
{
    /**
     * Get the active School Year (status = 'active')
     */
    public static function getActiveSchoolYear(): ?SchoolYear
    {
        return SchoolYear::where('status', 'active')->first()
            ?? SchoolYear::orderBy('school_year', 'desc')->first();
    }

    /**
     * Get current active Quarter for the active school year
     */
    public static function getCurrentQuarter(?int $schoolYearId = null): ?Quarter
    {
        $syId = $schoolYearId ?: static::getActiveSchoolYear()?->id;
        
        if (! $syId) {
            return null;
        }

        return Quarter::where('school_year_id', $syId)
            ->where('status', 'active')
            ->first() 
            ?? Quarter::where('school_year_id', $syId)->orderBy('sort_order', 'asc')->first();
    }

    /**
     * Get currently selected school year ID from session or default active
     */
    public static function getSelectedSchoolYearId(): ?int
    {
        $sessionSyId = Session::get('selected_school_year_id');
        
        if ($sessionSyId && SchoolYear::where('id', $sessionSyId)->exists()) {
            return (int) $sessionSyId;
        }

        return static::getActiveSchoolYear()?->id;
    }

    /**
     * Set selected school year ID in session
     */
    public static function setSelectedSchoolYearId(int $schoolYearId): void
    {
        Session::put('selected_school_year_id', $schoolYearId);
    }
}
