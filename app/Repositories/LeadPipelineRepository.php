<?php

namespace App\Repositories;

use App\Models\LeadPipeline;
use App\Models\LeadStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;


class LeadPipelineRepository
{
    /**
     * Buscar todos os pipelines (adaptado para super admin)
     */
    public function getForTenant(?Tenant $tenant, ?User $user = null, ?bool $activeOnly = false): Collection
    {
        $query = LeadPipeline::with('stages');

        // Se tem tenant, filtrar por tenant
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } 
        // Senão, filtrar por created_by (super admin vê só o que criou)
        elseif ($user) {
            $query->where('created_by', $user->id);
        }

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Buscar pipeline padrão (adaptado para super admin)
     */
    public function getDefault(?Tenant $tenant, ?User $user = null): ?LeadPipeline
    {
        $query = LeadPipeline::with('stages')->where('is_default', true);

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        return $query->first();
    }

    /**
     * Buscar pipeline por ID
     */
    public function find(int $pipelineId): ?LeadPipeline
    {
        return LeadPipeline::with('stages')->find($pipelineId);
    }

    /**
     * Criar novo pipeline
     */
    public function create(?Tenant $tenant, array $data, ?User $user = null): LeadPipeline
    {
        // Se tem tenant, usar tenant_id
        if ($tenant) {
            $data['tenant_id'] = $tenant->id;
        }
        
        // Se tem user, usar created_by
        if ($user) {
            $data['created_by'] = $user->id;
        }

        // Se não existe pipeline padrão, este será o padrão
        $hasDefault = LeadPipeline::where(function($q) use ($tenant, $user) {
            if ($tenant) {
                $q->where('tenant_id', $tenant->id);
            } elseif ($user) {
                $q->where('created_by', $user->id);
            }
        })
        ->where('is_default', true)
        ->exists();

        if (!$hasDefault) {
            $data['is_default'] = true;
        }

        // Definir order se não existe
        if (!isset($data['order'])) {
            $maxOrder = LeadPipeline::where(function($q) use ($tenant, $user) {
                if ($tenant) {
                    $q->where('tenant_id', $tenant->id);
                } elseif ($user) {
                    $q->where('created_by', $user->id);
                }
            })->max('order') ?? 0;
            
            $data['order'] = $maxOrder + 1;
        }

        return LeadPipeline::create($data);
    }

    /**
     * Atualizar pipeline
     */
    public function update(LeadPipeline $pipeline, array $data): LeadPipeline
    {
        // Se marcando como padrão, desmarcar outros
        if (isset($data['is_default']) && $data['is_default']) {
            LeadPipeline::where(function($q) use ($pipeline) {
                if ($pipeline->tenant_id) {
                    $q->where('tenant_id', $pipeline->tenant_id);
                } else {
                    $q->where('created_by', $pipeline->created_by);
                }
            })
            ->where('id', '!=', $pipeline->id)
            ->update(['is_default' => false]);
        }

        $pipeline->update($data);
        return $pipeline->fresh('stages');
    }

    /**
     * Deletar pipeline
     */
    public function delete(LeadPipeline $pipeline): bool
    {
        // Não pode deletar pipeline padrão
        if ($pipeline->is_default) {
            return false;
        }

        // Verificar se tem leads
        if ($pipeline->leads()->count() > 0) {
            // Mover leads para o pipeline padrão
            $user = Auth::user();
            $tenant = $user->tenant;
            $defaultPipeline = $this->getDefault($tenant, $user);
            
            if ($defaultPipeline && $defaultPipeline->id !== $pipeline->id) {
                // Pegar primeiro estágio do pipeline padrão
                $firstStage = $defaultPipeline->stages()->orderBy('order')->first();
                
                if ($firstStage) {
                    // Mover todos os leads
                    $pipeline->leads()->update([
                        'pipeline_id' => $defaultPipeline->id,
                        'stage_id' => $firstStage->id,
                    ]);
                }
            } else {
                return false;
            }
        }

        return $pipeline->delete();
    }

    /**
     * Criar estágio
     */
    public function createStage(LeadPipeline $pipeline, array $data): LeadStage
    {
        $data['pipeline_id'] = $pipeline->id;

        // Se não tem order, pegar próxima
        if (!isset($data['order'])) {
            $maxOrder = $pipeline->stages()->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
        }

        return LeadStage::create($data);
    }

    /**
     * Atualizar estágio
     */
    public function updateStage(LeadStage $stage, array $data): LeadStage
    {
        $stage->update($data);
        return $stage->fresh();
    }

    /**
     * Deletar estágio
     */
    public function deleteStage(LeadStage $stage): bool
    {
        // Verificar se tem leads
        if ($stage->leads()->count() > 0) {
            // Mover leads para o primeiro estágio do pipeline
            $firstStage = $stage->pipeline->stages()
                ->where('id', '!=', $stage->id)
                ->orderBy('order')
                ->first();
            
            if ($firstStage) {
                $stage->leads()->update(['stage_id' => $firstStage->id]);
            } else {
                return false;
            }
        }

        return $stage->delete();
    }

    /**
     * Reordenar estágios
     */
    public function reorderStages(LeadPipeline $pipeline, array $stageIds): void
    {
        foreach ($stageIds as $order => $stageId) {
            LeadStage::where('id', $stageId)
                ->where('pipeline_id', $pipeline->id)
                ->update(['order' => $order + 1]); // +1 porque array começa em 0
        }
    }

    /**
     * Duplicar pipeline
     */
    public function duplicate(LeadPipeline $pipeline, ?Tenant $tenant, ?User $user = null): LeadPipeline
    {
        $newPipeline = $pipeline->replicate();
        $newPipeline->name = $pipeline->name . ' (Cópia)';
        $newPipeline->is_default = false;
        
        // Definir tenant_id ou created_by
        if ($tenant) {
            $newPipeline->tenant_id = $tenant->id;
        }
        if ($user) {
            $newPipeline->created_by = $user->id;
        }
        
        $newPipeline->save();

        // Copiar estágios
        foreach ($pipeline->stages()->orderBy('order')->get() as $stage) {
            $newStage = $stage->replicate();
            $newStage->pipeline_id = $newPipeline->id;
            $newStage->save();
        }

        return $newPipeline->load('stages');
    }

    /**
     * Estatísticas do pipeline
     */
    public function getStats(LeadPipeline $pipeline): array
    {
        $stats = [];

        foreach ($pipeline->stages()->orderBy('order')->get() as $stage) {
            $stats[] = [
                'stage' => $stage,
                'count' => $stage->leads()->where('status', 'open')->count(),
                'value' => $stage->leads()->where('status', 'open')->sum('value'),
            ];
        }

        return $stats;
    }
}
