<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'institution_id',
        'promotion_id',
        'classroom_id',
        'product_id',
        'created_by',
        'title',
        'slug',
        'description',
        'unit_price',
        'deposit_percent',
        'target_units',
        'current_units',
        'starts_at',
        'ends_at',
        'delivery_mode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'deposit_percent' => 'integer',
            'target_units' => 'integer',
            'current_units' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CampaignParticipant::class)->latest();
    }

    public function progressPercent(): int
    {
        if ($this->target_units <= 0) {
            return 100;
        }

        return min(100, (int) round(($this->current_units / $this->target_units) * 100));
    }

    public function unitsRemaining(): int
    {
        return max(0, $this->target_units - $this->current_units);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Activa — En preventa',
            'goal_reached' => 'Meta alcanzada',
            'in_production' => 'En producción consolidada',
            'closed' => 'Cerrada y despachada',
            'cancelled' => 'Cancelada',
            default => ucfirst($this->status),
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
