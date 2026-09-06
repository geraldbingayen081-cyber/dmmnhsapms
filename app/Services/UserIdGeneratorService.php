<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserIdGeneratorService
{
    /**
     * Generate unique Admin ID (e.g., Admin-0001)
     */
    public static function generateAdminId(): string
    {
        return static::generateId('Admin-', 4);
    }

    /**
     * Generate unique Teacher ID (e.g., EMP-0001)
     */
    public static function generateTeacherId(): string
    {
        return static::generateId('EMP-', 4);
    }

    /**
     * Generate unique Parent ID (e.g., PARENT-0001)
     */
    public static function generateParentId(): string
    {
        return static::generateId('PARENT-', 4);
    }

    /**
     * Generate unique Student ID (e.g., 2026-0001)
     */
    public static function generateStudentId(string $year = null): string
    {
        $prefix = ($year ?: date('Y')) . '-';
        return static::generateId($prefix, 4);
    }

    /**
     * Collision-safe ID generator logic
     */
    protected static function generateId(string $prefix, int $padLength = 4): string
    {
        return DB::transaction(function () use ($prefix, $padLength) {
            $lastUser = User::where('login_id', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if (! $lastUser) {
                return $prefix . str_pad('1', $padLength, '0', STR_PAD_LEFT);
            }

            $numberPart = (int) str_replace($prefix, '', $lastUser->login_id);
            $nextNumber = $numberPart + 1;

            return $prefix . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
        });
    }
}
