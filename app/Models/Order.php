<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // This function for sending order items mails
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentRelation(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refundsRelation()
    {
        return $this->hasMany(Refund::class);
    }

    protected $casts = [
        'stripe_payload' => 'array',
    ];


}
