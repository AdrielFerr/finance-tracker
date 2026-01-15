<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'order',
        'is_won',
        'is_lost',
        'probability',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'order' => 'integer',
        'probability' => 'integer',
    ];

    // Relationships
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    // Helpers
    public function getLeadsCount(): int
    {
        return $this->leads()->count();
    }

    public function getTotalValue(): float
    {
        return $this->leads()->sum('value');
    }

    public function isEndStage(): bool
    {
        return $this->is_won || $this->is_lost;
    }
}
