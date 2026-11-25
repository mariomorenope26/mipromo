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

        $row = Cache::remember("sys_setting_{$key}", 300, fn () => self::where('key', $key)->first());

        if (! $row || $row->value === null || $row->value === '') {
            return $default;
        }

        if (! $row->is_encrypted) {
            return $row->value;
        }

        try {
            return Crypt::decryptString($row->value);
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

        Cache::forget("sys_setting_{$key}");

        return $setting;
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
