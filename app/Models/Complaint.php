<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_code',
        'order_number',
        'customer_name',
        'customer_doc_number',
        'customer_email',
        'customer_phone',
        'type',
        'subject',
        'detail',
        'request_detail',
        'status',
        'response',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }
}
