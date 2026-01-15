<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'color',
        'description',
        'leads_count',
    ];

    protected $casts = [
        'leads_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function leads(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_tag', 'lead_tag_id', 'lead_id')
            ->withTimestamps();
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Atualizar contador automaticamente
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($tag) {
            $tag->leads_count = $tag->leads()->count();
            $tag->saveQuietly();
        });
    }
}
