<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSettlement extends Model
{
    protected $fillable = [
        'vendor_id',
        'period_label',
        'gross_sales',
        'platform_commission',
        'net_payout',
        'status',
        'paid_at',
        'reference_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_sales' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'net_payout' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
