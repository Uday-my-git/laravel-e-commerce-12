<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_gateway',   
        'payment_intent_id', 
        'transaction_id',
        'reference_id',
        'amount',
        'currency',
        'status',
        'payload'
    ];

    protected $casts = [
        'stripe_payload' => 'array',
    ];

    /**
     * Get the user that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    // public function orderRelation(): BelongsTo

    public function orderRelation()
    {
        return $this->belongsTo(Order::class);
    }

    public function userRelation()
    {
        return $this->belongsTo(User::class);
    }

    public function refundsRelation()
    {
        return $this->hasMany(Refund::class);
    }


}
