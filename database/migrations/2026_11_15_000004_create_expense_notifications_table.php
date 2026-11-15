<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');

            $table->enum('channel', ['in_app', 'mail', 'whatsapp']);
            $table->enum('kind', ['due_soon', 'due_today', 'overdue']);
            $table->timestamp('sent_at');

            $table->timestamps();

            $table->unique(['expense_id', 'channel', 'kind']);
            $table->index(['user_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_notifications');
    }
};
