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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de atividade
            $table->enum('type', [
                'note',              // Nota/Anotação
                'call',              // Ligação
                'email',             // Email
                'meeting',           // Reunião
                'task',              // Tarefa
                'whatsapp',          // WhatsApp
                'stage_change',      // Mudança de estágio
                'status_change',     // Mudança de status
                'value_change',      // Mudança de valor
                'assignment',        // Atribuição/Reatribuição
                'created',           // Lead criado
                'comment',           // Comentário
            ])->default('note');
            
            // Conteúdo
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Metadados extras (JSON)
            $table->json('metadata')->nullable();
            /* Exemplos de metadata:
            {
                "call_duration": "00:15:30",
                "call_outcome": "interested",
                "email_subject": "Proposta Comercial",
                "meeting_location": "Escritório",
                "meeting_attendees": ["user1", "user2"],
                "old_value": 5000,
                "new_value": 7500,
                "old_stage": "Proposta",
                "new_stage": "Negociação"
            }
            */
            
            // Para tarefas agendadas
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            
            // Prioridade da tarefa
            $table->enum('priority', ['low', 'medium', 'high'])->nullable();
            
            // Visibilidade
            $table->boolean('is_pinned')->default(false); // Fixar no topo
            
            $table->timestamps();
            
            // Índices
            $table->index('lead_id');
            $table->index('user_id');
            $table->index('type');
            $table->index(['lead_id', 'created_at']);
            $table->index('scheduled_at');
            $table->index(['lead_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
