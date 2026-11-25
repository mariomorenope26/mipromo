<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'value',
        'is_encrypted',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return $default;
            }
        } catch (Throwable) {
            return $default;
        }

        $cacheKey = self::cacheKey($key);
        $cached = Cache::get($cacheKey);

        // Guard against legacy serialized objects or missing entries when serializable_classes=false
        if (! is_array($cached) || ! array_key_exists('exists', $cached)) {
            $row = self::where('key', $key)->first();
            $cached = $row
                ? [
                    'exists' => true,
                    'value' => $row->value,
                    'is_encrypted' => (bool) $row->is_encrypted,
                ]
                : ['exists' => false];

            Cache::put($cacheKey, $cached, 300);
        }

        if (! ($cached['exists'] ?? false)) {
            return $default;
        }

        $rawValue = $cached['value'] ?? null;
        if ($rawValue === null || $rawValue === '') {
            return $default;
        }

        if (! ($cached['is_encrypted'] ?? false)) {
            return (string) $rawValue;
        }

        try {
            return Crypt::decryptString((string) $rawValue);
        } catch (Throwable) {
            return $default;
        }
    }

    public static function setValue(
        string $key,
        ?string $value,
        string $group = 'general',
        bool $encrypt = false,
        ?string $description = null
    ): self {
        $storedValue = $value;
        if ($encrypt && $value !== null && $value !== '') {
            $storedValue = Crypt::encryptString($value);
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $storedValue,
                'is_encrypted' => $encrypt,
                'description' => $description,
            ]
        );

        Cache::forget(self::cacheKey($key));
        Cache::forget("sys_setting_{$key}");

        return $setting;
    }

    private static function cacheKey(string $key): string
    {
        return "sys_cfg_v2_{$key}";
    }

    public static function hasNonEmptyValue(string $key): bool
    {
        $val = self::getValue($key);

        return $val !== null && trim($val) !== '';
    }

    public static function maskedToken(?string $token): string
    {
        $clean = trim((string) $token);
        if ($clean === '') {
            return '';
        }

        $len = strlen($clean);
        if ($len <= 8) {
            return str_repeat('•', $len);
        }

        return substr($clean, 0, 4).str_repeat('•', max(6, $len - 8)).substr($clean, -4);
    }
}
