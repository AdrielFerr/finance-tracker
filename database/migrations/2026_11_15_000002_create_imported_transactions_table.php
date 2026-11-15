<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('import_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('expense_id')->nullable()->constrained()->onDelete('set null');

            $table->string('external_id')->nullable();
            $table->string('signature', 64);

            $table->date('occurred_at');
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->enum('direction', ['outflow', 'inflow']);

            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();

            $table->enum('status', ['pending', 'imported', 'ignored'])->default('pending');
            $table->boolean('is_possible_duplicate')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'signature']);
            $table->index(['import_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_transactions');
    }
};
