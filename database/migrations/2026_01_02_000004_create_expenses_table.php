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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->decimal('amount', 10, 2); // Valor da despesa
            
            // Tipo de despesa
            $table->enum('type', [
                'fixed',      // Fixa (sempre o mesmo valor)
                'variable',   // Variável (valor muda)
                'occasional'  // Eventual (não recorrente)
            ])->default('variable');
            
            // Status do pagamento
            $table->enum('status', [
                'pending',    // Pendente
                'paid',       // Pago
                'overdue',    // Vencido
                'canceled'    // Cancelado
            ])->default('pending');
            
            // Datas importantes
            $table->date('due_date'); // Data de vencimento
            $table->date('payment_date')->nullable(); // Data do pagamento
            $table->date('competence_date'); // Competência (mês de referência)
            
            // Recorrência
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('recurring_expense_id')->nullable()->constrained()->onDelete('set null');
            
            // Parcelamento
            $table->boolean('is_installment')->default(false);
            $table->integer('installment_number')->nullable(); // Ex: 3 (parcela 3 de 12)
            $table->integer('total_installments')->nullable(); // Ex: 12 (total de parcelas)
            
            // Comprovante
            $table->string('receipt_path')->nullable(); // Caminho do comprovante
            
            // Campos de auditoria
            $table->timestamps();
            $table->softDeletes();

            // Índices para otimização de consultas
            $table->index(['user_id', 'competence_date']);
            $table->index(['user_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('category_id');
            $table->index('payment_method_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};