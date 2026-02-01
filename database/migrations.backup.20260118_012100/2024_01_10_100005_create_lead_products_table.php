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
        Schema::create('lead_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            
            // Informações do produto/serviço
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable(); // Código do produto
            
            // Quantidades e valores
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 5, 2)->default(0); // Desconto em %
            $table->decimal('discount_amount', 15, 2)->default(0); // Valor do desconto
            $table->decimal('subtotal', 15, 2); // quantity * unit_price
            $table->decimal('total', 15, 2); // subtotal - discount_amount
            
            // Impostos (opcional)
            $table->decimal('tax_rate', 5, 2)->default(0); // % de imposto
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            // Recorrência (para produtos SaaS/assinaturas)
            $table->boolean('is_recurring')->default(false);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual'])->nullable();
            
            // Ordem de exibição
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_products');
    }
};
