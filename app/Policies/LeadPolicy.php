<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar se o usuário pode ver qualquer lead
     */
    public function viewAny(User $user): bool
    {
        // Usuário precisa estar associado a um tenant
        return $user->tenant_id !== null;
    }

    /**
     * Determinar se o usuário pode ver este lead
     */
    public function view(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode criar leads
     */
    public function create(User $user): bool
    {
        // Usuário precisa estar associado a um tenant
        return $user->tenant_id !== null;
    }

    /**
     * Determinar se o usuário pode atualizar este lead
     */
    public function update(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode deletar este lead
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        // Poderia adicionar: somente admins ou criador pode deletar
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode restaurar este lead
     */
    public function restore(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode deletar permanentemente este lead
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        // Apenas admins podem deletar permanentemente
        // Ajuste conforme seu sistema de roles
        return $user->tenant_id === $lead->tenant_id && $user->isAdmin();
    }

    /**
     * Determinar se o usuário pode atribuir o lead para outros
     */
    public function assign(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode marcar como ganho/perdido
     */
    public function changeStatus(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        // Poderia adicionar: somente responsável ou admin
        return $user->tenant_id === $lead->tenant_id;
    }

    /**
     * Determinar se o usuário pode mover entre estágios
     */
    public function moveStage(User $user, Lead $lead): bool
    {
        // Lead deve pertencer ao mesmo tenant do usuário
        return $user->tenant_id === $lead->tenant_id;
    }
}
