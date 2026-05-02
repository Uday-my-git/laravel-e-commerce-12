<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'stripe_refund_id',
        'stripe_payment_intent',
        'price',
        'currency',
        'status',
        'reason',
        'stripe_payload',
    ];

    protected $casts = [
        'stripe_payload' => 'array',
    ];

    public function paymentRelation()
    {
        return $this->belongsTo(Payment::class);
    }

    public function orderRelation()
    {
        return $this->belongsTo(Order::class);
    }

    
}
