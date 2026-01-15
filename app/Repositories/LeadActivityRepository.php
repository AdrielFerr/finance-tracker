<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LeadActivityRepository
{
    /**
     * Criar atividade
     */
    public function create(Lead $lead, User $user, array $data): LeadActivity
    {
        $data['lead_id'] = $lead->id;
        $data['user_id'] = $user->id;

        return LeadActivity::create($data);
    }

    /**
     * Buscar atividades do lead
     */
    public function getForLead(Lead $lead, ?string $type = null): Collection
    {
        $query = LeadActivity::with('user')
            ->where('lead_id', $lead->id);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Buscar tarefas pendentes (adaptado para super admin)
     */
    public function getPendingTasks(?int $tenantId = null, ?int $userId = null): Collection
    {
        $query = LeadActivity::with(['lead.stage', 'lead.pipeline', 'user'])
            ->where('type', 'task')
            ->where('is_completed', false);

        // Filtrar por tenant ou por created_by
        if ($tenantId) {
            $query->whereHas('lead', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        } elseif ($userId) {
            $query->whereHas('lead', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Buscar minhas tarefas (do usuário logado)
     */
    public function getMyTasks(User $user, ?bool $completedOnly = false): Collection
    {
        $query = LeadActivity::with(['lead.stage', 'lead.pipeline'])
            ->where('type', 'task')
            ->where('user_id', $user->id);

        if ($completedOnly !== null) {
            $query->where('is_completed', $completedOnly);
        }

        return $query->orderBy('due_date')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Marcar tarefa como concluída
     */
    public function completeTask(LeadActivity $activity, User $user): LeadActivity
    {
        $activity->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        return $activity->fresh(['user', 'lead']);
    }

    /**
     * Adicionar nota
     */
    public function addNote(Lead $lead, User $user, string $content, ?bool $isPinned = false): LeadActivity
    {
        return $this->create($lead, $user, [
            'type' => 'note',
            'description' => $content,
            'is_pinned' => $isPinned ?? false,
        ]);
    }

    /**
     * Registrar ligação
     */
    public function logCall(Lead $lead, User $user, array $data): LeadActivity
    {
        $description = $data['description'] ?? 'Ligação realizada';
        
        $metadata = [
            'duration' => $data['duration'] ?? null,
            'outcome' => $data['outcome'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        return $this->create($lead, $user, [
            'type' => 'call',
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Registrar reunião
     */
    public function logMeeting(Lead $lead, User $user, array $data): LeadActivity
    {
        $description = $data['description'] ?? 'Reunião realizada';
        
        $metadata = [
            'location' => $data['location'] ?? null,
            'attendees' => $data['attendees'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        return $this->create($lead, $user, [
            'type' => 'meeting',
            'description' => $description,
            'metadata' => $metadata,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
    }

    /**
     * Agendar tarefa
     */
    public function scheduleTask(Lead $lead, User $user, array $data): LeadActivity
    {
        return $this->create($lead, $user, [
            'type' => 'task',
            'description' => $data['description'],
            'due_date' => $data['due_date'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'is_completed' => false,
        ]);
    }

    /**
     * Atualizar atividade
     */
    public function update(LeadActivity $activity, array $data): LeadActivity
    {
        $activity->update($data);
        return $activity->fresh(['user', 'lead']);
    }

    /**
     * Deletar atividade
     */
    public function delete(LeadActivity $activity): bool
    {
        return $activity->delete();
    }

    /**
     * Buscar timeline do lead (todas atividades)
     */
    public function getTimeline(Lead $lead): Collection
    {
        return LeadActivity::with('user')
            ->where('lead_id', $lead->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar atividades fixadas
     */
    public function getPinned(Lead $lead): Collection
    {
        return LeadActivity::with('user')
            ->where('lead_id', $lead->id)
            ->where('is_pinned', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
