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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Relações
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('pipeline_id')->constrained('lead_pipelines')->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('lead_stages')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Informações básicas
            $table->string('title'); // Ex: "Venda de Software para Empresa X"
            $table->text('description')->nullable();
            
            // Informações de contato
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_position')->nullable(); // Cargo
            $table->string('company_name')->nullable();
            $table->string('company_size')->nullable(); // Ex: "1-10", "11-50", "51-200"
            $table->text('company_address')->nullable();
            
            // Valores financeiros
            $table->decimal('value', 15, 2)->default(0); // Valor total estimado
            $table->string('currency', 3)->default('BRL');
            
            // Origem do lead
            $table->enum('source', [
                'website',
                'referral',
                'social_media',
                'email_campaign',
                'cold_call',
                'event',
                'partner',
                'organic_search',
                'paid_ads',
                'other'
            ])->nullable();
            $table->string('source_details')->nullable(); // Detalhes da origem
            
            // Datas importantes
            $table->date('expected_close_date')->nullable();
            $table->timestamp('contacted_at')->nullable(); // Primeiro contato
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason')->nullable();
            
            // Prioridade
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Probabilidade de conversão (0-100%)
            $table->integer('probability')->default(50);
            
            // Status
            $table->enum('status', ['open', 'won', 'lost'])->default('open');
            
            // Tags/Labels (JSON)
            $table->json('tags')->nullable();
            
            // Custom fields (JSON) - para campos personalizados
            $table->json('custom_fields')->nullable();
            
            // Ordem no Kanban
            $table->integer('order')->default(0);
            
            // Pontuação do lead (lead scoring)
            $table->integer('score')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tenant_id');
            $table->index('pipeline_id');
            $table->index('stage_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('expected_close_date');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'pipeline_id', 'stage_id']);
            $table->fullText(['title', 'description', 'contact_name', 'company_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
