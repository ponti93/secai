<?php

namespace App\Services;

use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AICalendarService
{
    protected $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Suggest optimal meeting times based on availability and preferences
     */
    public function suggestMeetingTimes(array $preferences, int $userId): array
    {
        try {
            // Get user's existing events
            $existingEvents = CalendarEvent::where('user_id', $userId)
                ->where('start_time', '>=', now())
                ->orderBy('start_time', 'asc')
                ->get();

            // Get available time slots
            $availableSlots = $this->findAvailableSlots($existingEvents, $preferences);

            // Use AI to suggest best times
            $prompt = "Suggest the best meeting times based on these preferences and availability:
            
            Preferences:
            - Duration: {$preferences['duration']} minutes
            - Preferred times: " . implode(', ', $preferences['preferred_times'] ?? []) . "
            - Days of week: " . implode(', ', $preferences['days_of_week'] ?? []) . "
            - Time zone: {$preferences['timezone'] ?? 'UTC'}
            - Meeting type: {$preferences['meeting_type'] ?? 'general'}
            
            Available time slots: " . json_encode($availableSlots) . "
            
            Return JSON with:
            - suggestions: Array of 3-5 best time suggestions with reasons
            - conflicts: Array of any potential conflicts
            - recommendations: Array of general scheduling tips";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'suggestions' => $data['suggestions'] ?? [],
                        'conflicts' => $data['conflicts'] ?? [],
                        'recommendations' => $data['recommendations'] ?? [],
                        'available_slots' => $availableSlots
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Meeting time suggestion failed: ' . $e->getMessage());
        }

        // Fallback suggestions
        return [
            'success' => false,
            'suggestions' => $this->generateFallbackSuggestions($preferences),
            'conflicts' => [],
            'recommendations' => ['Consider scheduling during business hours', 'Allow buffer time between meetings'],
            'available_slots' => []
        ];
    }

    /**
     * Detect scheduling conflicts and overlaps
     */
    public function detectConflicts(CalendarEvent $newEvent, int $userId): array
    {
        try {
            // Get overlapping events
            $conflictingEvents = CalendarEvent::where('user_id', $userId)
                ->where('id', '!=', $newEvent->id)
                ->where(function ($query) use ($newEvent) {
                    $query->whereBetween('start_time', [$newEvent->start_time, $newEvent->end_time])
                          ->orWhereBetween('end_time', [$newEvent->start_time, $newEvent->end_time])
                          ->orWhere(function ($q) use ($newEvent) {
                              $q->where('start_time', '<=', $newEvent->start_time)
                                ->where('end_time', '>=', $newEvent->end_time);
                          });
                })
                ->get();

            if ($conflictingEvents->isEmpty()) {
                return [
                    'success' => true,
                    'has_conflicts' => false,
                    'conflicts' => [],
                    'suggestions' => []
                ];
            }

            // Use AI to analyze conflicts
            $conflictData = $conflictingEvents->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start_time' => $event->start_time->format('Y-m-d H:i'),
                    'end_time' => $event->end_time->format('Y-m-d H:i'),
                    'type' => $event->type ?? 'meeting'
                ];
            })->toArray();

            $prompt = "Analyze these scheduling conflicts and provide suggestions:
            
            New Event:
            - Title: {$newEvent->title}
            - Start: {$newEvent->start_time->format('Y-m-d H:i')}
            - End: {$newEvent->end_time->format('Y-m-d H:i')}
            - Type: {$newEvent->type ?? 'meeting'}
            
            Conflicting Events: " . json_encode($conflictData) . "
            
            Return JSON with:
            - conflict_analysis: Analysis of the conflicts
            - severity: low/medium/high
            - suggestions: Array of resolution suggestions
            - alternative_times: Array of suggested alternative times";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'has_conflicts' => true,
                        'conflicts' => $conflictingEvents->toArray(),
                        'analysis' => $data['conflict_analysis'] ?? 'Conflicts detected',
                        'severity' => $data['severity'] ?? 'medium',
                        'suggestions' => $data['suggestions'] ?? [],
                        'alternative_times' => $data['alternative_times'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Conflict detection failed: ' . $e->getMessage());
        }

        // Fallback conflict detection
        return [
            'success' => true,
            'has_conflicts' => true,
            'conflicts' => $conflictingEvents->toArray(),
            'analysis' => 'Conflicts detected with existing events',
            'severity' => 'medium',
            'suggestions' => ['Consider rescheduling one of the events', 'Check if any events can be shortened'],
            'alternative_times' => []
        ];
    }

    /**
     * Generate follow-up reminders and suggestions
     */
    public function generateFollowUpSuggestions(CalendarEvent $event): array
    {
        try {
            $prompt = "Generate follow-up suggestions for this meeting:
            
            Meeting Details:
            - Title: {$event->title}
            - Start: {$event->start_time->format('Y-m-d H:i')}
            - End: {$event->end_time->format('Y-m-d H:i')}
            - Type: {$event->type ?? 'meeting'}
            - Description: " . ($event->description ?? 'No description') . "
            
            Return JSON with:
            - pre_meeting: Array of pre-meeting preparation suggestions
            - post_meeting: Array of post-meeting follow-up actions
            - reminders: Array of suggested reminder times
            - action_items: Array of potential action items
            - next_steps: Array of suggested next steps";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'pre_meeting' => $data['pre_meeting'] ?? [],
                        'post_meeting' => $data['post_meeting'] ?? [],
                        'reminders' => $data['reminders'] ?? [],
                        'action_items' => $data['action_items'] ?? [],
                        'next_steps' => $data['next_steps'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Follow-up generation failed: ' . $e->getMessage());
        }

        // Fallback suggestions
        return [
            'success' => false,
            'pre_meeting' => [
                'Review agenda and materials',
                'Prepare questions and talking points',
                'Test any technical equipment'
            ],
            'post_meeting' => [
                'Send meeting summary to participants',
                'Follow up on action items',
                'Schedule any necessary follow-up meetings'
            ],
            'reminders' => [
                '1 hour before',
                '1 day before'
            ],
            'action_items' => [],
            'next_steps' => []
        ];
    }

    /**
     * Optimize meeting schedule for better productivity
     */
    public function optimizeSchedule(int $userId, array $preferences = []): array
    {
        try {
            // Get user's events for the next 30 days
            $events = CalendarEvent::where('user_id', $userId)
                ->where('start_time', '>=', now())
                ->where('start_time', '<=', now()->addDays(30))
                ->orderBy('start_time', 'asc')
                ->get();

            $scheduleData = $events->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start_time' => $event->start_time->format('Y-m-d H:i'),
                    'end_time' => $event->end_time->format('Y-m-d H:i'),
                    'type' => $event->type ?? 'meeting',
                    'duration' => $event->start_time->diffInMinutes($event->end_time)
                ];
            })->toArray();

            $prompt = "Analyze this schedule and provide optimization suggestions:
            
            Current Schedule: " . json_encode($scheduleData) . "
            
            Preferences:
            - Work hours: " . ($preferences['work_hours'] ?? '9 AM - 5 PM') . "
            - Break preferences: " . ($preferences['break_preferences'] ?? '15 min between meetings') . "
            - Focus time: " . ($preferences['focus_time'] ?? '2-3 hours daily') . "
            
            Return JSON with:
            - analysis: Overall schedule analysis
            - issues: Array of identified issues
            - suggestions: Array of optimization suggestions
            - ideal_schedule: Suggested improved schedule
            - productivity_tips: Array of productivity tips";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'analysis' => $data['analysis'] ?? 'Schedule analysis completed',
                        'issues' => $data['issues'] ?? [],
                        'suggestions' => $data['suggestions'] ?? [],
                        'ideal_schedule' => $data['ideal_schedule'] ?? [],
                        'productivity_tips' => $data['productivity_tips'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Schedule optimization failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'analysis' => 'Unable to analyze schedule',
            'issues' => [],
            'suggestions' => ['Consider adding buffer time between meetings', 'Schedule focus time blocks'],
            'ideal_schedule' => [],
            'productivity_tips' => []
        ];
    }

    /**
     * Find available time slots
     */
    private function findAvailableSlots($existingEvents, array $preferences): array
    {
        $duration = $preferences['duration'] ?? 60;
        $days = $preferences['days_of_week'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $startHour = $preferences['start_hour'] ?? 9;
        $endHour = $preferences['end_hour'] ?? 17;
        
        $slots = [];
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(14)->endOfDay();
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->format('l'));
            
            if (in_array($dayName, $days)) {
                $daySlots = $this->findDaySlots($date, $existingEvents, $duration, $startHour, $endHour);
                $slots = array_merge($slots, $daySlots);
            }
        }
        
        return array_slice($slots, 0, 20); // Limit to 20 slots
    }

    /**
     * Find available slots for a specific day
     */
    private function findDaySlots($date, $existingEvents, $duration, $startHour, $endHour): array
    {
        $slots = [];
        $startTime = $date->copy()->setHour($startHour)->setMinute(0);
        $endTime = $date->copy()->setHour($endHour)->setMinute(0);
        
        // Get events for this day
        $dayEvents = $existingEvents->filter(function ($event) use ($date) {
            return $event->start_time->isSameDay($date);
        })->sortBy('start_time');
        
        $currentTime = $startTime->copy();
        
        foreach ($dayEvents as $event) {
            // Check if there's a gap before this event
            if ($currentTime->addMinutes($duration)->lte($event->start_time)) {
                $slots[] = [
                    'start' => $currentTime->format('Y-m-d H:i'),
                    'end' => $currentTime->addMinutes($duration)->format('Y-m-d H:i'),
                    'duration' => $duration,
                    'day' => $date->format('l'),
                    'date' => $date->format('Y-m-d')
                ];
            }
            
            $currentTime = $event->end_time;
        }
        
        // Check for slot after last event
        if ($currentTime->addMinutes($duration)->lte($endTime)) {
            $slots[] = [
                'start' => $currentTime->format('Y-m-d H:i'),
                'end' => $currentTime->addMinutes($duration)->format('Y-m-d H:i'),
                'duration' => $duration,
                'day' => $date->format('l'),
                'date' => $date->format('Y-m-d')
            ];
        }
        
        return $slots;
    }

    /**
     * Generate fallback suggestions
     */
    private function generateFallbackSuggestions(array $preferences): array
    {
        $suggestions = [];
        $duration = $preferences['duration'] ?? 60;
        
        // Generate suggestions for next few days
        for ($i = 1; $i <= 5; $i++) {
            $date = now()->addDays($i);
            $suggestions[] = [
                'start' => $date->setHour(10)->setMinute(0)->format('Y-m-d H:i'),
                'end' => $date->setHour(10)->addMinutes($duration)->format('Y-m-d H:i'),
                'reason' => 'Morning slot - good for focused work',
                'confidence' => 0.8
            ];
            
            $suggestions[] = [
                'start' => $date->setHour(14)->setMinute(0)->format('Y-m-d H:i'),
                'end' => $date->setHour(14)->addMinutes($duration)->format('Y-m-d H:i'),
                'reason' => 'Afternoon slot - good for collaborative meetings',
                'confidence' => 0.7
            ];
        }
        
        return array_slice($suggestions, 0, 5);
    }

    /**
     * Call Gemini API
     */
    private function callGemini(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiApiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 2000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    // Clean up UTF-8 encoding issues
                    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
                    $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
                    return $text;
                }
            } elseif ($response->status() === 429) {
                // Quota exceeded - return fallback
                Log::warning('Gemini API quota exceeded');
                return $this->getFallbackResponse($prompt);
            } else {
                // Other API errors
                $errorData = $response->json();
                Log::error('Gemini API error: ' . ($errorData['error']['message'] ?? 'Unknown error'));
                return $this->getFallbackResponse($prompt);
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get fallback response when API fails
     */
    private function getFallbackResponse(string $prompt): string
    {
        // Simple fallback responses based on prompt content
        if (strpos($prompt, 'suggest') !== false) {
            return '{"suggestions": [{"start": "2025-09-16 10:00", "end": "2025-09-16 11:00", "reason": "Morning slot available", "confidence": 0.8}], "conflicts": [], "recommendations": ["Consider morning slots for better productivity"]}';
        } elseif (strpos($prompt, 'conflict') !== false) {
            return '{"has_conflicts": false, "conflicts": [], "analysis": "No conflicts detected", "severity": "low", "suggestions": [], "alternative_times": []}';
        } elseif (strpos($prompt, 'follow-up') !== false) {
            return '{"pre_meeting": ["Review agenda", "Prepare materials"], "post_meeting": ["Send summary", "Follow up on action items"], "reminders": ["1 hour before"], "action_items": [], "next_steps": []}';
        } elseif (strpos($prompt, 'optimize') !== false) {
            return '{"analysis": "Schedule optimization temporarily unavailable", "issues": [], "suggestions": ["Consider adding buffer time between meetings"], "ideal_schedule": [], "productivity_tips": ["Take regular breaks"]}';
        }
        
        return '{"message": "AI analysis temporarily unavailable due to quota limits. Please try again later."}';
    }
}
