<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPoll extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'question',
        'closes_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'closes_at' => 'datetime',
            'is_active' => 'boolean',
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

    public function options(): HasMany
    {
        return $this->hasMany(CommunityPollOption::class, 'poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommunityPollVote::class, 'poll_id');
    }
}
