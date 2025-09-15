<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'participants',
        'status',
        'transcript',
        'ai_summary',
        'action_items',
        'ai_insights',
        'recording_path',
        'audio_file_name',
        'audio_file_size',
        'audio_mime_type',
        'transcription_status',
    ];

    protected $casts = [
        'participants' => 'array',
        'action_items' => 'array',
        'ai_insights' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }
}
