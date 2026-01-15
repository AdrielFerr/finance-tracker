<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'activity_id',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LeadActivity::class);
    }

    public function getUrl(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}