<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'import_batch_id',
        'category_id',
        'expense_id',
        'external_id',
        'signature',
        'occurred_at',
        'description',
        'amount',
        'direction',
        'installment_number',
        'total_installments',
        'status',
        'is_possible_duplicate',
    ];

    protected $casts = [
        'occurred_at' => 'date',
        'amount' => 'decimal:2',
        'is_possible_duplicate' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOutflow($query)
    {
        return $query->where('direction', 'outflow');
    }

    public function isOutflow(): bool
    {
        return $this->direction === 'outflow';
    }

    public function isInstallment(): bool
    {
        return $this->installment_number !== null && $this->total_installments !== null;
    }
}
