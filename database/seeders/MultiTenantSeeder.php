<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Category;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiTenantSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. CRIAR SUPER ADMIN (Você)
        // ==========================================
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@financetracker.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'tenant_id' => null, // Super admin não pertence a nenhum tenant
            'is_active' => true,
        ]);

        echo "✅ Super Admin criado: admin@financetracker.com / admin123\n";

        // ==========================================
        // 2. CRIAR TENANT 1: Empresa ABC
        // ==========================================
        $tenantABC = Tenant::create([
            'name' => 'Empresa ABC Ltda',
            'slug' => 'empresa-abc',
            'email' => 'contato@empresaabc.com',
            'phone' => '(83) 3333-4444',
            'address' => 'Rua A, 123 - João Pessoa, PB',
            'max_users' => 10,
            'max_expenses' => 5000,
            'status' => 'active',
            'plan' => 'premium',
            'trial_ends_at' => now()->addDays(30),
            'subscription_ends_at' => now()->addYear(),
        ]);

        // Admin da Empresa ABC
        $adminABC = User::create([
            'name' => 'João Silva',
            'email' => 'joao@empresaabc.com',
            'password' => Hash::make('senha123'),
            'role' => 'tenant_admin',
            'tenant_id' => $tenantABC->id,
            'is_active' => true,
        ]);

        // Usuário normal da Empresa ABC
        $userABC = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@empresaabc.com',
            'password' => Hash::make('senha123'),
            'role' => 'user',
            'tenant_id' => $tenantABC->id,
            'is_active' => true,
        ]);

        // Categorias da Empresa ABC
        $categoriasABC = [
            ['name' => 'Aluguel', 'color' => '#EF4444', 'user_id' => $adminABC->id],
            ['name' => 'Salários', 'color' => '#3B82F6', 'user_id' => $adminABC->id],
            ['name' => 'Marketing', 'color' => '#10B981', 'user_id' => $adminABC->id],
        ];

        foreach ($categoriasABC as $cat) {
            Category::create($cat);
        }

        // Métodos de pagamento da Empresa ABC
        PaymentMethod::create([
            'name' => 'Conta Corrente Empresa',
            'type' => 'bank_transfer',
            'color' => '#6366F1',
            'user_id' => $adminABC->id,
        ]);

        echo "✅ Tenant 1 criado: Empresa ABC (joao@empresaabc.com / senha123)\n";

        // ==========================================
        // 3. CRIAR TENANT 2: Empresa XYZ
        // ==========================================
        $tenantXYZ = Tenant::create([
            'name' => 'Empresa XYZ Comércio',
            'slug' => 'empresa-xyz',
            'email' => 'contato@empresaxyz.com',
            'phone' => '(83) 9999-8888',
            'address' => 'Av. B, 456 - Campina Grande, PB',
            'max_users' => 5,
            'max_expenses' => 1000,
            'status' => 'active',
            'plan' => 'basic',
            'trial_ends_at' => now()->addDays(15),
            'subscription_ends_at' => now()->addMonths(6),
        ]);

        // Admin da Empresa XYZ
        $adminXYZ = User::create([
            'name' => 'Pedro Costa',
            'email' => 'pedro@empresaxyz.com',
            'password' => Hash::make('senha123'),
            'role' => 'tenant_admin',
            'tenant_id' => $tenantXYZ->id,
            'is_active' => true,
        ]);

        // Categorias da Empresa XYZ
        $categoriasXYZ = [
            ['name' => 'Fornecedores', 'color' => '#F59E0B', 'user_id' => $adminXYZ->id],
            ['name' => 'Energia', 'color' => '#8B5CF6', 'user_id' => $adminXYZ->id],
        ];

        foreach ($categoriasXYZ as $cat) {
            Category::create($cat);
        }

        echo "✅ Tenant 2 criado: Empresa XYZ (pedro@empresaxyz.com / senha123)\n";

        // ==========================================
        // RESUMO
        // ==========================================
        echo "\n";
        echo "════════════════════════════════════════════\n";
        echo "📊 RESUMO MULTI-TENANT\n";
        echo "════════════════════════════════════════════\n";
        echo "🔑 SUPER ADMIN:\n";
        echo "   Email: admin@financetracker.com\n";
        echo "   Senha: admin123\n";
        echo "\n";
        echo "🏢 TENANT 1 - Empresa ABC:\n";
        echo "   Admin: joao@empresaabc.com / senha123\n";
        echo "   User:  maria@empresaabc.com / senha123\n";
        echo "   Categorias: 3\n";
        echo "\n";
        echo "🏢 TENANT 2 - Empresa XYZ:\n";
        echo "   Admin: pedro@empresaxyz.com / senha123\n";
        echo "   Categorias: 2\n";
        echo "════════════════════════════════════════════\n";
    }
}