<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'source',
        'original_filename',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ImportedTransaction::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', 'reviewing');
    }

    public function isReviewing(): bool
    {
        return $this->status === 'reviewing';
    }

    public function getSourceTextAttribute(): string
    {
        return match ($this->source) {
            'ofx' => 'OFX',
            'csv' => 'CSV',
            'pdf' => 'PDF',
            default => strtoupper((string) $this->source),
        };
    }
}
