<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'content',
        'type',
        'is_pinned',
        'is_private',
        'mentions',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_private' => 'boolean',
        'mentions' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}