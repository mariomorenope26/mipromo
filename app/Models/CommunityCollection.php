<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityCollection extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'title',
        'description',
        'goal_amount',
        'current_amount',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'goal_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'ends_at' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(CommunityCollectionContribution::class, 'collection_id')->latest();
    }

    public function progressPercent(): int
    {
        if ((float) $this->goal_amount <= 0) {
            return 100;
        }

        return min(100, (int) round(((float) $this->current_amount / (float) $this->goal_amount) * 100));
    }
}
