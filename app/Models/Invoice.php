<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'document_type',
        'series',
        'correlative',
        'customer_doc_type',
        'customer_doc_number',
        'customer_legal_name',
        'subtotal_gravado',
        'igv',
        'total',
        'sunat_status',
        'sunat_hash',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'correlative' => 'integer',
            'subtotal_gravado' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function formattedNumber(): string
    {
        return sprintf('%s-%07d', $this->series, $this->correlative);
    }
}
