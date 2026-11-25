<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignParticipant extends Model
{
    protected $fillable = [
        'campaign_id',
        'user_id',
        'variant_id',
        'order_id',
        'participant_name',
        'participant_phone',
        'quantity',
        'custom_name',
        'custom_year',
        'amount_paid',
        'total_amount',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'amount_paid' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
