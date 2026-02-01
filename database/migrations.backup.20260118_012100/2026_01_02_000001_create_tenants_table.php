<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da empresa
            $table->string('slug')->unique(); // URL amigável (ex: empresa-abc)
            $table->string('domain')->nullable()->unique(); // Domínio customizado (opcional)
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            
            // Configurações
            $table->json('settings')->nullable(); // JSON com configs personalizadas
            $table->integer('max_users')->default(5); // Limite de usuários
            $table->integer('max_expenses')->default(1000); // Limite de despesas/mês
            
            // Status e plano
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->enum('plan', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};