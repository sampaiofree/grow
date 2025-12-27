<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'is_active',
        'last_test_payload',
        'last_response_status',
        'last_response_body',
        'last_response_at',
        'throttle_limit',
        'disabled_at',
        'servico_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_test_payload' => 'array',
            'disabled_at' => 'datetime',
            'last_response_status' => 'integer',
            'last_response_at' => 'datetime',
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

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
