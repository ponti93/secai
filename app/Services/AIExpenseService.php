<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIExpenseService
{
    protected $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Process receipt image and extract expense data
     */
    public function processReceipt($imageFile): array
    {
        try {
            // Convert image to base64
            $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
            $mimeType = $imageFile->getMimeType();

            $prompt = "Analyze this receipt image and extract expense information. Return JSON with the following structure:
            {
                \"description\": \"expense description\",
                \"amount\": \"amount as number\",
                \"merchant\": \"merchant name\",
                \"category\": \"expense category\",
                \"expense_date\": \"date in YYYY-MM-DD format\",
                \"tax_amount\": \"tax amount as number\",
                \"receipt_number\": \"receipt number if visible\",
                \"confidence\": \"confidence score 0-1\"
            }
            
            Categories should be one of: office-supplies, travel, meals, software, utilities, other
            Return only valid JSON, no other text.";

            $response = $this->callGeminiWithImage($prompt, $imageData, $mimeType);

            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Receipt processing failed: ' . $e->getMessage());
        }

        return [
            'description' => 'Receipt processed',
            'amount' => 0,
            'merchant' => '',
            'category' => 'other',
            'expense_date' => date('Y-m-d'),
            'tax_amount' => 0,
            'receipt_number' => '',
            'confidence' => 0.5
        ];
    }

    /**
     * Categorize expense using AI
     */
    public function categorizeExpense(string $description, float $amount, string $merchant = ''): array
    {
        try {
            $prompt = "Categorize this expense based on the description and merchant. Return JSON with category and confidence.
            
            Description: {$description}
            Amount: {$amount}
            Merchant: {$merchant}
            
            Categories: office-supplies, travel, meals, software, utilities, other
            Return JSON: {\"category\": \"category_name\", \"confidence\": 0.0-1.0}";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Expense categorization failed: ' . $e->getMessage());
        }

        return [
            'category' => 'other',
            'confidence' => 0.5
        ];
    }

    /**
     * Generate expense description using AI
     */
    public function generateDescription(string $merchant, float $amount, string $category = ''): string
    {
        try {
            $prompt = "Generate a professional expense description for this transaction:
            
            Merchant: {$merchant}
            Amount: {$amount}
            Category: {$category}
            
            Return only the description, no other text.";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                return trim($response);
            }
        } catch (\Exception $e) {
            Log::error('Description generation failed: ' . $e->getMessage());
        }

        return "Expense at {$merchant} - \${$amount}";
    }

    /**
     * Analyze expense patterns and provide insights
     */
    public function analyzeExpensePatterns(array $expenses): array
    {
        try {
            $expenseData = json_encode($expenses);
            
            $prompt = "Analyze these expense patterns and provide insights. Return JSON with analysis.
            
            Expenses: {$expenseData}
            
            Return JSON with: 
            - top_categories (array of categories with amounts)
            - spending_trends (array of trends)
            - recommendations (array of suggestions)
            - anomalies (array of unusual expenses)";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Expense analysis failed: ' . $e->getMessage());
        }

        return [
            'top_categories' => [],
            'spending_trends' => [],
            'recommendations' => [],
            'anomalies' => []
        ];
    }

    /**
     * Call Gemini API with image
     */
    private function callGeminiWithImage(string $prompt, string $imageData, string $mimeType): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiApiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
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
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call with image failed: ' . $e->getMessage());
        }

        return null;
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
}
