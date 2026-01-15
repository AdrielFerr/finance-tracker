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
        Schema::create('lead_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('lead_pipelines')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->integer('order')->default(0);
            $table->boolean('is_won')->default(false); // Estágio de ganho
            $table->boolean('is_lost')->default(false); // Estágio de perda
            $table->integer('probability')->nullable(); // % padrão de conversão
            $table->timestamps();
            
            // Índices
            $table->index('pipeline_id');
            $table->index(['pipeline_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_stages');
    }
};
