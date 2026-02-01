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
        // Agora sim! A tabela leads JÁ EXISTE
        Schema::table('leads', function (Blueprint $table) {
            // Permitir tenant_id nullable
            $table->foreignId('tenant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
