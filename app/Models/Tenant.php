<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'address',
        'logo',
        'settings',
        'max_users',
        'max_expenses',
        'status',
        'plan',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Usuários do tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Todas as despesas do tenant (via users)
     */
    public function expenses()
    {
        return Expense::whereHas('user', function($query) {
            $query->where('tenant_id', $this->id);
        });
    }

    /**
     * Todas as categorias do tenant (via users)
     */
    public function categories()
    {
        return Category::whereHas('user', function($query) {
            $query->where('tenant_id', $this->id);
        });
    }

    /**
     * Todos os métodos de pagamento do tenant (via users)
     */
    public function paymentMethods()
    {
        return PaymentMethod::whereHas('user', function($query) {
            $query->where('tenant_id', $this->id);
        });
    }

    /**
     * Verificar se está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar se está em trial
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Verificar se expirou
     */
    public function hasExpired(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    /**
     * Verificar limite de usuários
     */
    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    /**
     * Obter configuração específica
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Definir configuração
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }
}