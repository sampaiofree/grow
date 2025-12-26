<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultSlug = 'manual-webhook';

        $servicoId = DB::table('servicos')
            ->where('slug', $defaultSlug)
            ->value('id');

        if (! $servicoId) {
            $servicoId = DB::table('servicos')->insertGetId([
                'nome' => 'Manual',
                'slug' => $defaultSlug,
                'descricao' => 'Serviço padrão criado pelo sistema para endpoints existentes',
                'handler_class' => 'ManualService',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('webhook_endpoints', function (Blueprint $table) use ($servicoId) {
            $table->foreignId('servico_id')
                ->after('user_id')
                ->default($servicoId)
                ->constrained('servicos')
                ->cascadeOnDelete();
        });

        DB::table('webhook_endpoints')
            ->whereNull('servico_id')
            ->update(['servico_id' => $servicoId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servico_id');
        });

        DB::table('servicos')
            ->where('slug', 'manual-webhook')
            ->delete();
    }
};
