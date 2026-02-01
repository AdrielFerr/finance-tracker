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
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->decimal('amount', 10, 2);
            
            $table->enum('type', ['fixed', 'variable'])->default('variable');
            
            // Configuração de recorrência
            $table->enum('frequency', [
                'monthly',      // Mensal
                'bimonthly',    // Bimestral
                'quarterly',    // Trimestral
                'semiannual',   // Semestral
                'annual'        // Anual
            ])->default('monthly');
            
            $table->integer('due_day'); // Dia do vencimento (1-31)
            $table->date('start_date'); // Data de início da recorrência
            $table->date('end_date')->nullable(); // Data final (null = indeterminado)
            
            $table->boolean('auto_generate')->default(true); // Gerar automaticamente
            $table->integer('days_before_generation')->default(5); // Quantos dias antes gerar
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'is_active']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};