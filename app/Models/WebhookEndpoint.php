<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'is_active',
        'last_test_payload',
        'throttle_limit',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_test_payload' => 'array',
            'disabled_at' => 'datetime',
        ];
    }

    public function mappings()
    {
        return $this->hasMany(WebhookEndpointMapping::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
