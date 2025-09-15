<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        $authUrl = $this->googleCalendarService->getAuthUrl();
        return redirect($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect('/calendar')->with('error', 'No authorization code received from Google.');
            }

            // Exchange code for token
            $token = $this->googleCalendarService->exchangeCodeForToken($code);
            
            // Get user info from Google
            $userInfo = $this->googleCalendarService->getUserInfo($token['access_token']);
            
            // Find existing user or create new one
            $user = User::where('email', $userInfo['email'])->first();
            
            if ($user) {
                // Update existing user with Google info
                $user->update([
                    'google_id' => $userInfo['id'],
                    'google_token' => json_encode($token),
                    'google_refresh_token' => $token['refresh_token'] ?? null,
                    'google_calendar_connected' => true,
                ]);
            } else {
                // Create new user with Google info
                $user = User::create([
                    'name' => $userInfo['name'],
                    'email' => $userInfo['email'],
                    'password' => bcrypt(Str::random(16)), // Generate random password for Google users
                    'google_id' => $userInfo['id'],
                    'google_token' => json_encode($token),
                    'google_refresh_token' => $token['refresh_token'] ?? null,
                    'google_calendar_connected' => true,
                ]);
            }

            Auth::login($user);

            return redirect('/calendar')->with('success', 'Google Calendar connected successfully!');
        } catch (\Exception $e) {
            return redirect('/calendar')->with('error', 'Failed to connect Google Calendar: ' . $e->getMessage());
        }
    }

    /**
     * Get Google Calendar events
     */
    public function getCalendarEvents()
    {
        $user = Auth::user();

        if (!$user->google_calendar_connected) {
            return redirect('/auth/google');
        }

        // For now, just return the calendar view without Google API integration
        // This will be enhanced once Google API client is properly installed
        return view('calendar.enhanced');
    }

    /**
     * Sync events from Google Calendar
     */
    public function syncEvents(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->google_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Calendar not connected. Please connect first.'
                ], 400);
            }

            $token = json_decode($user->google_token, true);
            $this->googleCalendarService->setAccessToken($token);
            
            $syncedCount = $this->googleCalendarService->syncEventsToDatabase($user);

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCount} events from Google Calendar",
                'synced_count' => $syncedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Google Calendar events
     */
    public function getGoogleEvents(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->google_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Calendar not connected'
                ], 400);
            }

            $token = json_decode($user->google_token, true);
            $this->googleCalendarService->setAccessToken($token);
            
            $timeMin = $request->get('time_min');
            $timeMax = $request->get('time_max');
            
            $events = $this->googleCalendarService->getEvents('primary', $timeMin, $timeMax);
            
            $formattedEvents = [];
            foreach ($events as $event) {
                $formattedEvents[] = [
                    'id' => $event['id'],
                    'title' => $event['summary'] ?? 'No Title',
                    'description' => $event['description'] ?? '',
                    'start' => $event['start']['dateTime'] ?? $event['start']['date'],
                    'end' => $event['end']['dateTime'] ?? $event['end']['date'],
                    'location' => $event['location'] ?? '',
                    'attendees' => isset($event['attendees']) ? 
                        array_map(function($attendee) {
                            return $attendee['email'];
                        }, $event['attendees']) : [],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedEvents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Google Calendar events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create event in Google Calendar
     */
    public function createGoogleEvent(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->google_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Calendar not connected'
                ], 400);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'description' => 'nullable|string',
                'location' => 'nullable|string',
                'attendees' => 'nullable|array',
                'all_day' => 'boolean',
            ]);

            $token = json_decode($user->google_token, true);
            $this->googleCalendarService->setAccessToken($token);
            
            $eventData = $request->only([
                'title', 'start_date', 'end_date', 'start_time', 
                'end_time', 'description', 'location', 'attendees', 'all_day'
            ]);

            $googleEvent = $this->googleCalendarService->createEvent('primary', $eventData);

            // Also create in local database
            $localEvent = CalendarEvent::create([
                'user_id' => $user->id,
                'title' => $eventData['title'],
                'description' => $eventData['description'] ?? '',
                'start' => $eventData['start_date'] . ' ' . ($eventData['start_time'] ?? '00:00:00'),
                'end' => $eventData['end_date'] . ' ' . ($eventData['end_time'] ?? '00:00:00'),
                'all_day' => $eventData['all_day'] ?? false,
                'location' => $eventData['location'] ?? '',
                'event_type' => 'google_calendar',
                'priority' => 'normal',
                'attendees' => $eventData['attendees'] ?? [],
                'google_event_id' => $googleEvent['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully in Google Calendar',
                'data' => $localEvent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect()
    {
        try {
            $user = Auth::user();
            $user->update([
                'google_token' => null,
                'google_calendar_connected' => false,
            ]);

            return redirect()->route('calendar.index')
                ->with('success', 'Google Calendar disconnected successfully.');

        } catch (\Exception $e) {
            return redirect()->route('calendar.index')
                ->with('error', 'Failed to disconnect Google Calendar: ' . $e->getMessage());
        }
    }
}
