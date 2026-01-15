<?php

namespace App\Policies;

use App\Models\LeadActivity;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar se o usuário pode ver atividades do lead
     */
    public function viewAny(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode ver esta atividade
     */
    public function view(User $user, LeadActivity $activity): bool
    {
        // Atividade deve pertencer a um lead do mesmo tenant
        return $user->tenant_id === $activity->lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode criar atividades
     */
    public function create(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode atualizar esta atividade
     */
    public function update(User $user, LeadActivity $activity): bool
    {
        // Atividade deve pertencer a um lead do mesmo tenant
        // Poderia adicionar: somente o criador pode editar
        return $user->tenant_id === $activity->lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode deletar esta atividade
     */
    public function delete(User $user, LeadActivity $activity): bool
    {
        // Atividade deve pertencer a um lead do mesmo tenant
        // Poderia adicionar: somente o criador pode deletar
        return $user->tenant_id === $activity->lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode completar tarefa
     */
    public function complete(User $user, LeadActivity $activity): bool
    {
        // Atividade deve pertencer a um lead do mesmo tenant
        // Poderia adicionar: somente o responsável pode completar
        return $user->tenant_id === $activity->lead->tenant_id 
            && $activity->type === 'task';
    }
}
