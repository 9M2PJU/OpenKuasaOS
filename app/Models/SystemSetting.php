<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a setting by key with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set or update a setting value.
     */
    public static function set(string $key, mixed $value, ?string $description = null): static
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        if ($description !== null) {
            $setting->description = $description;
        }
        $setting->save();

        return $setting;
    }
}
