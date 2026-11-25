<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'type',
        'modular_code',
        'city',
        'region',
        'address',
        'website_url',
        'founded_year',
        'motto',
        'history',
        'anthem_excerpt',
        'primary_color',
        'secondary_color',
        'logo_path',
        'banner_path',
        'commission_rate',
        'is_featured',
        'is_published',
        'approved_by',
        'approved_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
            'commission_rate' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'approved_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_published', true);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class)->orderByDesc('year');
    }

    public function alumniProfiles(): HasMany
    {
        return $this->hasMany(AlumniProfile::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function delegates(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', [
            UserRole::InstitutionAdmin->value,
            UserRole::PromotionDelegate->value,
            UserRole::ClassroomDelegate->value,
        ]);
    }

    public function activeDelegates(): HasMany
    {
        return $this->delegates()->where('is_active', true);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBePublished(): bool
    {
        return $this->activeDelegates()->exists();
    }

    public function yearsOfHistory(): ?int
    {
        if (! $this->founded_year) {
            return null;
        }

        return max(0, (int) now()->year - (int) $this->founded_year);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
