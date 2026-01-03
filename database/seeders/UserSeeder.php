<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se o usuário já existe antes de criar
        if (!User::where('email', 'adriel@financetracker.com')->exists()) {
            User::create([
                'name' => 'Adriel Ferreira',
                'email' => 'adriel@financetracker.com',
                'password' => Hash::make('senha123'),
                'email_verified_at' => now(),
            ]);
        }

        // Criar mais alguns usuários de teste (opcional)
        // User::factory(5)->create();
    }
}