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
        Schema::create('lead_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('lead_activities')->onDelete('cascade');
            
            // Informações do arquivo
            $table->string('filename'); // Nome salvo no storage
            $table->string('original_name'); // Nome original do arquivo
            $table->string('mime_type');
            $table->bigInteger('size'); // Tamanho em bytes
            $table->string('path'); // Caminho completo no storage
            $table->string('disk')->default('local'); // public, s3, etc
            
            // Tipo de anexo
            $table->enum('type', [
                'document',    // Documentos gerais
                'image',       // Imagens
                'proposal',    // Proposta comercial
                'contract',    // Contrato
                'invoice',     // Nota fiscal
                'receipt',     // Recibo
                'presentation',// Apresentação
                'other'
            ])->default('document');
            
            // Metadados
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Dados extras como dimensões de imagem, etc
            
            $table->timestamps();
            
            // Índices
            $table->index('lead_id');
            $table->index('user_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_attachments');
    }
};
