<?php

namespace App\Traits;

trait HasRoles
{
    /**
     * Verificar se o usuário é admin
     */
    public function isAdmin(): bool
    {
        // Opção 1: Se você tem coluna 'is_admin' na tabela users
        return $this->is_admin ?? false;
        
        // Opção 2: Se você tem coluna 'role' na tabela users
        // return $this->role === 'admin';
        
        // Opção 3: Se você tem sistema de roles/permissions separado
        // return $this->hasRole('admin');
    }

    /**
     * Verificar se o usuário é manager/gerente
     */
    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->is_admin ?? false;
    }

    /**
     * Verificar se o usuário tem uma role específica
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Verificar se o usuário tem permissão para gerenciar leads
     */
    public function canManageLeads(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'sales']);
    }

    /**
     * Verificar se o usuário pode ver todos os leads do tenant
     */
    public function canViewAllLeads(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }
}
