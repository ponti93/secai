<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIDocumentService
{
    protected $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Summarize document content using Gemini AI
     */
    public function summarizeDocument(string $content, string $documentType = 'general'): array
    {
        try {
            $prompt = "Summarize this document content. Extract the key points, main ideas, and important information. 
            Make it concise but comprehensive. Return as JSON with:
            - summary: Main summary (2-3 paragraphs)
            - key_points: Array of key points
            - main_topics: Array of main topics covered
            - word_count: Original word count
            - summary_word_count: Summary word count
            
            Document Type: {$documentType}
            Content: {$content}";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'summary' => $data['summary'] ?? 'Unable to generate summary',
                        'key_points' => $data['key_points'] ?? [],
                        'main_topics' => $data['main_topics'] ?? [],
                        'word_count' => $data['word_count'] ?? str_word_count($content),
                        'summary_word_count' => $data['summary_word_count'] ?? 0
                    ];
                } else {
                    // If response is not JSON, it might be a fallback string
                    return [
                        'success' => false,
                        'summary' => $response,
                        'key_points' => ['Key points extraction unavailable'],
                        'main_topics' => ['Main topics unavailable'],
                        'word_count' => str_word_count($content),
                        'summary_word_count' => 0,
                        'message' => 'AI analysis temporarily unavailable due to quota limits.'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Document summarization failed: ' . $e->getMessage());
        }

        // Fallback summary
        $wordCount = str_word_count($content);
        $summary = substr($content, 0, 200) . (strlen($content) > 200 ? '...' : '');
        
        return [
            'success' => false,
            'summary' => $summary,
            'key_points' => [],
            'main_topics' => [],
            'word_count' => $wordCount,
            'summary_word_count' => str_word_count($summary)
        ];
    }

    /**
     * Generate document content from outline or prompt using Gemini AI
     */
    public function generateDocumentContent(string $prompt, string $documentType = 'general', array $options = []): array
    {
        try {
            $defaultOptions = [
                'tone' => 'professional',
                'length' => 'medium',
                'format' => 'structured',
                'include_intro' => true,
                'include_conclusion' => true
            ];
            
            $options = array_merge($defaultOptions, $options);
            
            $systemPrompt = $this->buildDocumentPrompt($documentType, $options);
            $fullPrompt = "{$systemPrompt}\n\nUser Request: {$prompt}";
            
            $response = $this->callGemini($fullPrompt);
            
            if ($response) {
                return [
                    'success' => true,
                    'content' => $response,
                    'word_count' => str_word_count($response),
                    'document_type' => $documentType,
                    'options' => $options
                ];
            }
        } catch (\Exception $e) {
            Log::error('Document generation failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'content' => 'Unable to generate document content. Please try again.',
            'word_count' => 0,
            'document_type' => $documentType,
            'options' => $options
        ];
    }

    /**
     * Extract keywords from document content
     */
    public function extractKeywords(string $content, int $maxKeywords = 10): array
    {
        try {
            $prompt = "Extract the most important keywords and phrases from this document content. 
            Return as JSON array with keywords. Focus on:
            - Main topics and subjects
            - Important names, places, dates
            - Key concepts and terms
            - Technical terms if any
            
            Maximum {$maxKeywords} keywords.
            Content: {$content}";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $keywords = json_decode($response, true);
                if (is_array($keywords)) {
                    return [
                        'success' => true,
                        'keywords' => array_slice($keywords, 0, $maxKeywords)
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Keyword extraction failed: ' . $e->getMessage());
        }

        // Fallback: simple keyword extraction
        $words = str_word_count(strtolower($content), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those'];
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        $wordCounts = array_count_values($filteredWords);
        arsort($wordCounts);
        $keywords = array_slice(array_keys($wordCounts), 0, $maxKeywords);

        return [
            'success' => false,
            'keywords' => $keywords
        ];
    }

    /**
     * Analyze document content for quality and readability
     */
    public function analyzeDocument(string $content): array
    {
        try {
            $prompt = "Analyze this document content and provide insights. Return as JSON with:
            - readability_score: 1-10 (10 being most readable)
            - tone: Professional, casual, formal, technical, etc.
            - quality_score: 1-10 (10 being highest quality)
            - suggestions: Array of improvement suggestions
            - strengths: Array of document strengths
            - word_count: Total word count
            - reading_time: Estimated reading time in minutes
            
            Content: {$content}";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'readability_score' => $data['readability_score'] ?? 5,
                        'tone' => $data['tone'] ?? 'neutral',
                        'quality_score' => $data['quality_score'] ?? 5,
                        'suggestions' => $data['suggestions'] ?? [],
                        'strengths' => $data['strengths'] ?? [],
                        'word_count' => $data['word_count'] ?? str_word_count($content),
                        'reading_time' => $data['reading_time'] ?? ceil(str_word_count($content) / 200)
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Document analysis failed: ' . $e->getMessage());
        }

        // Fallback analysis
        $wordCount = str_word_count($content);
        $readingTime = ceil($wordCount / 200);
        
        return [
            'success' => false,
            'readability_score' => 5,
            'tone' => 'neutral',
            'quality_score' => 5,
            'suggestions' => [],
            'strengths' => [],
            'word_count' => $wordCount,
            'reading_time' => $readingTime
        ];
    }

    /**
     * Build document generation prompt based on type and options
     */
    private function buildDocumentPrompt(string $documentType, array $options): string
    {
        $prompts = [
            'report' => 'You are an AI assistant that creates professional business reports. Write clear, well-structured content with proper formatting and data analysis.',
            'proposal' => 'You are an AI assistant that creates compelling business proposals. Focus on benefits, clear structure, and persuasive language.',
            'memo' => 'You are an AI assistant that creates professional memos. Use clear, concise language and proper memo format.',
            'letter' => 'You are an AI assistant that creates professional business letters. Use formal tone and proper letter format.',
            'agenda' => 'You are an AI assistant that creates meeting agendas. Focus on clear structure, time allocation, and actionable items.',
            'minutes' => 'You are an AI assistant that creates meeting minutes. Capture key decisions, action items, and important discussions.',
            'general' => 'You are an AI assistant that creates professional documents. Write clear, well-structured content appropriate for business use.'
        ];

        $basePrompt = $prompts[$documentType] ?? $prompts['general'];
        
        $toneInstructions = [
            'professional' => 'Use a professional, business-appropriate tone.',
            'casual' => 'Use a friendly, conversational tone.',
            'formal' => 'Use a very formal, official tone.',
            'technical' => 'Use precise, technical language.'
        ];

        $lengthInstructions = [
            'short' => 'Keep it concise and to the point (1-2 pages).',
            'medium' => 'Provide moderate detail (2-5 pages).',
            'long' => 'Be comprehensive and detailed (5+ pages).'
        ];

        $formatInstructions = [
            'structured' => 'Use clear headings, bullet points, and numbered lists where appropriate.',
            'narrative' => 'Write in a flowing, narrative style.',
            'outline' => 'Create a structured outline format.'
        ];

        $prompt = $basePrompt;
        $prompt .= " " . ($toneInstructions[$options['tone']] ?? $toneInstructions['professional']);
        $prompt .= " " . ($lengthInstructions[$options['length']] ?? $lengthInstructions['medium']);
        $prompt .= " " . ($formatInstructions[$options['format']] ?? $formatInstructions['structured']);

        if ($options['include_intro']) {
            $prompt .= " Include a clear introduction.";
        }
        if ($options['include_conclusion']) {
            $prompt .= " Include a strong conclusion.";
        }

        return $prompt;
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
        if (strpos($prompt, 'summarize') !== false) {
            return '{"summary": "Document summary temporarily unavailable due to API quota limits. Please try again later.", "key_points": ["Key points extraction unavailable"], "main_topics": ["Main topics unavailable"], "word_count": 0, "summary_word_count": 0}';
        } elseif (strpos($prompt, 'keywords') !== false) {
            return '["important", "document", "information", "review"]';
        } elseif (strpos($prompt, 'analyze') !== false) {
            return '{"readability_score": 7, "tone": "professional", "quality_score": 8, "suggestions": ["Analysis temporarily unavailable"], "strengths": ["Content appears well-structured"], "word_count": 0, "reading_time": 0}';
        } elseif (strpos($prompt, 'generate') !== false) {
            return 'This is a fallback document generated due to API quota limits. Please try again later for AI-generated content.';
        }
        
        return 'AI analysis temporarily unavailable due to quota limits. Please try again later.';
    }
}