<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
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

        // Gerar slug automaticamente ao criar
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Atualizar slug ao modificar o nome
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
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
     * Scope para buscar apenas categorias ativas
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
     * Calcula o total gasto na categoria em um período
     */
    public function totalExpensesInPeriod($startDate, $endDate)
    {
        return $this->expenses()
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->sum('amount');
    }

    /**
     * Calcula a porcentagem de gastos desta categoria no total
     */
    public function percentageOfTotal($startDate, $endDate)
    {
        $categoryTotal = $this->totalExpensesInPeriod($startDate, $endDate);
        
        $userTotal = Expense::where('user_id', $this->user_id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->sum('amount');

        if ($userTotal == 0) {
            return 0;
        }

        return ($categoryTotal / $userTotal) * 100;
    }

    /**
     * Retorna a cor em formato RGB
     */
    public function getRgbColorAttribute(): string
    {
        $hex = ltrim($this->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgb($r, $g, $b)";
    }

    /**
     * Verifica se a categoria pode ser deletada
     */
    public function canBeDeleted(): bool
    {
        return $this->expenses()->count() === 0 && 
               $this->recurringExpenses()->count() === 0;
    }
}