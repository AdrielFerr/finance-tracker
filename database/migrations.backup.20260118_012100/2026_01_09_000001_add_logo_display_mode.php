<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar nova configuração para modo de exibição da logo
        DB::table('system_settings')->insert([
            'key' => 'logo_display_mode',
            'value' => 'logo_and_name', // Opções: 'logo_and_name' ou 'logo_only'
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->where('key', 'logo_display_mode')->delete();
    }
};