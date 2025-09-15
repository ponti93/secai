<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarEvent extends Model
{
    protected $fillable = [
        'user_id',
        'google_event_id',
        'calendar_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'attendees',
        'status',
        'is_recurring',
        'recurrence',
        'html_link',
        'last_synced_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'attendees' => 'array',
        'recurrence' => 'array',
        'is_recurring' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
