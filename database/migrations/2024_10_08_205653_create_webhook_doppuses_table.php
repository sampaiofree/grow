<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookDoppusesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('webhook_doppus', function (Blueprint $table) {
            $table->id(); // ID principal da tabela
            $table->unsignedBigInteger('user_id')->nullable(); // Relacionamento com a tabela de usuários
            $table->string('token', 500); // Campo para armazenar um token grande

            // Campos do cliente
            $table->json('customer')->nullable(); // Conjunto de dados do cliente (usaremos JSON para facilitar)
            $table->json('address')->nullable();  // Conjunto de dados do endereço (JSON)
            $table->json('items')->nullable();    // Conjunto de dados dos itens da venda (JSON)
            $table->json('affiliate')->nullable();  // Dados do afiliado (JSON)
            $table->json('recurrence')->nullable(); // Dados de recorrência (JSON)
            $table->json('transaction')->nullable(); // Dados da transação (JSON)
            $table->json('payment')->nullable(); // Dados de pagamento (JSON)
            $table->json('shipping')->nullable(); // Dados de frete (JSON)
            $table->json('links')->nullable(); // Links para boleto e PIX (JSON)
            $table->json('tracking')->nullable(); // Dados de rastreamento (JSON)
            $table->json('status')->nullable(); // Dados de status (JSON)

            // Timestamps automáticos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('webhook_doppus');
    }
}
