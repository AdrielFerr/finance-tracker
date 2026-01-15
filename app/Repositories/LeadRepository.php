<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\LeadStage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeadRepository
{
    /**
     * Buscar leads com filtros (adaptado para super admin)
     */
    public function getForTenant(
        ?Tenant $tenant,
        ?int $pipelineId = null,
        ?int $stageId = null,
        ?string $status = null,
        ?int $assignedTo = null,
        ?string $search = null,
        int $perPage = 15,
        ?User $user = null
    ): LengthAwarePaginator {
        $query = Lead::with([
            'stage',
            'pipeline',
            'assignedTo',
            'createdBy',
            'activities' => fn($q) => $q->latest()->limit(5),
        ]);

        // Se tem tenant, filtrar por tenant
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } 
        // Senão, filtrar por created_by (super admin vê só o que criou)
        elseif ($user) {
            $query->where('created_by', $user->id);
        }

        // Filtros
        if ($pipelineId) {
            $query->where('pipeline_id', $pipelineId);
        }

        if ($stageId) {
            $query->where('stage_id', $stageId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }

        // Busca
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('order')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Buscar leads para Kanban (adaptado para super admin)
     */
    public function getForKanban(?Tenant $tenant, int $pipelineId, ?User $user = null): Collection
    {
        $query = Lead::with([
            'stage',
            'assignedTo',
            'activities' => fn($q) => $q->latest()->limit(3),
            'products',
        ])
        ->where('pipeline_id', $pipelineId)
        ->where('status', 'open')
        ->orderBy('order');

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        return $query->get()->groupBy('stage_id');
    }

    /**
     * Criar novo lead (adaptado para super admin)
     */
    public function create(?Tenant $tenant, User $user, array $data): Lead
    {
        // Se tem tenant, usar tenant_id
        if ($tenant) {
            $data['tenant_id'] = $tenant->id;
        }
        
        $data['created_by'] = $user->id;
        $data['status'] = 'open';

        // Se não informou estágio, pegar o primeiro do pipeline
        if (!isset($data['stage_id']) && isset($data['pipeline_id'])) {
            $firstStage = LeadStage::where('pipeline_id', $data['pipeline_id'])
                ->orderBy('order')
                ->first();
            
            if ($firstStage) {
                $data['stage_id'] = $firstStage->id;
            }
        }

        $lead = Lead::create($data);

        // Registrar atividade de criação
        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => 'created',
            'description' => 'Lead criado',
        ]);

        return $lead->load(['stage', 'pipeline', 'assignedTo', 'createdBy']);
    }

    /**
     * Atualizar lead
     */
    public function update(Lead $lead, User $user, array $data): Lead
    {
        // Detectar mudanças importantes
        $changes = [];

        if (isset($data['value']) && $data['value'] != $lead->value) {
            $changes[] = [
                'type' => 'value_change',
                'description' => "Valor alterado de R$ " . number_format($lead->value, 2, ',', '.') . 
                                " para R$ " . number_format($data['value'], 2, ',', '.'),
                'metadata' => [
                    'old_value' => $lead->value,
                    'new_value' => $data['value'],
                ],
            ];
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] != $lead->assigned_to) {
            $oldUser = $lead->assignedTo;
            $newUser = User::find($data['assigned_to']);
            
            $changes[] = [
                'type' => 'assignment',
                'description' => "Lead atribuído para " . ($newUser ? $newUser->name : 'Ninguém'),
                'metadata' => [
                    'old_user_id' => $lead->assigned_to,
                    'new_user_id' => $data['assigned_to'],
                ],
            ];
        }

        // Atualizar lead
        $lead->update($data);

        // Registrar atividades de mudanças
        foreach ($changes as $change) {
            $lead->activities()->create([
                'user_id' => $user->id,
                'type' => $change['type'],
                'description' => $change['description'],
                'metadata' => $change['metadata'] ?? null,
            ]);
        }

        return $lead->fresh(['stage', 'pipeline', 'assignedTo', 'createdBy']);
    }

    /**
     * Deletar lead (soft delete)
     */
    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }

    /**
     * Restaurar lead deletado
     */
    public function restore(int $leadId): bool
    {
        $lead = Lead::withTrashed()->find($leadId);
        return $lead ? $lead->restore() : false;
    }

    /**
     * Mover lead para outro estágio
     */
    public function moveToStage(Lead $lead, LeadStage $stage, User $user, int $newOrder = 0): Lead
    {
        $lead->moveToStage($stage, $user);
        
        if ($newOrder > 0) {
            $lead->update(['order' => $newOrder]);
        }

        return $lead->fresh(['stage', 'pipeline']);
    }

    /**
     * Atualizar ordem dos leads no Kanban
     */
    public function updateOrder(array $leads): void
    {
        DB::transaction(function() use ($leads) {
            foreach ($leads as $order => $leadId) {
                Lead::where('id', $leadId)->update(['order' => $order]);
            }
        });
    }

    /**
     * Estatísticas (adaptado para super admin)
     */
    public function getStats(?Tenant $tenant, ?int $pipelineId = null, ?User $user = null): array
    {
        $query = Lead::query();

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        if ($pipelineId) {
            $query->where('pipeline_id', $pipelineId);
        }

        $total = $query->count();
        $open = $query->clone()->where('status', 'open')->count();
        $won = $query->clone()->where('status', 'won')->count();
        $lost = $query->clone()->where('status', 'lost')->count();

        $totalValue = $query->clone()->sum('value') ?? 0;
        $wonValue = $query->clone()->where('status', 'won')->sum('value') ?? 0;
        $openValue = $query->clone()->where('status', 'open')->sum('value') ?? 0;

        $conversionRate = $total > 0 ? ($won / $total) * 100 : 0;

        return [
            'total_leads' => $total,
            'open_leads' => $open,
            'won_leads' => $won,
            'lost_leads' => $lost,
            'total_value' => $totalValue,
            'won_value' => $wonValue,
            'open_value' => $openValue,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }

    /**
     * Leads por estágio (adaptado para super admin)
     */
    public function getByStage(?Tenant $tenant, int $pipelineId, ?User $user = null): Collection
    {
        $query = Lead::select('stage_id', DB::raw('count(*) as count'), DB::raw('sum(value) as total_value'))
            ->with('stage')
            ->where('pipeline_id', $pipelineId)
            ->where('status', 'open')
            ->groupBy('stage_id');

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    /**
     * Leads vencendo (adaptado para super admin)
     */
    public function getUpcoming(?Tenant $tenant, int $days = 7, ?User $user = null): Collection
    {
        $query = Lead::with(['stage', 'assignedTo', 'pipeline'])
            ->where('status', 'open')
            ->whereBetween('expected_close_date', [now(), now()->addDays($days)])
            ->orderBy('expected_close_date');

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    /**
     * Leads atrasados (adaptado para super admin)
     */
    public function getOverdue(?Tenant $tenant, ?User $user = null): Collection
    {
        $query = Lead::with(['stage', 'assignedTo', 'pipeline'])
            ->where('status', 'open')
            ->where('expected_close_date', '<', now())
            ->orderBy('expected_close_date');

        // Filtrar por tenant ou por created_by
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif ($user) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    /**
     * Meus leads (do usuário logado)
     */
    public function getMyLeads(User $user, ?string $status = null): Collection
    {
        $query = Lead::with(['stage', 'pipeline', 'activities'])
            ->where('assigned_to', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Buscar lead por ID com relacionamentos
     */
    public function findWithRelations(int $leadId): ?Lead
    {
        return Lead::with([
            'tenant',
            'pipeline',
            'stage',
            'assignedTo',
            'createdBy',
            'activities.user',
            'products',
            'attachments.user',
            'notes.user',
            'leadTags',
        ])->find($leadId);
    }

    /**
     * Duplicar lead
     */
    public function duplicate(Lead $lead, User $user): Lead
    {
        $newLead = $lead->replicate();
        $newLead->title = $lead->title . ' (Cópia)';
        $newLead->created_by = $user->id;
        $newLead->status = 'open';
        $newLead->won_at = null;
        $newLead->lost_at = null;
        $newLead->lost_reason = null;
        $newLead->save();

        // Copiar produtos
        foreach ($lead->products as $product) {
            $newProduct = $product->replicate();
            $newProduct->lead_id = $newLead->id;
            $newProduct->save();
        }

        // Registrar atividade
        $newLead->activities()->create([
            'user_id' => $user->id,
            'type' => 'created',
            'description' => "Lead duplicado de #{$lead->id}",
        ]);

        return $newLead->load(['stage', 'pipeline', 'products']);
    }
}
