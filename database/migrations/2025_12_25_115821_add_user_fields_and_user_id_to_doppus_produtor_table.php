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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'app_id')) {
                $table->string('app_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'app_secret')) {
                $table->string('app_secret')->nullable();
            }
            if (!Schema::hasColumn('users', 'meta_conta_de_anuncios')) {
                $table->string('meta_conta_de_anuncios')->nullable();
            }
        });

        Schema::table('doppus_produtor', function (Blueprint $table) {
            if (!Schema::hasColumn('doppus_produtor', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doppus_produtor', function (Blueprint $table) {
            if (Schema::hasColumn('doppus_produtor', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'app_id') ? 'app_id' : null,
                Schema::hasColumn('users', 'app_secret') ? 'app_secret' : null,
                Schema::hasColumn('users', 'meta_conta_de_anuncios') ? 'meta_conta_de_anuncios' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
