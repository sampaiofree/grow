<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDoppus extends Model
{
    use HasFactory;

    protected $table = 'webhook_doppus'; // Nome da tabela

    protected $fillable = [
        'user_id',
        'transaction_code',
        'token',
        'customer',
        'address',
        'items',
        'affiliate',
        'recurrence',
        'transaction',
        'payment',
        'shipping',
        'links',
        'tracking',
        'status',
    ];

    // Defina que os campos customer, address, etc., são arrays JSON
    protected $casts = [
        'customer' => 'array',
        'address' => 'array',
        'items' => 'array',
        'affiliate' => 'array',
        'recurrence' => 'array',
        'transaction' => 'array',
        'payment' => 'array',
        'shipping' => 'array',
        'links' => 'array',
        'tracking' => 'array',
        'status' => 'array',
    ];
}
