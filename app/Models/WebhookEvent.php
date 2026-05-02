<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $table = 'webhooks';

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_id',
        'event_type',
        'event_id',
        'payment_gateway',
        'processed',
        'resource_id',
        'payload',
    ];


    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
    ];


}
