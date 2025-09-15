<?php

namespace App\Services;

use Google\Client;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\GoogleCalendarEvent;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;
    protected $calendarService;
    protected $accessToken;

    public function __construct()
    {
        // Initialize with basic HTTP client for now
        $this->client = new GuzzleClient([
            'base_uri' => 'https://www.googleapis.com/',
            'timeout' => 30,
        ]);
    }

    /**
     * Get authorization URL for OAuth
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'scope' => 'https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/calendar.events',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];
        
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): array
    {
        try {
            $response = $this->client->post('oauth2/v4/token', [
                'form_params' => [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => config('services.google.redirect'),
                ]
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Google Calendar token exchange failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set access token for authenticated user
     */
    public function setAccessToken(array $token): void
    {
        // Store token for use in API calls
        $this->accessToken = $token['access_token'] ?? $token;
    }

    /**
     * Get user info from Google
     */
    public function getUserInfo(string $accessToken): array
    {
        try {
            $response = $this->client->get('oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Failed to get user info: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refresh access token if needed
     */
    public function refreshTokenIfNeeded(): bool
    {
        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if ($refreshToken) {
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Get user's calendars
     */
    public function getCalendars(): array
    {
        try {
            $this->refreshTokenIfNeeded();
            $response = $this->client->get('https://www.googleapis.com/calendar/v3/users/me/calendarList');
            $data = json_decode($response->getBody(), true);
            return $data['items'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch calendars: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get events from a specific calendar
     */
    public function getEvents(string $calendarId, array $options = []): array
    {
        try {
            $params = [
                'maxResults' => $options['maxResults'] ?? 100,
                'orderBy' => 'startTime',
                'singleEvents' => 'true',
            ];

            if (isset($options['timeMin'])) {
                $params['timeMin'] = $options['timeMin'];
            }
            if (isset($options['timeMax'])) {
                $params['timeMax'] = $options['timeMax'];
            }

            $url = 'calendar/v3/calendars/' . urlencode($calendarId) . '/events?' . http_build_query($params);
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['items'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch events: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new event
     */
    public function createEvent(string $calendarId, array $eventData): array
    {
        try {
            $this->refreshTokenIfNeeded();
            
            $event = [
                'summary' => $eventData['title'],
                'description' => $eventData['description'] ?? '',
                'location' => $eventData['location'] ?? '',
                'start' => [
                    'dateTime' => Carbon::parse($eventData['start_time'])->toRfc3339String(),
                    'timeZone' => 'UTC',
                ],
                'end' => [
                    'dateTime' => Carbon::parse($eventData['end_time'])->toRfc3339String(),
                    'timeZone' => 'UTC',
                ],
            ];

            // Set attendees if provided
            if (isset($eventData['attendees']) && is_array($eventData['attendees'])) {
                $attendees = [];
                foreach ($eventData['attendees'] as $email) {
                    $attendees[] = ['email' => $email];
                }
                $event['attendees'] = $attendees;
            }

            $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarId) . '/events';
            $response = $this->client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($event)
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Failed to create event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing event
     */
    public function updateEvent(string $calendarId, string $eventId, array $eventData): array
    {
        try {
            $this->refreshTokenIfNeeded();
            
            // First get the existing event
            $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarId) . '/events/' . urlencode($eventId);
            $response = $this->client->get($url);
            $event = json_decode($response->getBody(), true);
            
            // Update the event data
            $event['summary'] = $eventData['title'];
            $event['description'] = $eventData['description'] ?? '';
            $event['location'] = $eventData['location'] ?? '';
            $event['start'] = [
                'dateTime' => Carbon::parse($eventData['start_time'])->toRfc3339String(),
                'timeZone' => 'UTC',
            ];
            $event['end'] = [
                'dateTime' => Carbon::parse($eventData['end_time'])->toRfc3339String(),
                'timeZone' => 'UTC',
            ];

            // Update attendees if provided
            if (isset($eventData['attendees']) && is_array($eventData['attendees'])) {
                $attendees = [];
                foreach ($eventData['attendees'] as $email) {
                    $attendees[] = ['email' => $email];
                }
                $event['attendees'] = $attendees;
            }

            // Update the event
            $response = $this->client->put($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($event)
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Failed to update event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an event
     */
    public function deleteEvent(string $calendarId, string $eventId): bool
    {
        try {
            $this->refreshTokenIfNeeded();
            $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarId) . '/events/' . urlencode($eventId);
            $this->client->delete($url);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync events from Google Calendar to local database
     */
    public function syncEventsToDatabase(User $user, string $calendarId = 'primary'): int
    {
        try {
            $this->setAccessToken(json_decode($user->google_token, true));
            $events = $this->getEvents($calendarId);
            $syncedCount = 0;

            foreach ($events as $googleEvent) {
                $existingEvent = GoogleCalendarEvent::where('google_event_id', $googleEvent['id'])
                    ->where('user_id', $user->id)
                    ->first();

                $eventData = [
                    'user_id' => $user->id,
                    'google_event_id' => $googleEvent['id'],
                    'calendar_id' => $calendarId,
                    'title' => $googleEvent['summary'] ?? 'No Title',
                    'description' => $googleEvent['description'] ?? '',
                    'start_time' => $googleEvent['start']['dateTime'] ?? $googleEvent['start']['date'],
                    'end_time' => $googleEvent['end']['dateTime'] ?? $googleEvent['end']['date'],
                    'location' => $googleEvent['location'] ?? '',
                    'attendees' => isset($googleEvent['attendees']) ? array_map(fn($a) => $a['email'], $googleEvent['attendees']) : null,
                    'status' => $googleEvent['status'] ?? 'confirmed',
                    'html_link' => $googleEvent['htmlLink'] ?? '',
                    'last_synced_at' => now(),
                ];

                if ($existingEvent) {
                    $existingEvent->update($eventData);
                } else {
                    GoogleCalendarEvent::create($eventData);
                }
                $syncedCount++;
            }

            return $syncedCount;
        } catch (\Exception $e) {
            Log::error('Failed to sync events: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync local events to Google Calendar
     */
    public function syncEventsToGoogle(User $user, string $calendarId = 'primary'): int
    {
        try {
            $this->setAccessToken(json_decode($user->google_token, true));
            $localEvents = GoogleCalendarEvent::where('user_id', $user->id)->get();
            $syncedCount = 0;

            foreach ($localEvents as $localEvent) {
                $eventData = [
                    'title' => $localEvent->title,
                    'description' => $localEvent->description,
                    'start_time' => $localEvent->start_time,
                    'end_time' => $localEvent->end_time,
                    'location' => $localEvent->location,
                    'attendees' => $localEvent->attendees,
                ];

                if ($localEvent->google_event_id) {
                    // Update existing event
                    $this->updateEvent($calendarId, $localEvent->google_event_id, $eventData);
                } else {
                    // Create new event
                    $googleEvent = $this->createEvent($calendarId, $eventData);
                    $localEvent->update(['google_event_id' => $googleEvent['id']]);
                }
                $syncedCount++;
            }

            return $syncedCount;
        } catch (\Exception $e) {
            Log::error('Failed to sync events to Google: ' . $e->getMessage());
            throw $e;
        }
    }
}