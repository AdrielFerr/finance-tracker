<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadPipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'order',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(LeadStage::class, 'pipeline_id')->orderBy('order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'pipeline_id');
    }

    // Scopes
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helpers
    public function getTotalValue(): float
    {
        return $this->leads()->sum('value');
    }

    public function getLeadsCount(): int
    {
        return $this->leads()->count();
    }

    public function getOpenLeadsCount(): int
    {
        return $this->leads()->open()->count();
    }

    public function getWonLeadsCount(): int
    {
        return $this->leads()->won()->count();
    }

    public function getLostLeadsCount(): int
    {
        return $this->leads()->lost()->count();
    }

    public function getConversionRate(): float
    {
        $total = $this->getLeadsCount();
        if ($total === 0) {
            return 0;
        }
        
        $won = $this->getWonLeadsCount();
        return ($won / $total) * 100;
    }
}
