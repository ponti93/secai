<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\CalendarEvent;

class CalendarAiService
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Get AI-powered scheduling suggestions
     */
    public function getSchedulingSuggestions($events, $preferences = [])
    {
        try {
            $prompt = $this->buildSchedulingPrompt($events, $preferences);
            
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->parseSchedulingResponse($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            return ['error' => 'No suggestions generated'];
            
        } catch (\Exception $e) {
            Log::error('Calendar AI Service Error: ' . $e->getMessage());
            return ['error' => 'Failed to get AI suggestions'];
        }
    }

    /**
     * Analyze calendar patterns and provide insights
     */
    public function analyzeCalendarPatterns($events, $timeRange = 'month')
    {
        try {
            $prompt = $this->buildAnalysisPrompt($events, $timeRange);
            
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.5,
                        'maxOutputTokens' => 1500,
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->parseAnalysisResponse($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            return ['error' => 'No analysis generated'];
            
        } catch (\Exception $e) {
            Log::error('Calendar AI Analysis Error: ' . $e->getMessage());
            return ['error' => 'Failed to analyze calendar patterns'];
        }
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectConflicts($newEvent, $existingEvents)
    {
        try {
            $prompt = $this->buildConflictDetectionPrompt($newEvent, $existingEvents);
            
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 500,
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->parseConflictResponse($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            return ['conflicts' => [], 'suggestions' => []];
            
        } catch (\Exception $e) {
            Log::error('Calendar Conflict Detection Error: ' . $e->getMessage());
            return ['conflicts' => [], 'suggestions' => []];
        }
    }

    /**
     * Optimize meeting times based on AI analysis
     */
    public function optimizeMeetingTimes($events, $meetingDuration = 60)
    {
        try {
            $prompt = $this->buildOptimizationPrompt($events, $meetingDuration);
            
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.6,
                        'maxOutputTokens' => 800,
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->parseOptimizationResponse($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            return ['suggestions' => []];
            
        } catch (\Exception $e) {
            Log::error('Calendar Optimization Error: ' . $e->getMessage());
            return ['suggestions' => []];
        }
    }

    /**
     * Generate productivity insights
     */
    public function generateProductivityInsights($events, $userPreferences = [])
    {
        try {
            $prompt = $this->buildProductivityPrompt($events, $userPreferences);
            
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1200,
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->parseProductivityResponse($data['candidates'][0]['content']['parts'][0]['text']);
            }
            
            return ['insights' => []];
            
        } catch (\Exception $e) {
            Log::error('Calendar Productivity Analysis Error: ' . $e->getMessage());
            return ['insights' => []];
        }
    }

    private function buildSchedulingPrompt($events, $preferences)
    {
        $eventsText = $this->formatEventsForPrompt($events);
        $preferencesText = $this->formatPreferencesForPrompt($preferences);
        
        return "As an AI calendar assistant, analyze the following calendar events and provide smart scheduling suggestions:

EVENTS:
{$eventsText}

USER PREFERENCES:
{$preferencesText}

Please provide:
1. Optimal meeting times for the next week
2. Time blocks for focused work
3. Suggestions for reducing meeting fatigue
4. Recommendations for better work-life balance

Format your response as JSON with the following structure:
{
  \"optimal_times\": [\"time1\", \"time2\"],
  \"focus_blocks\": [\"block1\", \"block2\"],
  \"fatigue_reduction\": [\"suggestion1\", \"suggestion2\"],
  \"work_life_balance\": [\"recommendation1\", \"recommendation2\"]
}";
    }

    private function buildAnalysisPrompt($events, $timeRange)
    {
        $eventsText = $this->formatEventsForPrompt($events);
        
        return "Analyze the following calendar events for patterns and provide insights:

EVENTS:
{$eventsText}

TIME RANGE: {$timeRange}

Please analyze and provide:
1. Meeting density patterns
2. Most/least productive hours
3. Time distribution across different activities
4. Recommendations for optimization

Format your response as JSON:
{
  \"meeting_density\": \"analysis\",
  \"productive_hours\": [\"hour1\", \"hour2\"],
  \"time_distribution\": {\"meetings\": \"X%\", \"focus\": \"Y%\"},
  \"recommendations\": [\"rec1\", \"rec2\"]
}";
    }

    private function buildConflictDetectionPrompt($newEvent, $existingEvents)
    {
        $newEventText = json_encode($newEvent);
        $existingEventsText = $this->formatEventsForPrompt($existingEvents);
        
        return "Check for scheduling conflicts between this new event and existing events:

NEW EVENT:
{$newEventText}

EXISTING EVENTS:
{$existingEventsText}

Identify:
1. Direct time conflicts
2. Travel time conflicts
3. Preparation time conflicts
4. Alternative time suggestions

Format as JSON:
{
  \"conflicts\": [\"conflict1\", \"conflict2\"],
  \"suggestions\": [\"suggestion1\", \"suggestion2\"]
}";
    }

    private function buildOptimizationPrompt($events, $meetingDuration)
    {
        $eventsText = $this->formatEventsForPrompt($events);
        
        return "Optimize the following calendar for better productivity:

EVENTS:
{$eventsText}

MEETING DURATION: {$meetingDuration} minutes

Suggest:
1. Better time slots for meetings
2. Buffer time recommendations
3. Batch similar meetings
4. Focus time blocks

Format as JSON:
{
  \"suggestions\": [\"suggestion1\", \"suggestion2\"]
}";
    }

    private function buildProductivityPrompt($events, $userPreferences)
    {
        $eventsText = $this->formatEventsForPrompt($events);
        $preferencesText = $this->formatPreferencesForPrompt($userPreferences);
        
        return "Generate productivity insights based on this calendar:

EVENTS:
{$eventsText}

USER PREFERENCES:
{$preferencesText}

Provide insights on:
1. Energy levels throughout the day
2. Meeting effectiveness
3. Focus time availability
4. Work-life balance

Format as JSON:
{
  \"insights\": [\"insight1\", \"insight2\"]
}";
    }

    private function formatEventsForPrompt($events)
    {
        if (empty($events)) {
            return "No events scheduled";
        }

        $formatted = [];
        foreach ($events as $event) {
            $formatted[] = "- {$event['title']} on " . 
                          date('Y-m-d H:i', strtotime($event['start'])) . 
                          " (Duration: " . $event['duration'] . " min)";
        }

        return implode("\n", $formatted);
    }

    private function formatPreferencesForPrompt($preferences)
    {
        if (empty($preferences)) {
            return "No specific preferences set";
        }

        return json_encode($preferences);
    }

    private function parseSchedulingResponse($response)
    {
        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => 'Failed to parse AI response'];
        }
    }

    private function parseAnalysisResponse($response)
    {
        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => 'Failed to parse analysis response'];
        }
    }

    private function parseConflictResponse($response)
    {
        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['conflicts' => [], 'suggestions' => []];
        }
    }

    private function parseOptimizationResponse($response)
    {
        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['suggestions' => []];
        }
    }

    private function parseProductivityResponse($response)
    {
        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['insights' => []];
        }
    }
}