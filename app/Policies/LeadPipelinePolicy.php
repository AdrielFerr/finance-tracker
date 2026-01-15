<?php

namespace App\Policies;

use App\Models\LeadPipeline;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPipelinePolicy
{
    use HandlesAuthorization;

    /**
     * Determinar se o usuário pode ver qualquer pipeline
     */
    public function viewAny(User $user): bool
    {
        // Usuário precisa estar associado a um tenant
        return $user->tenant_id !== null;
    }

    /**
     * Determinar se o usuário pode ver este pipeline
     */
    public function view(User $user, LeadPipeline $pipeline): bool
    {
        // Pipeline deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $pipeline->tenant_id;
    }

    /**
     * Determinar se o usuário pode criar pipelines
     */
    public function create(User $user): bool
    {
        // Usuário precisa estar associado a um tenant
        // Poderia adicionar: somente admins podem criar pipelines
        return $user->tenant_id !== null;
    }

    /**
     * Determinar se o usuário pode atualizar este pipeline
     */
    public function update(User $user, LeadPipeline $pipeline): bool
    {
        // Pipeline deve pertencer ao mesmo tenant do usuário
        // Poderia adicionar: somente admins podem editar pipelines
        return $user->tenant_id === $pipeline->tenant_id;
    }

    /**
     * Determinar se o usuário pode deletar este pipeline
     */
    public function delete(User $user, LeadPipeline $pipeline): bool
    {
        // Pipeline deve pertencer ao mesmo tenant do usuário
        // Poderia adicionar: somente admins podem deletar pipelines
        return $user->tenant_id === $pipeline->tenant_id;
    }

    /**
     * Determinar se o usuário pode restaurar este pipeline
     */
    public function restore(User $user, LeadPipeline $pipeline): bool
    {
        return $user->tenant_id === $pipeline->tenant_id;
    }

    /**
     * Determinar se o usuário pode deletar permanentemente este pipeline
     */
    public function forceDelete(User $user, LeadPipeline $pipeline): bool
    {
        // Apenas admins podem deletar permanentemente
        return $user->tenant_id === $pipeline->tenant_id && $user->isAdmin();
    }

    /**
     * Determinar se o usuário pode gerenciar estágios
     */
    public function manageStages(User $user, LeadPipeline $pipeline): bool
    {
        // Pipeline deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $pipeline->tenant_id;
    }

    /**
     * Determinar se o usuário pode duplicar pipeline
     */
    public function duplicate(User $user, LeadPipeline $pipeline): bool
    {
        // Pipeline deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $pipeline->tenant_id;
    }
}
