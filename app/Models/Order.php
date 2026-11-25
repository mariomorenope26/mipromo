<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'institution_id',
        'campaign_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_dni_ruc',
        'shipping_zone_id',
        'shipping_address',
        'city',
        'department',
        'tracking_code',
        'courier_name',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total',
        'institution_commission_amount',
        'coupon_code',
        'payment_status',
        'order_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'institution_commission_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->where('document_type', '!=', 'nota_credito');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class)->latest();
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pendiente de pago',
            'partial' => 'Adelanto registrado',
            'verified' => 'Pago conciliado',
            'rejected' => 'Pago rechazado',
            default => ucfirst($this->payment_status),
        };
    }

    public function orderStatusLabel(): string
    {
        return match ($this->order_status) {
            'pending' => 'Recibido',
            'confirmed' => 'Confirmado',
            'in_production' => 'En producción',
            'shipped' => 'En tránsito / Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->order_status),
        };
    }
}
