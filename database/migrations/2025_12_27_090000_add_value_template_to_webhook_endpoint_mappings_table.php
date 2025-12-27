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
        Schema::table('webhook_endpoint_mappings', function (Blueprint $table) {
            $table->text('value_template')->nullable()->after('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoint_mappings', function (Blueprint $table) {
            $table->dropColumn('value_template');
        });
    }
};
