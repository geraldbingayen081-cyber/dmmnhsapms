<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set/update a setting value by key.
     */
    public static function set(string $key, $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Remove a setting by key.
     */
    public static function forget(string $key): bool
    {
        return (bool) static::where('key', $key)->delete();
    }

    /**
     * Return the publicly accessible URL of the school logo if set.
     */
    public static function logoUrl(): ?string
    {
        $path = static::get('school_logo');
        if (!empty($path)) {
            // Check if file exists on the public disk
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
            // If path is an absolute URL or starts with storage/
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }
        return null;
    }
}
