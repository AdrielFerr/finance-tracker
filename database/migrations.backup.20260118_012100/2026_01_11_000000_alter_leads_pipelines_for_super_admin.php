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
        // Alterar lead_pipelines
        Schema::table('lead_pipelines', function (Blueprint $table) {
            // Permitir tenant_id nullable
            $table->foreignId('tenant_id')->nullable()->change();
            
            // Adicionar created_by
            $table->foreignId('created_by')->nullable()->after('tenant_id')->constrained('users')->onDelete('cascade');
        });

        // Alterar leads
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
        Schema::table('lead_pipelines', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->foreignId('tenant_id')->nullable(false)->change();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
