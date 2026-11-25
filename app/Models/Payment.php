<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'is_partial_deposit',
        'reference_code',
        'voucher_path',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_partial_deposit' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'bank_transfer' => 'Transferencia Bancaria',
            'card_culqi' => 'Tarjeta Crédito/Débito (Pasarela)',
            'cash_on_delivery' => 'Pago contra entrega',
            default => ucfirst($this->method),
        };
    }
}
