<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ExpenseAiService
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Extract data from receipt using AI OCR with Gemini API
     */
    public function extractReceiptData($imagePath)
    {
        try {
            // Convert image to base64 for Gemini API
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            
            $prompt = "Analyze this receipt image and extract the following detailed information in JSON format:
            - description: Main item or service purchased (be specific about what was bought)
            - amount: Total amount spent (as a number, no currency symbols)
            - tax_amount: Tax amount if visible (as a number)
            - category: One of: office-supplies, travel, meals, software, utilities, other
            - date: Date of purchase (YYYY-MM-DD format)
            - merchant: Store or business name
            - merchant_address: Store address if visible
            - payment_method: Payment method used (cash, card, etc.)
            - items: Array of individual items purchased with quantities and prices
            - subtotal: Subtotal before tax (as a number)
            - discount: Any discount applied (as a number)
            - receipt_number: Receipt or ticket number if visible
            - time: Time of purchase if visible
            - staff: Staff member or cashier name if visible
            
            Return only valid JSON without any additional text or explanations.";

            // Use Gemini API for image analysis
            $result = $this->geminiService->analyzeImage($imageData, $mimeType, $prompt);
            
            if ($result['success']) {
                $extractedData = json_decode($result['response'], true);
                
                // Validate and clean the extracted data
                if (is_array($extractedData)) {
                    $extractedData = array_merge([
                        'description' => 'Receipt purchase',
                        'amount' => 0.00,
                        'tax_amount' => 0.00,
                        'category' => 'other',
                        'date' => date('Y-m-d'),
                        'merchant' => 'Unknown',
                        'merchant_address' => '',
                        'payment_method' => '',
                        'items' => [],
                        'subtotal' => 0.00,
                        'discount' => 0.00,
                        'receipt_number' => '',
                        'time' => '',
                        'staff' => ''
                    ], $extractedData);
                    
                    // Ensure numeric values
                    $extractedData['amount'] = floatval($extractedData['amount']);
                    $extractedData['tax_amount'] = floatval($extractedData['tax_amount']);
                    $extractedData['subtotal'] = floatval($extractedData['subtotal']);
                    $extractedData['discount'] = floatval($extractedData['discount']);
                    
                    // Validate category
                    $validCategories = ['office-supplies', 'travel', 'meals', 'software', 'utilities', 'other'];
                    if (!in_array($extractedData['category'], $validCategories)) {
                        $extractedData['category'] = 'other';
                    }
                    
                    // Ensure items is an array
                    if (!is_array($extractedData['items'])) {
                        $extractedData['items'] = [];
                    }
                    
                    return [
                        'success' => true,
                        'data' => $extractedData
                    ];
                }
            }
            
            // Fallback if AI processing fails
            return [
                'success' => false,
                'message' => 'Failed to extract data from receipt. Please enter manually.',
                'data' => [
                    'description' => '',
                    'amount' => 0.00,
                    'category' => 'other',
                    'date' => date('Y-m-d'),
                    'merchant' => ''
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Receipt OCR Error: ' . $e->getMessage());
            
            // Check if it's a quota error
            if (strpos($e->getMessage(), '429') !== false || strpos($e->getMessage(), 'quota') !== false) {
                return [
                    'success' => false,
                    'message' => 'Gemini API quota exceeded. Please try again later or contact support.',
                    'data' => [
                        'description' => '',
                        'amount' => 0.00,
                        'category' => 'other',
                        'date' => date('Y-m-d'),
                        'merchant' => ''
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error processing receipt: ' . $e->getMessage(),
                'data' => [
                    'description' => '',
                    'amount' => 0.00,
                    'category' => 'other',
                    'date' => date('Y-m-d'),
                    'merchant' => ''
                ]
            ];
        }
    }

    /**
     * Categorize expense using AI
     */
    public function categorizeExpense($description, $amount = null)
    {
        try {
            $prompt = "Categorize this expense description into one of these categories:
            - office-supplies: Office supplies, stationery, equipment
            - travel: Transportation, flights, hotels, meals while traveling
            - meals: Food and beverages for business meetings
            - software: Software licenses, subscriptions, digital tools
            - utilities: Electricity, water, internet, phone bills
            - other: Anything that doesn't fit the above categories
            
            Description: {$description}
            Amount: {$amount}
            
            Return only the category name.";

            $result = $this->geminiService->generateText($prompt);
            
            if ($result['success']) {
                // Clean up response and validate
                $response = $result['content'] ?? $result['response'] ?? '';
                $category = strtolower(trim($response));
                
                // Extract category from response if it's not exact
                $validCategories = ['office-supplies', 'travel', 'meals', 'software', 'utilities', 'other'];
                $foundCategory = 'other';
                
                foreach ($validCategories as $validCategory) {
                    if (strpos($category, $validCategory) !== false) {
                        $foundCategory = $validCategory;
                        break;
                    }
                }
                
                // Check for common variations
                if (strpos($category, 'office') !== false || strpos($category, 'supplies') !== false) {
                    $foundCategory = 'office-supplies';
                } elseif (strpos($category, 'travel') !== false || strpos($category, 'transportation') !== false) {
                    $foundCategory = 'travel';
                } elseif (strpos($category, 'meal') !== false || strpos($category, 'food') !== false) {
                    $foundCategory = 'meals';
                } elseif (strpos($category, 'software') !== false || strpos($category, 'subscription') !== false) {
                    $foundCategory = 'software';
                } elseif (strpos($category, 'utility') !== false || strpos($category, 'electricity') !== false) {
                    $foundCategory = 'utilities';
                }

                return [
                    'success' => true,
                    'category' => $foundCategory,
                    'confidence' => 0.85
                ];
            }

            return [
                'success' => false,
                'category' => 'other',
                'confidence' => 0.0
            ];
        } catch (\Exception $e) {
            Log::error('Expense Categorization Error: ' . $e->getMessage());
            return [
                'success' => false,
                'category' => 'other',
                'confidence' => 0.0
            ];
        }
    }

    /**
     * Detect potential fraud in expense
     */
    public function detectFraud($expenseData)
    {
        try {
            $prompt = "Analyze this expense for potential fraud indicators:
            - Unusually high amount for the category
            - Suspicious description patterns
            - Timing anomalies
            - Duplicate patterns
            
            Expense: {$expenseData['description']}
            Amount: {$expenseData['amount']}
            Category: {$expenseData['category']}
            Date: {$expenseData['date']}
            
            Return a JSON object with:
            - is_fraud: boolean
            - risk_score: number (0-100)
            - reasons: array of strings
            - recommendations: array of strings";

            $result = $this->geminiService->generateText($prompt);
            
            if ($result['success']) {
                $fraudAnalysis = json_decode($result['response'], true);
                
                if (is_array($fraudAnalysis)) {
                    return [
                        'success' => true,
                        'is_fraud' => $fraudAnalysis['is_fraud'] ?? false,
                        'risk_score' => $fraudAnalysis['risk_score'] ?? 0,
                        'reasons' => $fraudAnalysis['reasons'] ?? [],
                        'recommendations' => $fraudAnalysis['recommendations'] ?? []
                    ];
                }
            }

            // Fallback analysis
            $riskScore = 0;
            $reasons = [];
            $recommendations = [];

            // Check for unusually high amounts
            if ($expenseData['amount'] > 1000) {
                $riskScore += 30;
                $reasons[] = 'Unusually high amount';
                $recommendations[] = 'Request additional documentation';
            }

            // Check for suspicious descriptions
            $suspiciousWords = ['misc', 'other', 'cash', 'personal'];
            foreach ($suspiciousWords as $word) {
                if (stripos($expenseData['description'], $word) !== false) {
                    $riskScore += 20;
                    $reasons[] = 'Vague or suspicious description';
                    $recommendations[] = 'Request detailed receipt';
                }
            }

            return [
                'success' => true,
                'is_fraud' => $riskScore > 50,
                'risk_score' => $riskScore,
                'reasons' => $reasons,
                'recommendations' => $recommendations
            ];
        } catch (\Exception $e) {
            Log::error('Fraud Detection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'is_fraud' => false,
                'risk_score' => 0,
                'reasons' => [],
                'recommendations' => []
            ];
        }
    }

    /**
     * Generate budget analysis and recommendations
     */
    public function analyzeBudget($expenses, $budget)
    {
        try {
            $totalSpent = array_sum(array_column($expenses, 'amount'));
            $remaining = $budget - $totalSpent;
            $percentage = $budget > 0 ? ($totalSpent / $budget) * 100 : 0;

            $analysis = [
                'total_budget' => $budget,
                'total_spent' => $totalSpent,
                'remaining' => $remaining,
                'percentage_used' => $percentage,
                'status' => $percentage > 90 ? 'critical' : ($percentage > 70 ? 'warning' : 'good'),
                'recommendations' => []
            ];

            // Generate AI recommendations
            if ($percentage > 90) {
                $analysis['recommendations'][] = 'Budget nearly exhausted. Consider reducing non-essential expenses.';
            } elseif ($percentage > 70) {
                $analysis['recommendations'][] = 'Budget usage is high. Monitor spending closely.';
            } else {
                $analysis['recommendations'][] = 'Budget usage is healthy. Continue current spending patterns.';
            }

            // Category-specific recommendations
            $categoryTotals = [];
            foreach ($expenses as $expense) {
                $category = $expense['category'];
                $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $expense['amount'];
            }

            foreach ($categoryTotals as $category => $amount) {
                if ($amount > $budget * 0.3) {
                    $analysis['recommendations'][] = "High spending in {$category} category. Consider reviewing these expenses.";
                }
            }

            return [
                'success' => true,
                'analysis' => $analysis
            ];
        } catch (\Exception $e) {
            Log::error('Budget Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'analysis' => null
            ];
        }
    }

    /**
     * Generate spending insights
     */
    public function generateInsights($expenses)
    {
        try {
            $totalExpenses = count($expenses);
            $totalAmount = array_sum(array_column($expenses, 'amount'));
            $averageAmount = $totalExpenses > 0 ? $totalAmount / $totalExpenses : 0;

            // Category breakdown
            $categoryTotals = [];
            foreach ($expenses as $expense) {
                $category = $expense['category'];
                $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $expense['amount'];
            }

            // Monthly trends
            $monthlyTotals = [];
            foreach ($expenses as $expense) {
                $date = $expense['expense_date'] ?? $expense['date'] ?? date('Y-m-d');
                $month = date('Y-m', strtotime($date));
                $monthlyTotals[$month] = ($monthlyTotals[$month] ?? 0) + $expense['amount'];
            }

            $insights = [
                'total_expenses' => $totalExpenses,
                'total_amount' => $totalAmount,
                'average_amount' => $averageAmount,
                'top_category' => array_keys($categoryTotals, max($categoryTotals))[0] ?? 'none',
                'category_breakdown' => $categoryTotals,
                'monthly_trends' => $monthlyTotals,
                'recommendations' => []
            ];

            // Generate AI insights
            if ($averageAmount > 500) {
                $insights['recommendations'][] = 'High average expense amount. Consider reviewing large purchases.';
            }

            if (count($categoryTotals) > 5) {
                $insights['recommendations'][] = 'Expenses spread across many categories. Consider consolidating vendors.';
            }

            return [
                'success' => true,
                'insights' => $insights
            ];
        } catch (\Exception $e) {
            Log::error('Insights Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'insights' => null
            ];
        }
    }
}
