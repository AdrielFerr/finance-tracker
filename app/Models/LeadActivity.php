<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadActivity extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'title',
        'description',
        'metadata',
        'scheduled_at',
        'due_at',
        'completed_at',
        'is_completed',
        'priority',
        'is_pinned',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->is_completed || !is_null($this->completed_at);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'is_completed' => true,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}