<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove as tabelas do modulo de Leads (CRM), descontinuado.
     *
     * A ordem respeita as chaves estrangeiras: filhas primeiro, pais depois.
     * Nenhuma tabela fora do modulo referencia essas tabelas.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'lead_tag',
            'lead_attachments',
            'lead_activities',
            'lead_notes',
            'lead_products',
            'lead_tags',
            'leads',
            'lead_stages',
            'lead_pipelines',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Sem reversao: as migrations que criavam essas tabelas foram removidas
     * junto com o modulo, entao nao ha estrutura para recriar.
     */
    public function down(): void
    {
        //
    }
};
