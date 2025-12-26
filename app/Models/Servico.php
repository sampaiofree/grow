<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'handler_class',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function camposObrigatorios(): HasMany
    {
        return $this->hasMany(ServicoCampoObrigatorio::class);
    }

    public function webhookEndpoints(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }
}
