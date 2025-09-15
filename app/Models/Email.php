<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'content',
        'from_email',
        'to_email',
        'cc_emails',
        'bcc_emails',
        'status',
        'is_read',
        'is_important',
        'attachments',
        'ai_summary',
        'ai_reply',
        'ai_suggestions',
        'ai_category',
        'ai_priority',
        'ai_sentiment',
        'ai_key_info',
        'ai_suggested_action',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachments' => 'array',
        'ai_suggestions' => 'array',
        'ai_sentiment' => 'array',
        'ai_key_info' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
