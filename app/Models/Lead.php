<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'pipeline_id',
        'stage_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_position',
        'company_name',
        'company_size',
        'company_address',
        'value',
        'currency',
        'source',
        'source_details',
        'expected_close_date',
        'contacted_at',
        'won_at',
        'lost_at',
        'lost_reason',
        'priority',
        'probability',
        'status',
        'tags',
        'custom_fields',
        'order',
        'score',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'tags' => 'array',
        'custom_fields' => 'array',
        'expected_close_date' => 'date',
        'contacted_at' => 'datetime',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'score' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(LeadProduct::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function leadTags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'lead_tag', 'lead_id', 'lead_tag_id')
            ->withTimestamps();
    }

    // Scopes
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStage($query, $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helpers
    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getTotalProductsValue(): float
    {
        return $this->products->sum('total');
    }

    public function moveToStage(LeadStage $stage, User $user): void
    {
        $oldStage = $this->stage;
        
        $this->update(['stage_id' => $stage->id]);
        
        // Registrar atividade
        $this->activities()->create([
            'user_id' => $user->id,
            'type' => 'stage_change',
            'description' => "Movido de '{$oldStage->name}' para '{$stage->name}'",
            'metadata' => [
                'old_stage_id' => $oldStage->id,
                'new_stage_id' => $stage->id,
            ],
        ]);

        // Se moveu para estágio de ganho/perda
        if ($stage->is_won) {
            $this->markAsWon($user);
        } elseif ($stage->is_lost) {
            $this->markAsLost($user);
        }
    }

    public function markAsWon(User $user, ?string $note = null): void
    {
        $this->update([
            'status' => 'won',
            'won_at' => now(),
            'probability' => 100,
        ]);

        $this->activities()->create([
            'user_id' => $user->id,
            'type' => 'status_change',
            'description' => 'Lead marcado como ganho' . ($note ? ": {$note}" : ''),
        ]);
    }

    public function markAsLost(User $user, ?string $reason = null): void
    {
        $this->update([
            'status' => 'lost',
            'lost_at' => now(),
            'lost_reason' => $reason,
            'probability' => 0,
        ]);

        $this->activities()->create([
            'user_id' => $user->id,
            'type' => 'status_change',
            'description' => 'Lead marcado como perdido' . ($reason ? ": {$reason}" : ''),
            'metadata' => ['reason' => $reason],
        ]);
    }

    public function reopen(User $user): void
    {
        $this->update([
            'status' => 'open',
            'won_at' => null,
            'lost_at' => null,
            'lost_reason' => null,
        ]);

        $this->activities()->create([
            'user_id' => $user->id,
            'type' => 'status_change',
            'description' => 'Lead reaberto',
        ]);
    }
}
