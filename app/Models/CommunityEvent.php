<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityEvent extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'title',
        'description',
        'location',
        'starts_at',
        'ticket_price',
        'max_attendees',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ticket_price' => 'decimal:2',
            'max_attendees' => 'integer',
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

    public function rsvps(): HasMany
    {
        return $this->hasMany(CommunityEventRsvp::class, 'event_id');
    }
}
