<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RecurringExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'payment_method_id',
        'description',
        'notes',
        'amount',
        'type',
        'frequency',
        'due_day',
        'start_date',
        'end_date',
        'auto_generate',
        'days_before_generation',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_generate' => 'boolean',
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
     * Relacionamento com Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relacionamento com PaymentMethod
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relacionamento com Expenses geradas
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Scope para buscar apenas recorrentes ativas
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
     * Scope para recorrentes que devem gerar despesas
     */
    public function scopeShouldGenerate($query)
    {
        return $query->where('auto_generate', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Retorna o texto traduzido da frequência
     */
    public function getFrequencyTextAttribute(): string
    {
        return match($this->frequency) {
            'monthly' => 'Mensal',
            'bimonthly' => 'Bimestral',
            'quarterly' => 'Trimestral',
            'semiannual' => 'Semestral',
            'annual' => 'Anual',
            default => 'Desconhecido',
        };
    }

    /**
     * Calcula a próxima data de vencimento
     */
    public function getNextDueDate(): Carbon
    {
        $today = Carbon::today();
        $dueDay = $this->due_day;
        
        // Se o dia de vencimento já passou neste mês
        if ($today->day > $dueDay) {
            $nextMonth = $today->copy()->addMonth();
        } else {
            $nextMonth = $today->copy();
        }
        
        // Ajustar para o dia de vencimento
        return $nextMonth->day($dueDay);
    }

    /**
     * Verifica se deve gerar despesa para determinado mês
     */
    public function shouldGenerateForMonth(Carbon $date): bool
    {
        if (!$this->is_active || !$this->auto_generate) {
            return false;
        }

        // Verificar se está dentro do período
        if ($date->lessThan($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->greaterThan($this->end_date)) {
            return false;
        }

        // Verificar frequência
        $monthsDiff = $this->start_date->diffInMonths($date);

        return match($this->frequency) {
            'monthly' => true,
            'bimonthly' => $monthsDiff % 2 === 0,
            'quarterly' => $monthsDiff % 3 === 0,
            'semiannual' => $monthsDiff % 6 === 0,
            'annual' => $monthsDiff % 12 === 0,
            default => false,
        };
    }

    /**
     * Verifica se já existe despesa gerada para determinado mês
     */
    public function hasExpenseForMonth(Carbon $date): bool
    {
        return $this->expenses()
            ->whereYear('competence_date', $date->year)
            ->whereMonth('competence_date', $date->month)
            ->exists();
    }

    /**
     * Retorna o valor formatado em BRL
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }
}