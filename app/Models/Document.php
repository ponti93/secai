<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'status',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'ai_metadata',
        'ai_summary',
        'ai_category',
        'ai_tags',
        'ai_keywords',
        'ai_sentiment',
        'ai_key_info',
        'ai_insights',
        'ai_extracted_text',
        'collaborators',
        'published_at',
    ];

    protected $casts = [
        'ai_metadata' => 'array',
        'collaborators' => 'array',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
