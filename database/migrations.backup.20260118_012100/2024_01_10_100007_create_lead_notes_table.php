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
        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Conteúdo da nota
            $table->text('content');
            
            // Tipo de nota
            $table->enum('type', [
                'general',      // Nota geral
                'important',    // Importante
                'follow_up',    // Follow-up
                'meeting',      // Reunião
                'internal'      // Interna (não visível para cliente)
            ])->default('general');
            
            // Visibilidade
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_private')->default(false); // Visível apenas para o criador
            
            // Menções (@usuario)
            $table->json('mentions')->nullable(); // IDs dos usuários mencionados
            
            $table->timestamps();
            
            // Índices
            $table->index('lead_id');
            $table->index('user_id');
            $table->index(['lead_id', 'is_pinned']);
            $table->index(['lead_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
    }
};
