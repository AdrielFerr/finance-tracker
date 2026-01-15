<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadPipeline;
use App\Models\LeadStage;
use App\Models\Tenant;

class LeadPipelineSeeder extends Seeder
{
    public function run(): void
    {
        // Para cada tenant, criar pipeline padrão
        Tenant::all()->each(function ($tenant) {
            // Pipeline de Vendas Padrão
            $pipeline = LeadPipeline::create([
                'tenant_id' => $tenant->id,
                'name' => 'Pipeline de Vendas',
                'description' => 'Processo padrão de vendas',
                'order' => 1,
                'is_active' => true,
            ]);

            // Estágios padrão
            $stages = [
                [
                    'name' => 'Novo Lead',
                    'color' => '#94a3b8', // Cinza
                    'order' => 1,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => 'Contato Inicial',
                    'color' => '#60a5fa', // Azul
                    'order' => 2,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => 'Qualificação',
                    'color' => '#a78bfa', // Roxo
                    'order' => 3,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => 'Proposta Enviada',
                    'color' => '#f59e0b', // Amarelo
                    'order' => 4,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => 'Negociação',
                    'color' => '#fb923c', // Laranja
                    'order' => 5,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => 'Fechamento',
                    'color' => '#34d399', // Verde claro
                    'order' => 6,
                    'is_won' => false,
                    'is_lost' => false,
                ],
                [
                    'name' => '✅ Ganho',
                    'color' => '#10b981', // Verde
                    'order' => 7,
                    'is_won' => true,
                    'is_lost' => false,
                ],
                [
                    'name' => '❌ Perdido',
                    'color' => '#ef4444', // Vermelho
                    'order' => 8,
                    'is_won' => false,
                    'is_lost' => true,
                ],
            ];

            foreach ($stages as $stage) {
                LeadStage::create([
                    'pipeline_id' => $pipeline->id,
                    ...$stage,
                ]);
            }
        });
    }
}
