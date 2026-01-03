<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot do trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // Ao criar, automaticamente associa ao tenant do usuário logado
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->tenant_id && !$model->user_id) {
                $model->user_id = Auth::id();
            }
        });

        // Scope global: usuários normais só veem dados do próprio tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = Auth::user();

            // Super Admin vê tudo
            if ($user && $user->role === 'super_admin') {
                return;
            }

            // Usuários normais: filtrar pelo tenant via user_id
            if ($user && $user->tenant_id) {
                $builder->whereHas('user', function ($query) use ($user) {
                    $query->where('tenant_id', $user->tenant_id);
                });
            }
        });
    }

    /**
     * Relacionamento com User (que tem tenant_id)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obter tenant através do user
     */
    public function getTenantAttribute(): ?Tenant
    {
        return $this->user?->tenant;
    }
}