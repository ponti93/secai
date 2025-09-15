<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'bio',
        'avatar',
        'google_id',
        'google_token',
        'google_refresh_token',
        'google_calendar_connected',
        'google_token_expires_at',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    // Relationships
    public function emails(): HasMany
    {
        return $this->hasMany(\App\Models\Email::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(\App\Models\Document::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(\App\Models\Meeting::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(\App\Models\Inventory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(\App\Models\Expense::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(\App\Models\CalendarEvent::class);
    }

    public function aiUsage(): HasMany
    {
        return $this->hasMany(\App\Models\AiUsage::class);
    }

    public function googleCalendarEvents(): HasMany
    {
        return $this->hasMany(\App\Models\GoogleCalendarEvent::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getUnreadEmailsCount(): int
    {
        return $this->emails()->unread()->count();
    }

    public function getTodaysMeetingsCount(): int
    {
        return $this->meetings()->today()->count();
    }

    public function getUpcomingMeetingsCount(): int
    {
        return $this->meetings()->upcoming()->count();
    }

    public function getLowStockItemsCount(): int
    {
        return $this->inventory()->lowStock()->count();
    }

    public function getPendingExpensesCount(): int
    {
        return $this->expenses()->byStatus('pending')->count();
    }
}