<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $hidden = [
        'purchase_price',
    ];

    protected $fillable = [
        'institution_id',
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'sku_prefix',
        'short_description',
        'description',
        'purchase_price',
        'base_price',
        'member_price',
        'is_customizable',
        'is_preorder_only',
        'show_verified_vendor',
        'image_path',
        'gallery',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'base_price' => 'decimal:2',
            'member_price' => 'decimal:2',
            'is_customizable' => 'boolean',
            'is_preorder_only' => 'boolean',
            'show_verified_vendor' => 'boolean',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true)->latest();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function effectivePrice(?User $user = null): float
    {
        if ($user && $this->member_price && $user->isRole('alumni', 'promotion_delegate', 'classroom_delegate', 'institution_admin', 'super_admin')) {
            return (float) $this->member_price;
        }

        return (float) $this->base_price;
    }

    public function profitAmount(): float
    {
        return round((float) $this->base_price - (float) $this->purchase_price, 2);
    }

    public function marginPercent(): float
    {
        $sale = (float) $this->base_price;
        if ($sale <= 0) {
            return 0.0;
        }

        return round((($sale - (float) $this->purchase_price) / $sale) * 100, 1);
    }

    public function hasVerifiedVendorBadge(): bool
    {
        return (bool) ($this->show_verified_vendor && ($this->vendor?->is_verified ?? false));
    }

    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock');
    }

    public function averageRating(): float
    {
        return round((float) ($this->reviews()->avg('rating') ?? 5.0), 1);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
