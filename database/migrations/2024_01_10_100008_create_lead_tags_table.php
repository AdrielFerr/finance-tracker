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
        Schema::create('lead_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->text('description')->nullable();
            $table->integer('leads_count')->default(0); // Cache do número de leads
            $table->timestamps();
            
            // Índices
            $table->index('tenant_id');
            $table->index('slug');
        });

        // Tabela pivot para relação many-to-many
        Schema::create('lead_tag', function (Blueprint $table) {
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_tag_id')->constrained('lead_tags')->onDelete('cascade');
            $table->timestamps();
            
            // Chave primária composta
            $table->primary(['lead_id', 'lead_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_tag');
        Schema::dropIfExists('lead_tags');
    }
};
