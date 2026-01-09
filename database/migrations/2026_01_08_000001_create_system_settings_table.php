<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  // ← NOVA LINHA

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('system_settings')->insert([
            ['key' => 'app_name', 'value' => 'FinanceTracker', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_path', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'favicon_path', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#4F46E5', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};