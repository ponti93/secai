<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIEmailService
{
    protected $openaiApiKey;
    protected $geminiApiKey;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Categorize email content using AI
     */
    public function categorizeEmail(string $subject, string $content): array
    {
        try {
            $prompt = "Analyze this email and categorize it. Return JSON with category, priority, and suggested action.
            
            Email Subject: {$subject}
            Email Content: {$content}
            
            Categories: urgent, important, informational, promotional, spam, personal, work, meeting, invoice, complaint, support
            Priority: high, medium, low
            Suggested Action: reply, forward, archive, delete, schedule_meeting, create_task
            
            Return only valid JSON format.";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                return json_decode($response, true) ?? [
                    'category' => 'informational',
                    'priority' => 'medium',
                    'suggested_action' => 'reply'
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI Email categorization failed: ' . $e->getMessage());
        }

        return [
            'category' => 'informational',
            'priority' => 'medium',
            'suggested_action' => 'reply'
        ];
    }

    /**
     * Generate smart reply suggestions
     */
    public function generateReplySuggestions(string $originalEmail, string $context = ''): array
    {
        try {
            $prompt = "Generate 3 professional email reply suggestions for this email. Make them concise and appropriate.
            
            Original Email: {$originalEmail}
            Context: {$context}
            
            Return ONLY a valid JSON array in this exact format:
            [
                {
                    \"subject\": \"Reply Subject 1\",
                    \"content\": \"Reply content 1\"
                },
                {
                    \"subject\": \"Reply Subject 2\", 
                    \"content\": \"Reply content 2\"
                },
                {
                    \"subject\": \"Reply Subject 3\",
                    \"content\": \"Reply content 3\"
                }
            ]
            
            Do not include any other text, just the JSON array.";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                // Clean the response to extract JSON
                $cleanedResponse = trim($response);
                
                // Try to find JSON array in the response
                if (preg_match('/\[.*\]/s', $cleanedResponse, $matches)) {
                    $jsonString = $matches[0];
                } else {
                    $jsonString = $cleanedResponse;
                }
                
                $data = json_decode($jsonString, true);
                
                if (is_array($data)) {
                    return $data;
                }
                
                // Fallback: try to parse as suggestions object
                $fallbackData = json_decode($cleanedResponse, true);
                if (isset($fallbackData['suggestions']) && is_array($fallbackData['suggestions'])) {
                    return $fallbackData['suggestions'];
                }
            }
        } catch (\Exception $e) {
            Log::error('AI Reply generation failed: ' . $e->getMessage());
        }

        // Return fallback suggestions if AI fails
        return [
            [
                'subject' => 'Thank you for your email',
                'content' => 'Thank you for your email. I have received your message and will get back to you soon.'
            ],
            [
                'subject' => 'Re: ' . substr($originalEmail, 0, 50),
                'content' => 'Thank you for reaching out. I appreciate your message and will respond in detail shortly.'
            ],
            [
                'subject' => 'Quick response',
                'content' => 'Thanks for your email. I\'m currently busy but will provide a detailed response soon.'
            ]
        ];
    }

    /**
     * Analyze email sentiment
     */
    public function analyzeSentiment(string $content): array
    {
        try {
            $prompt = "Analyze the sentiment of this email content. Return JSON with sentiment, confidence, and emotional indicators.
            
            Email Content: {$content}
            
            Return JSON with: sentiment (positive, negative, neutral), confidence (0-1), emotions (array of detected emotions)";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                return json_decode($response, true) ?? [
                    'sentiment' => 'neutral',
                    'confidence' => 0.5,
                    'emotions' => []
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI Sentiment analysis failed: ' . $e->getMessage());
        }

        return [
            'sentiment' => 'neutral',
            'confidence' => 0.5,
            'emotions' => []
        ];
    }

    /**
     * Extract key information from email
     */
    public function extractKeyInfo(string $content): array
    {
        try {
            $prompt = "Extract key information from this email. Return JSON with extracted data.
            
            Email Content: {$content}
            
            Extract: dates, times, names, phone_numbers, email_addresses, locations, amounts, deadlines, action_items
            Return as JSON object with arrays for each type.";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                return json_decode($response, true) ?? [];
            }
        } catch (\Exception $e) {
            Log::error('AI Key info extraction failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Generate email summary
     */
    public function generateSummary(string $content): string
    {
        try {
            $prompt = "Summarize this email in 2-3 sentences, highlighting the main points and any action items.
            
            Email Content: {$content}";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                return trim($response);
            }
        } catch (\Exception $e) {
            Log::error('AI Email summary failed: ' . $e->getMessage());
        }

        return 'Unable to generate summary.';
    }

    /**
     * Call Gemini AI API
     */
    public function callGemini(string $prompt): ?string
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
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Call OpenAI API (fallback)
     */
    public function callOpenAI(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API call failed: ' . $e->getMessage());
        }

        return null;
    }

}
