<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'payment_method_id',
        'description',
        'notes',
        'amount',
        'type',
        'status',
        'due_date',
        'payment_date',
        'competence_date',
        'is_recurring',
        'recurring_expense_id',
        'is_installment',
        'installment_number',
        'total_installments',
        'receipt_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'competence_date' => 'date',
        'is_recurring' => 'boolean',
        'is_installment' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Atualizar status automaticamente ao definir payment_date
        static::saving(function ($expense) {
            if ($expense->payment_date && $expense->status === 'pending') {
                $expense->status = 'paid';
            }

            // Verificar se está vencida
            if (!$expense->payment_date && 
                $expense->due_date < Carbon::today() && 
                $expense->status === 'pending') {
                $expense->status = 'overdue';
            }
        });

        // Deletar arquivo ao remover despesa
        static::deleting(function ($expense) {
            if ($expense->receipt_path) {
                Storage::delete($expense->receipt_path);
            }
        });
    }

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
     * Relacionamento com RecurringExpense
     */
    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    /**
     * Scope para despesas pagas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para despesas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para despesas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Scope para filtrar por período de competência
     */
    public function scopeInCompetencePeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('competence_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por mês/ano específico
     */
    public function scopeForMonth($query, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        return $query->whereBetween('competence_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para despesas recorrentes
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope para despesas parceladas
     */
    public function scopeInstallment($query)
    {
        return $query->where('is_installment', true);
    }

    /**
     * Verifica se a despesa está vencida
     */
    public function isOverdue(): bool
    {
        return $this->due_date < Carbon::today() && 
               $this->status === 'pending';
    }

    /**
     * Verifica se a despesa vence hoje
     */
    public function isDueToday(): bool
    {
        return $this->due_date->isToday() && 
               $this->status === 'pending';
    }

    /**
     * Verifica se a despesa vence em breve (próximos X dias)
     */
    public function isDueSoon($days = 3): bool
    {
        return $this->due_date <= Carbon::today()->addDays($days) && 
               $this->due_date >= Carbon::today() &&
               $this->status === 'pending';
    }

    /**
     * Marca a despesa como paga
     */
    public function markAsPaid($paymentDate = null)
    {
        $this->status = 'paid';
        $this->payment_date = $paymentDate ?? Carbon::now();
        $this->save();
    }

    /**
     * Marca a despesa como cancelada
     */
    public function markAsCanceled()
    {
        $this->status = 'canceled';
        $this->save();
    }

    /**
     * Retorna a descrição formatada com parcela (se for parcelada)
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $description = $this->description;
        
        if ($this->is_installment) {
            $description .= " ({$this->installment_number}/{$this->total_installments})";
        }
        
        return $description;
    }

    /**
     * Retorna o valor formatado em BRL
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Retorna a classe CSS baseada no status
     */
    public function getStatusColorClassAttribute(): string
    {
        return match($this->status) {
            'paid' => 'text-green-600 bg-green-50',
            'pending' => 'text-yellow-600 bg-yellow-50',
            'overdue' => 'text-red-600 bg-red-50',
            'canceled' => 'text-gray-600 bg-gray-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }

    /**
     * Retorna o texto traduzido do status
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'overdue' => 'Vencido',
            'canceled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    /**
     * Retorna URL do comprovante
     */
    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? Storage::url($this->receipt_path) : null;
    }
}