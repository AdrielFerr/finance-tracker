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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100); // Ex: "Nubank", "Bradesco Cartão"
            $table->enum('type', [
                'credit_card',
                'debit_card',
                'bank_slip',
                'pix',
                'cash',
                'bank_transfer',
                'other'
            ]);
            $table->string('last_four_digits', 4)->nullable(); // Últimos 4 dígitos do cartão
            $table->date('expiry_date')->nullable(); // Data de vencimento do cartão
            $table->integer('due_day')->nullable(); // Dia de vencimento da fatura (1-31)
            $table->integer('closing_day')->nullable(); // Dia de fechamento da fatura (1-31)
            $table->decimal('credit_limit', 10, 2)->nullable(); // Limite do cartão
            $table->string('brand', 50)->nullable(); // Visa, Mastercard, etc
            $table->string('color', 7)->default('#6B7280');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'is_active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};