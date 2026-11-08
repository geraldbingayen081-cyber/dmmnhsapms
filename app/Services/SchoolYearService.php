<?php

namespace App\Services;

use App\Models\SchoolYear;

class SchoolYearService
{
    /**
     * Determine if any school year exists in the system.
     */
    public static function hasSchoolYears(): bool
    {
        return SchoolYear::exists();
    }

    /**
     * Get the latest school year by highest chronological end calendar year.
     */
    public static function getLatestSchoolYear(): ?SchoolYear
    {
        return SchoolYear::all()
            ->filter(function (SchoolYear $sy) {
                return (bool) preg_match('/^(\d{4})-(\d{4})$/', $sy->school_year);
            })
            ->sortByDesc(function (SchoolYear $sy) {
                preg_match('/^(\d{4})-(\d{4})$/', $sy->school_year, $matches);
                return (int) $matches[2];
            })
            ->first() ?? SchoolYear::orderBy('school_year', 'desc')->first();
    }

    /**
     * Calculate the next required chronological school year.
     * If no school years exist, suggest the current calendar school year (e.g. 2026-2027).
     */
    public static function getNextAvailableSchoolYear(): string
    {
        $latest = self::getLatestSchoolYear();

        if (!$latest) {
            $currentYear = (int) date('Y');
            return "{$currentYear}-" . ($currentYear + 1);
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', $latest->school_year, $matches)) {
            $nextStart = (int) $matches[2];
            $nextEnd = $nextStart + 1;
            return "{$nextStart}-{$nextEnd}";
        }

        $currentYear = (int) date('Y');
        return "{$currentYear}-" . ($currentYear + 1);
    }

    /**
     * Suggest default academic start and end dates (August 1 to May 31).
     */
    public static function getSuggestedDates(?string $schoolYear = null): array
    {
        $syString = $schoolYear ?: self::getNextAvailableSchoolYear();

        if (preg_match('/^(\d{4})-(\d{4})$/', $syString, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];

            return [
                'start_date' => "{$startYear}-08-01",
                'end_date' => "{$endYear}-05-31",
            ];
        }

        $currentYear = (int) date('Y');
        return [
            'start_date' => "{$currentYear}-08-01",
            'end_date' => ($currentYear + 1) . "-05-31",
        ];
    }

    /**
     * Validate sequential school year creation.
     * Returns null if valid, or a descriptive error message if invalid.
     */
    public static function validateSequentialCreation(string $schoolYear): ?string
    {
        $schoolYear = trim($schoolYear);

        // 1. Format Validation: YYYY-YYYY
        if (!preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)) {
            return 'School year format must be YYYY-YYYY (e.g., 2026-2027).';
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        // 2. Consecutive Calendar Years Validation
        if ($endYear !== $startYear + 1) {
            return 'A school year must start and end in consecutive calendar years (e.g., ' . $startYear . '-' . ($startYear + 1) . ').';
        }

        // 3. Initial School Year Handling (Empty Database)
        if (!self::hasSchoolYears()) {
            return null;
        }

        // 4. Sequential Chronological Validation
        $expectedNext = self::getNextAvailableSchoolYear();
        preg_match('/^(\d{4})-(\d{4})$/', $expectedNext, $expectedMatches);
        $expectedStart = (int) $expectedMatches[1];

        if ($schoolYear === $expectedNext) {
            return null;
        }

        if ($startYear < $expectedStart) {
            return "You cannot create a previous school year. The next available school year is {$expectedNext}.";
        }

        return "School years must follow chronological sequence. The next available school year is {$expectedNext}.";
    }

    /**
     * Validate format and consecutive calendar years for editing.
     */
    public static function validateConsecutiveYears(string $schoolYear): ?string
    {
        $schoolYear = trim($schoolYear);

        if (!preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)) {
            return 'School year format must be YYYY-YYYY (e.g., 2026-2027).';
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            return 'A school year must start and end in consecutive calendar years (e.g., ' . $startYear . '-' . ($startYear + 1) . ').';
        }

        return null;
    }
}
