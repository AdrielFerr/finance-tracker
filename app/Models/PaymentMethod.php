<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'last_four_digits',
        'expiry_date',
        'due_day',
        'closing_day',
        'credit_limit',
        'brand',
        'color',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Expenses
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Relacionamento com RecurringExpenses
     */
    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    /**
     * Scope para buscar apenas métodos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para buscar cartões de crédito
     */
    public function scopeCreditCards($query)
    {
        return $query->where('type', 'credit_card');
    }

    /**
     * Retorna o nome formatado com últimos dígitos (se existir)
     */
    public function getFormattedNameAttribute(): string
    {
        if ($this->last_four_digits) {
            return "{$this->name} (**** {$this->last_four_digits})";
        }
        
        return $this->name;
    }

    /**
     * Retorna o texto traduzido do tipo
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'bank_slip' => 'Boleto',
            'pix' => 'PIX',
            'cash' => 'Dinheiro',
            'bank_transfer' => 'Transferência',
            'other' => 'Outro',
            default => 'Desconhecido',
        };
    }

    /**
     * Verifica se é um cartão de crédito
     */
    public function isCreditCard(): bool
    {
        return $this->type === 'credit_card';
    }

    /**
     * Calcula o limite disponível
     */
    public function getAvailableLimit($month = null, $year = null): float
    {
        if (!$this->credit_limit) {
            return 0;
        }

        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $used = $this->expenses()
            ->whereYear('competence_date', $year)
            ->whereMonth('competence_date', $month)
            ->where('status', '!=', 'canceled')
            ->sum('amount');

        return $this->credit_limit - $used;
    }

    /**
     * Retorna a porcentagem de uso do limite
     */
    public function getLimitUsagePercentage($month = null, $year = null): float
    {
        if (!$this->credit_limit || $this->credit_limit == 0) {
            return 0;
        }

        $available = $this->getAvailableLimit($month, $year);
        $used = $this->credit_limit - $available;

        return ($used / $this->credit_limit) * 100;
    }
}