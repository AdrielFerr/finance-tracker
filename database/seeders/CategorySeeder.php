<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $defaultCategories = [
            ['name' => 'Alimentação', 'color' => '#10B981', 'icon' => 'utensils', 'description' => 'Supermercado, restaurantes, delivery'],
            ['name' => 'Transporte', 'color' => '#3B82F6', 'icon' => 'car', 'description' => 'Combustível, transporte público, aplicativos'],
            ['name' => 'Moradia', 'color' => '#8B5CF6', 'icon' => 'home', 'description' => 'Aluguel, condomínio, IPTU'],
            ['name' => 'Contas', 'color' => '#F59E0B', 'icon' => 'file-invoice', 'description' => 'Água, luz, internet, telefone'],
            ['name' => 'Saúde', 'color' => '#EF4444', 'icon' => 'heart', 'description' => 'Plano de saúde, medicamentos, consultas'],
            ['name' => 'Educação', 'color' => '#06B6D4', 'icon' => 'book', 'description' => 'Cursos, mensalidades, livros'],
            ['name' => 'Lazer', 'color' => '#EC4899', 'icon' => 'gamepad', 'description' => 'Cinema, viagens, hobbies'],
            ['name' => 'Vestuário', 'color' => '#14B8A6', 'icon' => 'shirt', 'description' => 'Roupas, calçados, acessórios'],
            ['name' => 'Serviços', 'color' => '#F97316', 'icon' => 'wrench', 'description' => 'Manutenções, reparos, profissionais'],
            ['name' => 'Pets', 'color' => '#A855F7', 'icon' => 'paw', 'description' => 'Veterinário, ração, produtos pet'],
            ['name' => 'Investimentos', 'color' => '#22C55E', 'icon' => 'chart-line', 'description' => 'Ações, fundos, poupança'],
            ['name' => 'Outros', 'color' => '#6B7280', 'icon' => 'ellipsis', 'description' => 'Despesas diversas'],
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $category) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'color' => $category['color'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]);
            }
        }
    }
}