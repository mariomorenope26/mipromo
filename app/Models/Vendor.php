<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $hidden = [
        'bank_account',
        'bank_cci',
        'internal_notes',
    ];

    protected $fillable = [
        'user_id',
        'business_name',
        'trade_name',
        'ruc',
        'slug',
        'city',
        'address',
        'phone',
        'contact_name',
        'email',
        'commission_rate',
        'payment_terms',
        'bank_name',
        'bank_account',
        'bank_cci',
        'lead_time_days',
        'rating',
        'description',
        'category_specialty',
        'is_active',
        'is_verified',
        'verified_at',
        'verified_by',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'rating' => 'decimal:2',
            'lead_time_days' => 'integer',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(VendorSettlement::class)->latest();
    }

    public function scopeVerified($query)
    {
        return $query->where('is_active', true)->where('is_verified', true);
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->business_name;
    }
}
