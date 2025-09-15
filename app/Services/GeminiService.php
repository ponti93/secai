<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $client;
    private $apiKey;
    private $model;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/'
        ]);
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-1.5-flash'));
    }

    /**
     * Generate email reply using Gemini
     */
    public function generateEmailReply(string $emailContent, string $context = ''): array
    {
        $prompt = "You are an AI assistant that helps draft professional email replies. 
        Generate a polite, professional, and helpful reply to the original email. 
        Keep it concise but comprehensive.
        
        Original Email: {$emailContent}
        Context: {$context}
        
        Please draft a professional reply to this email.";

        return $this->makeRequestWithOptions('email-reply', $prompt, [
            'temperature' => 0.7,
            'max_tokens' => 500
        ]);
    }

    /**
     * Generate document content using Gemini
     */
    public function generateDocument(string $prompt, string $documentType = 'general', string $tone = 'professional'): array
    {
        $systemPrompt = $this->getDocumentSystemPrompt($documentType, $tone);
        $fullPrompt = "{$systemPrompt}\n\nUser Request: {$prompt}";

        return $this->makeRequestWithOptions('document-generation', $fullPrompt, [
            'temperature' => 0.7,
            'max_tokens' => 2000
        ]);
    }

    /**
     * Summarize meeting transcript using Gemini
     */
    public function summarizeTranscript(string $transcript, string $meetingType = 'general'): array
    {
        $prompt = "You are an AI assistant that creates professional meeting summaries. 
        Extract key points, decisions, and action items from meeting transcripts.
        
        Meeting Type: {$meetingType}
        Transcript: {$transcript}
        
        Please create a comprehensive summary of this meeting.";

        return $this->makeRequest('meeting-summary', $prompt);
    }

    /**
     * Extract action items from transcript using Gemini
     */
    public function extractActionItems(string $transcript): array
    {
        $prompt = "You are an AI assistant that extracts action items from meeting transcripts. 
        Identify all action items, who is responsible, and when they are due.
        
        Meeting Transcript: {$transcript}
        
        Please extract all action items from this meeting in JSON format.";

        return $this->makeRequest('action-items', $prompt);
    }

    /**
     * Generate meeting agenda using Gemini
     */
    public function generateAgenda(string $meetingTitle, string $meetingType, array $participants, int $duration, array $topics = []): array
    {
        $prompt = "You are an AI assistant that creates professional meeting agendas.
        
        Meeting Title: {$meetingTitle}
        Meeting Type: {$meetingType}
        Participants: " . implode(', ', $participants) . "
        Duration: {$duration} minutes
        Topics: " . implode(', ', $topics) . "
        
        Please create a detailed agenda for this meeting.";

        return $this->makeRequest('meeting-agenda', $prompt);
    }

    /**
     * Categorize expense using Gemini
     */
    public function categorizeExpense(string $description, float $amount, string $context = ''): array
    {
        $prompt = "You are an AI assistant that categorizes business expenses. 
        Analyze the expense and provide:
        1. Category (from: Travel, Meals, Office Supplies, Software, Marketing, Utilities, Professional Services, Equipment, Training, Other)
        2. Brief reasoning for the categorization
        3. Suggested subcategory if applicable
        
        Expense Description: {$description}
        Amount: \${$amount}
        Context: {$context}
        
        Please categorize this expense:";

        return $this->makeRequestWithOptions('expense-categorization', $prompt, [
            'temperature' => 0.3,
            'max_tokens' => 300
        ]);
    }

    /**
     * Generate inventory reorder suggestions using Gemini
     */
    public function generateReorderSuggestions(array $inventoryData): array
    {
        $prompt = "You are an AI assistant that analyzes inventory data and provides insights and recommendations for inventory management.
        
        Inventory Data: " . json_encode($inventoryData, JSON_PRETTY_PRINT) . "
        
        Please analyze this inventory data and provide reorder suggestions.";

        return $this->makeRequest('inventory-suggestions', $prompt);
    }

    /**
     * Generate email template using Gemini
     */
    public function generateEmailTemplate(string $templateType, array $context = []): array
    {
        $prompt = "You are an AI assistant that creates professional email templates.
        
        Template Type: {$templateType}
        Context: " . json_encode($context, JSON_PRETTY_PRINT) . "
        
        Please create a professional email template for this scenario.";

        return $this->makeRequest('email-template', $prompt);
    }

    /**
     * Analyze content using Gemini
     */
    public function analyzeContent(string $content, string $type): array
    {
        $prompt = "You are an AI assistant that analyzes content and provides insights.
        
        Content Type: {$type}
        Content: {$content}
        
        Please analyze this content and provide relevant insights.";

        return $this->makeRequest('content-analysis', $prompt);
    }

    /**
     * Make request to Gemini API
     */
    private function makeRequest(string $feature, string $prompt): array
    {
        try {
            $startTime = microtime(true);
            
            $response = $this->client->post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => $this->apiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            ]);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $content = $data['candidates'][0]['content']['parts'][0]['text'];
                // Clean up UTF-8 encoding issues
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
                $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content); // Remove control characters
                
                $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? 0;
                
                // Estimate cost (this is approximate)
                $cost = $this->calculateCost($tokensUsed);

                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $tokensUsed,
                    'cost' => $cost,
                    'response_time_ms' => round($responseTime),
                    'model' => $this->model,
                ];
            }

            return [
                'success' => false,
                'error' => 'No content generated',
                'response_time_ms' => round($responseTime),
            ];

        } catch (RequestException $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
                'response_time_ms' => 0,
            ];
        }
    }

    /**
     * Calculate estimated cost based on tokens used
     */
    private function calculateCost(int $tokens): float
    {
        // Approximate cost calculation for Gemini 1.5 Flash
        // Input: $0.075 per 1M tokens, Output: $0.30 per 1M tokens
        // This is a simplified calculation
        return ($tokens / 1000000) * 0.20; // Average of input/output costs
    }

    /**
     * Get system prompt for document generation
     */
    private function getDocumentSystemPrompt(string $documentType, string $tone): string
    {
        $prompts = [
            'report' => 'You are an AI assistant that creates professional business reports. Write clear, well-structured content with proper formatting.',
            'proposal' => 'You are an AI assistant that creates compelling business proposals. Focus on benefits, clear structure, and persuasive language.',
            'memo' => 'You are an AI assistant that creates professional memos. Use clear, concise language and proper memo format.',
            'letter' => 'You are an AI assistant that creates professional business letters. Use formal tone and proper letter format.',
            'agenda' => 'You are an AI assistant that creates meeting agendas. Focus on clear structure, time allocation, and actionable items.',
            'minutes' => 'You are an AI assistant that creates meeting minutes. Capture key decisions, action items, and important discussions.',
            'general' => 'You are an AI assistant that creates professional documents. Write clear, well-structured content appropriate for business use.'
        ];

        $basePrompt = $prompts[$documentType] ?? $prompts['general'];
        
        $toneInstructions = [
            'professional' => 'Use a professional, formal tone throughout.',
            'casual' => 'Use a friendly, casual tone while maintaining professionalism.',
            'formal' => 'Use a very formal, official tone.',
            'friendly' => 'Use a warm, approachable tone while being professional.'
        ];

        $toneInstruction = $toneInstructions[$tone] ?? $toneInstructions['professional'];

        return "{$basePrompt} {$toneInstruction}";
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Hello, this is a test message. Please respond with "API connection successful."'
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 50,
                    ]
                ],
                'query' => [
                    'key' => $this->apiKey
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'message' => 'Gemini API connection successful',
                    'model' => $this->model,
                    'response' => $result['candidates'][0]['content']['parts'][0]['text']
                ];
            }

            return [
                'success' => false,
                'message' => 'Gemini API connection failed',
                'error' => 'No response from API',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gemini API connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze image using Gemini API
     */
    public function analyzeImage($imageData, $mimeType, $prompt)
    {
        try {
            $response = $this->client->post("models/{$this->model}:generateContent", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageData
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'topK' => 32,
                        'topP' => 1,
                        'maxOutputTokens' => 1024,
                    ]
                ],
                'query' => [
                    'key' => $this->apiKey
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                // Clean up UTF-8 encoding issues
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
                $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text); // Remove control characters
                
                return [
                    'success' => true,
                    'response' => $text
                ];
            }

            return [
                'success' => false,
                'error' => 'No response from Gemini API'
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Image Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transcribe audio using Gemini API
     */
    public function transcribeAudio($audioData, $mimeType, $language = 'en'): array
    {
        try {
            $prompt = "Transcribe the following audio file. Provide a clean, accurate transcription with proper punctuation and formatting. If the audio contains multiple speakers, indicate speaker changes with 'Speaker 1:', 'Speaker 2:', etc. Language: {$language}";

            $response = $this->client->post("models/{$this->model}:generateContent", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $audioData
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'topK' => 32,
                        'topP' => 1,
                        'maxOutputTokens' => 4096,
                    ]
                ],
                'query' => [
                    'key' => $this->apiKey
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $transcript = $result['candidates'][0]['content']['parts'][0]['text'];
                $tokensUsed = $result['usageMetadata']['totalTokenCount'] ?? 0;
                $cost = $this->calculateCost($tokensUsed);

                return [
                    'success' => true,
                    'transcript' => $transcript,
                    'tokens_used' => $tokensUsed,
                    'cost' => $cost,
                    'language' => $language,
                    'provider' => 'gemini',
                    'model' => $this->model,
                ];
            }

            return [
                'success' => false,
                'error' => 'No transcription generated'
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Audio Transcription Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Perform OCR on image using Gemini API
     */
    public function performOCR($imageData, $mimeType, $language = 'en'): array
    {
        try {
            $prompt = "Extract all text from this image using OCR. Return the text exactly as it appears, maintaining formatting and structure. If there are multiple columns or sections, preserve the layout. Language: {$language}";

            $response = $this->client->post("models/{$this->model}:generateContent", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageData
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'topK' => 32,
                        'topP' => 1,
                        'maxOutputTokens' => 2048,
                    ]
                ],
                'query' => [
                    'key' => $this->apiKey
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $extractedText = $result['candidates'][0]['content']['parts'][0]['text'];
                $tokensUsed = $result['usageMetadata']['totalTokenCount'] ?? 0;
                $cost = $this->calculateCost($tokensUsed);

                return [
                    'success' => true,
                    'text' => $extractedText,
                    'tokens_used' => $tokensUsed,
                    'cost' => $cost,
                    'language' => $language,
                ];
            }

            return [
                'success' => false,
                'error' => 'No text extracted from image'
            ];

        } catch (\Exception $e) {
            Log::error('Gemini OCR Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enhanced text generation with multiple options
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $defaultOptions = [
            'tone' => 'professional',
            'length' => 'medium',
            'style' => 'formal',
            'include_bullets' => false,
            'include_numbers' => false,
            'max_tokens' => 2048,
            'temperature' => 0.7,
        ];

        $options = array_merge($defaultOptions, $options);

        $systemPrompt = $this->buildTextGenerationPrompt($options);
        $fullPrompt = "{$systemPrompt}\n\nUser Request: {$prompt}";

        return $this->makeRequestWithOptions('text-generation', $fullPrompt, $options);
    }

    /**
     * Generate meeting notes from transcript
     */
    public function generateMeetingNotes(string $transcript, array $options = []): array
    {
        $defaultOptions = [
            'include_action_items' => true,
            'include_decisions' => true,
            'include_key_points' => true,
            'format' => 'structured',
        ];

        $options = array_merge($defaultOptions, $options);

        $prompt = "Create comprehensive meeting notes from this transcript. ";
        
        if ($options['include_action_items']) {
            $prompt .= "Include a clear action items section. ";
        }
        if ($options['include_decisions']) {
            $prompt .= "Highlight all decisions made. ";
        }
        if ($options['include_key_points']) {
            $prompt .= "Summarize key discussion points. ";
        }

        $prompt .= "Format: {$options['format']}\n\nTranscript: {$transcript}";

        return $this->makeRequest('meeting-notes', $prompt);
    }

    /**
     * Generate document from audio transcription
     */
    public function generateDocumentFromAudio(string $transcript, string $documentType = 'meeting-minutes', array $options = []): array
    {
        $prompt = "Convert this meeting transcript into a {$documentType}. ";
        
        if ($documentType === 'meeting-minutes') {
            $prompt .= "Include: meeting details, attendees, agenda items, discussions, decisions, and action items.";
        } elseif ($documentType === 'report') {
            $prompt .= "Structure as a professional report with executive summary, main points, and recommendations.";
        } elseif ($documentType === 'summary') {
            $prompt .= "Create a concise summary highlighting the most important points.";
        }

        $prompt .= "\n\nTranscript: {$transcript}";

        return $this->makeRequest('audio-to-document', $prompt);
    }

    /**
     * Build text generation prompt based on options
     */
    private function buildTextGenerationPrompt(array $options): string
    {
        $toneInstructions = [
            'professional' => 'Use a professional, business-appropriate tone.',
            'casual' => 'Use a friendly, conversational tone.',
            'formal' => 'Use a very formal, official tone.',
            'friendly' => 'Use a warm, approachable tone.',
            'technical' => 'Use precise, technical language.',
        ];

        $lengthInstructions = [
            'short' => 'Keep it concise and to the point (1-2 paragraphs).',
            'medium' => 'Provide a moderate amount of detail (3-5 paragraphs).',
            'long' => 'Be comprehensive and detailed (6+ paragraphs).',
        ];

        $styleInstructions = [
            'formal' => 'Use formal language and structure.',
            'informal' => 'Use casual, relaxed language.',
            'academic' => 'Use scholarly, research-oriented language.',
            'business' => 'Use professional business language.',
        ];

        $prompt = "You are an AI assistant that generates high-quality text content. ";
        $prompt .= $toneInstructions[$options['tone']] ?? $toneInstructions['professional'];
        $prompt .= " " . $lengthInstructions[$options['length']] ?? $lengthInstructions['medium'];
        $prompt .= " " . $styleInstructions[$options['style']] ?? $styleInstructions['formal'];

        if ($options['include_bullets']) {
            $prompt .= " Use bullet points where appropriate.";
        }
        if ($options['include_numbers']) {
            $prompt .= " Use numbered lists for sequential information.";
        }

        return $prompt;
    }

    /**
     * Make request with custom options
     */
    private function makeRequestWithOptions(string $feature, string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);
            
            $response = $this->client->post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => $this->apiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => $options['max_tokens'] ?? 2048,
                    ]
                ]
            ]);

            $responseTime = (microtime(true) - $startTime) * 1000;
            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $content = $data['candidates'][0]['content']['parts'][0]['text'];
                $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? 0;
                $cost = $this->calculateCost($tokensUsed);

                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $tokensUsed,
                    'cost' => $cost,
                    'response_time_ms' => round($responseTime),
                    'model' => $this->model,
                    'options' => $options,
                ];
            }

            return [
                'success' => false,
                'error' => 'No content generated',
                'response_time_ms' => round($responseTime),
            ];

        } catch (RequestException $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
                'response_time_ms' => 0,
            ];
        }
    }

    /**
     * Get fallback response when API fails
     */
    private function getFallbackResponse(string $prompt, string $feature = 'general'): string
    {
        $fallbacks = [
            'email-reply' => 'Thank you for your email. I will review your message and get back to you soon.',
            'document' => 'This is a sample document generated as a fallback. Please try again later for AI-generated content.',
            'summarize' => 'Document summary: This document contains important information that requires review.',
            'keywords' => 'Keywords: important, document, information, review',
            'analyze' => 'Document analysis: This document appears to be well-structured and informative.',
            'categorize' => 'This content appears to be general information.',
            'sentiment' => 'The sentiment appears to be neutral.',
            'general' => 'AI analysis is temporarily unavailable. Please try again later.'
        ];

        return $fallbacks[$feature] ?? $fallbacks['general'];
    }
}
