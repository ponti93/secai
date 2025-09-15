<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalendarAiService;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Auth;

class CalendarAiController extends Controller
{
    protected $calendarAiService;

    public function __construct(CalendarAiService $calendarAiService)
    {
        $this->calendarAiService = $calendarAiService;
    }

    /**
     * Get AI-powered scheduling suggestions
     */
    public function getSchedulingSuggestions(Request $request)
    {
        try {
            $user = Auth::user();
            $events = CalendarEvent::where('user_id', $user->id)
                ->where('start', '>=', now())
                ->orderBy('start')
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })
                ->toArray();

            $preferences = $user->preferences ?? [];
            
            $suggestions = $this->calendarAiService->getSchedulingSuggestions($events, $preferences);

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get scheduling suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze calendar patterns
     */
    public function analyzePatterns(Request $request)
    {
        try {
            $user = Auth::user();
            $timeRange = $request->get('time_range', 'month');
            
            $events = CalendarEvent::where('user_id', $user->id)
                ->when($timeRange === 'week', function ($query) {
                    return $query->whereBetween('start', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($timeRange === 'month', function ($query) {
                    return $query->whereBetween('start', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                ->when($timeRange === 'year', function ($query) {
                    return $query->whereBetween('start', [now()->startOfYear(), now()->endOfYear()]);
                })
                ->orderBy('start')
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })
                ->toArray();

            $analysis = $this->calendarAiService->analyzeCalendarPatterns($events, $timeRange);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze calendar patterns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectConflicts(Request $request)
    {
        try {
            $user = Auth::user();
            $newEvent = $request->validate([
                'title' => 'required|string',
                'start' => 'required|date',
                'end' => 'required|date|after:start',
                'duration' => 'integer|min:1'
            ]);

            $existingEvents = CalendarEvent::where('user_id', $user->id)
                ->where('start', '>=', now())
                ->orderBy('start')
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })
                ->toArray();

            $conflicts = $this->calendarAiService->detectConflicts($newEvent, $existingEvents);

            return response()->json([
                'success' => true,
                'data' => $conflicts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detect conflicts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize meeting times
     */
    public function optimizeMeetingTimes(Request $request)
    {
        try {
            $user = Auth::user();
            $meetingDuration = $request->get('duration', 60);
            
            $events = CalendarEvent::where('user_id', $user->id)
                ->where('start', '>=', now())
                ->where('event_type', 'meeting')
                ->orderBy('start')
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })
                ->toArray();

            $optimization = $this->calendarAiService->optimizeMeetingTimes($events, $meetingDuration);

            return response()->json([
                'success' => true,
                'data' => $optimization
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize meeting times',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate productivity insights
     */
    public function generateProductivityInsights(Request $request)
    {
        try {
            $user = Auth::user();
            $preferences = $user->preferences ?? [];
            
            $events = CalendarEvent::where('user_id', $user->id)
                ->where('start', '>=', now()->subDays(30))
                ->orderBy('start')
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })
                ->toArray();

            $insights = $this->calendarAiService->generateProductivityInsights($events, $preferences);

            return response()->json([
                'success' => true,
                'data' => $insights
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate productivity insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar analytics dashboard data
     */
    public function getAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $timeRange = $request->get('time_range', 'month');
            
            $events = CalendarEvent::where('user_id', $user->id)
                ->when($timeRange === 'week', function ($query) {
                    return $query->whereBetween('start', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($timeRange === 'month', function ($query) {
                    return $query->whereBetween('start', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                ->when($timeRange === 'year', function ($query) {
                    return $query->whereBetween('start', [now()->startOfYear(), now()->endOfYear()]);
                })
                ->get();

            // Basic analytics
            $totalEvents = $events->count();
            $meetings = $events->where('event_type', 'meeting')->count();
            $deadlines = $events->where('event_type', 'deadline')->count();
            $urgentEvents = $events->where('priority', 'urgent')->count();
            
            // Time distribution
            $totalDuration = $events->sum('duration') ?? $events->count() * 60;
            $meetingDuration = $events->where('event_type', 'meeting')->sum('duration') ?? 0;
            
            // AI insights
            $aiInsights = $this->calendarAiService->generateProductivityInsights(
                $events->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start,
                        'end' => $event->end,
                        'duration' => $event->duration ?? 60,
                        'type' => $event->event_type,
                        'priority' => $event->priority
                    ];
                })->toArray(),
                $user->preferences ?? []
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'total_events' => $totalEvents,
                    'meetings' => $meetings,
                    'deadlines' => $deadlines,
                    'urgent_events' => $urgentEvents,
                    'total_duration' => $totalDuration,
                    'meeting_duration' => $meetingDuration,
                    'meeting_percentage' => $totalDuration > 0 ? round(($meetingDuration / $totalDuration) * 100, 2) : 0,
                    'ai_insights' => $aiInsights['insights'] ?? []
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
