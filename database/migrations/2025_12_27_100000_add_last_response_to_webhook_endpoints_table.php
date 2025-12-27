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
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->unsignedInteger('last_response_status')->nullable()->after('last_test_payload');
            $table->longText('last_response_body')->nullable()->after('last_response_status');
            $table->timestamp('last_response_at')->nullable()->after('last_response_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->dropColumn(['last_response_status', 'last_response_body', 'last_response_at']);
        });
    }
};
