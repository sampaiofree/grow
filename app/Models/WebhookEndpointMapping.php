<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpointMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_endpoint_id',
        'target_key',
        'source_paths',
        'delimiter',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'source_paths' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    public function endpoint()
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
