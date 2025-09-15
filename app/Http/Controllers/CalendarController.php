<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display a listing of calendar events
     */
    public function index()
    {
        $events = CalendarEvent::where('user_id', Auth::id())
            ->orderBy('start_time', 'asc')
            ->get();
        
        return view('calendar.index', compact('events'));
    }

    /**
     * Show the form for creating a new calendar event
     */
    public function create()
    {
        return view('calendar.create');
    }

    /**
     * Store a newly created calendar event
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|string',
            'all_day' => 'nullable|boolean',
        ]);

        CalendarEvent::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'attendees' => $request->attendees ? json_encode(explode(',', $request->attendees)) : null,
            'status' => 'confirmed',
            'all_day' => $request->has('all_day'),
        ]);

        return redirect()->route('calendar.index')->with('success', 'Calendar event created successfully!');
    }

    /**
     * Display the specified calendar event
     */
    public function show(CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('calendar.show', compact('event'));
    }

    /**
     * Show the form for editing the specified calendar event
     */
    public function edit(CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('calendar.edit', compact('event'));
    }

    /**
     * Update the specified calendar event
     */
    public function update(Request $request, CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|string',
            'all_day' => 'nullable|boolean',
        ]);

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'attendees' => $request->attendees ? json_encode(explode(',', $request->attendees)) : null,
            'all_day' => $request->has('all_day'),
        ];

        $event->update($updateData);

        return redirect()->route('calendar.index')->with('success', 'Calendar event updated successfully!');
    }

    /**
     * Remove the specified calendar event
     */
    public function destroy(CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $event->delete();
        return redirect()->route('calendar.index')->with('success', 'Calendar event deleted successfully!');
    }

    /**
     * Suggest optimal meeting times using AI
     */
    public function suggestMeetingTimes(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer|min:15|max:480',
            'preferred_times' => 'nullable|array',
            'days_of_week' => 'nullable|array',
            'timezone' => 'nullable|string',
            'meeting_type' => 'nullable|string'
        ]);

        try {
            $aiService = new \App\Services\AICalendarService();
            
            $preferences = [
                'duration' => $request->duration,
                'preferred_times' => $request->preferred_times ?? ['morning', 'afternoon'],
                'days_of_week' => $request->days_of_week ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'timezone' => $request->timezone ?? 'UTC',
                'meeting_type' => $request->meeting_type ?? 'general',
                'start_hour' => $request->start_hour ?? 9,
                'end_hour' => $request->end_hour ?? 17
            ];
            
            $result = $aiService->suggestMeetingTimes($preferences, Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error suggesting meeting times: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect scheduling conflicts using AI
     */
    public function detectConflicts(CalendarEvent $event)
    {
        try {
            $aiService = new \App\Services\AICalendarService();
            $result = $aiService->detectConflicts($event, Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error detecting conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate follow-up suggestions using AI
     */
    public function generateFollowUpSuggestions(CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $aiService = new \App\Services\AICalendarService();
            $result = $aiService->generateFollowUpSuggestions($event);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating follow-up suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize schedule using AI
     */
    public function optimizeSchedule(Request $request)
    {
        $request->validate([
            'work_hours' => 'nullable|string',
            'break_preferences' => 'nullable|string',
            'focus_time' => 'nullable|string'
        ]);

        try {
            $aiService = new \App\Services\AICalendarService();
            
            $preferences = [
                'work_hours' => $request->work_hours ?? '9 AM - 5 PM',
                'break_preferences' => $request->break_preferences ?? '15 min between meetings',
                'focus_time' => $request->focus_time ?? '2-3 hours daily'
            ];
            
            $result = $aiService->optimizeSchedule(Auth::id(), $preferences);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing schedule: ' . $e->getMessage()
            ], 500);
        }
    }
}
