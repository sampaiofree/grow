<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doppus_produtor', function (Blueprint $table) {
            // Campos de Customer
            $table->id();
            $table->string('customer_name', 50);
            $table->string('customer_email', 100);
            $table->string('customer_phone', 15)->nullable();
            $table->string('customer_doc_type', 4)->nullable();
            $table->string('customer_doc', 30)->nullable();
            $table->string('customer_ip_address', 40)->nullable();
            
            // Campos de Address
            $table->string('address_zipcode', 9)->nullable();
            $table->string('address_address', 100)->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement', 50)->nullable();
            $table->string('address_neighborhood', 50)->nullable();
            $table->string('address_city', 50)->nullable();
            $table->string('address_state', 2)->nullable();
            
            // Campos de Items
            $table->string('items_code', 8)->nullable();
            $table->string('items_name', 50)->nullable();
            $table->string('items_offer', 8)->nullable();
            $table->string('items_offer_name', 30)->nullable();
            $table->string('items_type', 20)->nullable();
            $table->integer('items_value')->nullable();

            // Campos de Affiliate
            $table->string('affiliate_code', 8)->nullable();
            $table->string('affiliate_name', 50)->nullable();
            $table->string('affiliate_email', 100)->nullable();

            // Campos de Recurrence
            $table->string('recurrence_code', 10)->nullable();
            $table->string('recurrence_periodicy', 20)->nullable();

            // Campos de Transaction
            $table->string('transaction_code', 10)->nullable();
            $table->dateTime('transaction_registration_date')->nullable();
            $table->integer('transaction_items')->nullable();
            $table->integer('transaction_discount')->nullable();
            $table->integer('transaction_shipping')->nullable();
            $table->integer('transaction_subtotal')->nullable();
            $table->integer('transaction_interest')->nullable();
            $table->integer('transaction_interest_add')->nullable();
            $table->integer('transaction_total')->nullable();
            $table->integer('transaction_fee_transaction')->nullable();
            $table->integer('transaction_fee_doppus')->nullable();
            $table->integer('transaction_fee_affiliate')->nullable();
            $table->integer('transaction_fee_manager')->nullable();
            $table->integer('transaction_fee_coproducers')->nullable();
            $table->integer('transaction_fee_producer')->nullable();

            // Campos de Payment
            $table->string('payment_method', 20)->nullable();
            $table->integer('payment_plots')->nullable();
            $table->string('payment_creditcard', 16)->nullable();
            $table->string('payment_brand', 20)->nullable();
            $table->string('payment_owner', 50)->nullable();
            $table->date('payment_due_date')->nullable();
            $table->string('payment_digitable_line', 55)->nullable();
            $table->string('payment_brcode', 250)->nullable();

            // Campos de Shipping
            $table->string('shipping_method', 20)->nullable();
            $table->integer('shipping_deadline')->nullable();

            // Campos de Links
            $table->string('links_billet', 250)->nullable();
            $table->string('links_qrcode', 250)->nullable();
            $table->string('links_reprocess', 250)->nullable();
            $table->string('links_checkout', 250)->nullable();

            // Campos de Tracking
            $table->string('tracking_utm_source', 100)->nullable();
            $table->string('tracking_utm_medium', 100)->nullable();
            $table->string('tracking_utm_campaign', 100)->nullable();
            $table->string('tracking_utm_term', 100)->nullable();
            $table->string('tracking_utm_content', 100)->nullable();
            $table->string('tracking_src', 100)->nullable();

            // Campos de Status
            $table->dateTime('status_registration_date')->nullable();
            $table->string('status_code', 20)->nullable();
            $table->string('status_message', 150)->nullable();

            // Campos de Status Log
            $table->dateTime('status_log_registration_date')->nullable();
            $table->string('status_log_code', 20)->nullable();
            $table->string('status_log_message', 150)->nullable();

            $table->timestamps(); // Campos de controle (created_at e updated_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doppus_produtor');
    }
};
