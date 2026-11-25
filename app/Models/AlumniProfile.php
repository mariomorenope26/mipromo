<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniProfile extends Model
{
    protected $fillable = [
        'user_id',
        'institution_id',
        'promotion_id',
        'classroom_id',
        'full_name',
        'dni',
        'entry_year',
        'graduation_year',
        'current_city',
        'phone',
        'email',
        'profession',
        'company',
        'bio',
        'verification_status',
        'verified_by',
        'verified_at',
        'privacy_settings',
        'import_batch',
    ];

    protected function casts(): array
    {
        return [
            'entry_year' => 'integer',
            'graduation_year' => 'integer',
            'verified_at' => 'datetime',
            'privacy_settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function allowsField(string $field): bool
    {
        $settings = $this->privacy_settings ?? [];

        return (bool) ($settings["show_{$field}"] ?? true);
    }
}
