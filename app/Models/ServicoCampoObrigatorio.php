<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicoCampoObrigatorio extends Model
{
    use HasFactory;

    protected $table = 'servicos_campos_obrigatorios';

    protected $fillable = [
        'servico_id',
        'nome_exibicao',
        'campo_padrao',
        'tipo',
        'obrigatorio',
    ];

    protected $casts = [
        'obrigatorio' => 'boolean',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
