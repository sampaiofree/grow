<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicos_campos_obrigatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')
                ->constrained('servicos')
                ->cascadeOnDelete();
            $table->string('nome_exibicao', 120);
            $table->string('campo_padrao', 120);
            $table->string('tipo', 60);
            $table->boolean('obrigatorio')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos_campos_obrigatorios');
    }
};
