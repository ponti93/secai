<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIInventoryService
{
    protected $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Forecast demand for inventory items using AI
     */
    public function forecastDemand(int $userId, array $options = []): array
    {
        try {
            // Get inventory data for analysis
            $inventory = Inventory::where('user_id', $userId)
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($inventory->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No inventory data available for forecasting',
                    'forecasts' => []
                ];
            }

            // Prepare data for AI analysis
            $inventoryData = $inventory->map(function ($item) {
                return [
                    'name' => $item->name,
                    'current_quantity' => $item->quantity,
                    'min_quantity' => $item->min_quantity ?? 0,
                    'max_quantity' => $item->max_quantity ?? 0,
                    'unit_price' => $item->unit_price ?? 0,
                    'category' => $item->category ?? 'general',
                    'last_updated' => $item->updated_at->format('Y-m-d'),
                    'description' => $item->description ?? ''
                ];
            })->toArray();

            $forecastPeriod = $options['period'] ?? '30 days';
            $confidence_level = $options['confidence'] ?? 'medium';

            $prompt = "Analyze this inventory data and provide demand forecasting for the next {$forecastPeriod}:
            
            Inventory Data: " . json_encode($inventoryData) . "
            
            Confidence Level: {$confidence_level}
            
            Return JSON with:
            - forecasts: Array of demand forecasts for each item with:
              - item_name: Name of the item
              - predicted_demand: Predicted quantity needed
              - confidence_score: 0-100 confidence in prediction
              - risk_level: low/medium/high risk of stockout
              - recommended_action: suggested action
              - factors: Array of factors influencing the forecast
            - overall_analysis: Overall inventory health analysis
            - recommendations: Array of general inventory management recommendations
            - trends: Array of identified trends and patterns";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'forecasts' => $data['forecasts'] ?? [],
                        'overall_analysis' => $data['overall_analysis'] ?? 'Analysis completed',
                        'recommendations' => $data['recommendations'] ?? [],
                        'trends' => $data['trends'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Demand forecasting failed: ' . $e->getMessage());
        }

        // Fallback forecasting
        return [
            'success' => false,
            'message' => 'Unable to generate AI forecasts. Using basic analysis.',
            'forecasts' => $this->generateFallbackForecasts($inventory),
            'overall_analysis' => 'Basic inventory analysis completed',
            'recommendations' => ['Monitor stock levels regularly', 'Set up automated reorder points'],
            'trends' => []
        ];
    }

    /**
     * Generate reorder suggestions using AI
     */
    public function generateReorderSuggestions(int $userId): array
    {
        try {
            // Get inventory items that might need reordering
            $inventory = Inventory::where('user_id', $userId)
                ->where(function ($query) {
                    $query->whereColumn('quantity', '<=', 'min_quantity')
                          ->orWhere('quantity', '<=', 5); // Low stock threshold
                })
                ->orWhere('quantity', '>', 0) // Include all items for analysis
                ->orderBy('quantity', 'asc')
                ->get();

            if ($inventory->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No inventory items found',
                    'suggestions' => []
                ];
            }

            $inventoryData = $inventory->map(function ($item) {
                return [
                    'name' => $item->name,
                    'current_quantity' => $item->quantity,
                    'min_quantity' => $item->min_quantity ?? 0,
                    'max_quantity' => $item->max_quantity ?? 0,
                    'unit_price' => $item->unit_price ?? 0,
                    'category' => $item->category ?? 'general',
                    'supplier' => $item->supplier ?? 'Unknown',
                    'lead_time_days' => $item->lead_time_days ?? 7,
                    'description' => $item->description ?? ''
                ];
            })->toArray();

            $prompt = "Analyze this inventory data and provide smart reorder suggestions:
            
            Inventory Data: " . json_encode($inventoryData) . "
            
            Return JSON with:
            - suggestions: Array of reorder suggestions with:
              - item_name: Name of the item
              - current_stock: Current quantity
              - suggested_quantity: Recommended reorder quantity
              - urgency: low/medium/high urgency level
              - reason: Explanation for the suggestion
              - estimated_cost: Estimated cost of reorder
              - priority_score: 1-10 priority score
              - timing: When to place the order
            - summary: Summary of reorder recommendations
            - cost_analysis: Total estimated cost and budget impact
            - risk_assessment: Assessment of stockout risks";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'suggestions' => $data['suggestions'] ?? [],
                        'summary' => $data['summary'] ?? 'Reorder analysis completed',
                        'cost_analysis' => $data['cost_analysis'] ?? [],
                        'risk_assessment' => $data['risk_assessment'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Reorder suggestions failed: ' . $e->getMessage());
        }

        // Fallback suggestions
        return [
            'success' => false,
            'message' => 'Unable to generate AI suggestions. Using basic analysis.',
            'suggestions' => $this->generateFallbackReorderSuggestions($inventory),
            'summary' => 'Basic reorder analysis completed',
            'cost_analysis' => [],
            'risk_assessment' => []
        ];
    }

    /**
     * Optimize pricing using AI
     */
    public function optimizePricing(int $userId, array $options = []): array
    {
        try {
            // Get inventory data for pricing analysis
            $inventory = Inventory::where('user_id', $userId)
                ->where('unit_price', '>', 0)
                ->orderBy('unit_price', 'desc')
                ->get();

            if ($inventory->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No pricing data available for optimization',
                    'optimizations' => []
                ];
            }

            $inventoryData = $inventory->map(function ($item) {
                return [
                    'name' => $item->name,
                    'current_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'category' => $item->category ?? 'general',
                    'supplier' => $item->supplier ?? 'Unknown',
                    'description' => $item->description ?? '',
                    'last_price_change' => $item->updated_at->format('Y-m-d')
                ];
            })->toArray();

            $market_conditions = $options['market_conditions'] ?? 'stable';
            $profit_margin_target = $options['profit_margin'] ?? 20;
            $competition_level = $options['competition'] ?? 'medium';

            $prompt = "Analyze this inventory pricing data and provide optimization recommendations:
            
            Inventory Data: " . json_encode($inventoryData) . "
            
            Market Conditions: {$market_conditions}
            Target Profit Margin: {$profit_margin_target}%
            Competition Level: {$competition_level}
            
            Return JSON with:
            - optimizations: Array of pricing optimizations with:
              - item_name: Name of the item
              - current_price: Current unit price
              - suggested_price: Recommended new price
              - price_change: Percentage change
              - reasoning: Explanation for the price change
              - expected_impact: Expected impact on sales/profit
              - confidence: Confidence level in the recommendation
              - implementation_priority: high/medium/low priority
            - market_analysis: Analysis of market conditions
            - profit_impact: Expected impact on overall profitability
            - competitive_analysis: Competitive positioning recommendations";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'optimizations' => $data['optimizations'] ?? [],
                        'market_analysis' => $data['market_analysis'] ?? 'Market analysis completed',
                        'profit_impact' => $data['profit_impact'] ?? [],
                        'competitive_analysis' => $data['competitive_analysis'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Pricing optimization failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Unable to generate AI pricing optimization. Using basic analysis.',
            'optimizations' => $this->generateFallbackPricingOptimizations($inventory),
            'market_analysis' => 'Basic market analysis completed',
            'profit_impact' => [],
            'competitive_analysis' => []
        ];
    }

    /**
     * Generate inventory insights and analytics
     */
    public function generateInsights(int $userId): array
    {
        try {
            $inventory = Inventory::where('user_id', $userId)->get();
            
            if ($inventory->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No inventory data available',
                    'insights' => []
                ];
            }

            $inventoryData = $inventory->map(function ($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price ?? 0,
                    'category' => $item->category ?? 'general',
                    'supplier' => $item->supplier ?? 'Unknown',
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'updated_at' => $item->updated_at->format('Y-m-d')
                ];
            })->toArray();

            $prompt = "Analyze this inventory data and provide comprehensive insights:
            
            Inventory Data: " . json_encode($inventoryData) . "
            
            Return JSON with:
            - key_metrics: Key inventory metrics and KPIs
            - trends: Identified trends and patterns
            - recommendations: Actionable recommendations
            - risk_alerts: Potential risks and issues
            - opportunities: Growth and optimization opportunities
            - category_analysis: Analysis by product category
            - supplier_analysis: Supplier performance insights";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return [
                        'success' => true,
                        'insights' => $data
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Insights generation failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Unable to generate AI insights',
            'insights' => []
        ];
    }

    /**
     * Generate fallback forecasts
     */
    private function generateFallbackForecasts($inventory): array
    {
        $forecasts = [];
        
        foreach ($inventory as $item) {
            $currentQty = $item->quantity;
            $minQty = $item->min_quantity ?? 0;
            
            // Simple forecast based on current stock vs minimum
            $predictedDemand = max(1, $currentQty - $minQty);
            $riskLevel = $currentQty <= $minQty ? 'high' : ($currentQty <= $minQty * 1.5 ? 'medium' : 'low');
            
            $forecasts[] = [
                'item_name' => $item->name,
                'predicted_demand' => $predictedDemand,
                'confidence_score' => 60,
                'risk_level' => $riskLevel,
                'recommended_action' => $riskLevel === 'high' ? 'Reorder immediately' : 'Monitor closely',
                'factors' => ['Current stock level', 'Minimum quantity threshold']
            ];
        }
        
        return $forecasts;
    }

    /**
     * Generate fallback reorder suggestions
     */
    private function generateFallbackReorderSuggestions($inventory): array
    {
        $suggestions = [];
        
        foreach ($inventory as $item) {
            $currentQty = $item->quantity;
            $minQty = $item->min_quantity ?? 0;
            $maxQty = $item->max_quantity ?? $minQty * 2;
            
            if ($currentQty <= $minQty) {
                $suggestions[] = [
                    'item_name' => $item->name,
                    'current_stock' => $currentQty,
                    'suggested_quantity' => $maxQty - $currentQty,
                    'urgency' => 'high',
                    'reason' => 'Stock below minimum threshold',
                    'estimated_cost' => ($maxQty - $currentQty) * ($item->unit_price ?? 0),
                    'priority_score' => 9,
                    'timing' => 'Immediately'
                ];
            }
        }
        
        return $suggestions;
    }

    /**
     * Generate fallback pricing optimizations
     */
    private function generateFallbackPricingOptimizations($inventory): array
    {
        $optimizations = [];
        
        foreach ($inventory as $item) {
            $currentPrice = $item->unit_price ?? 0;
            if ($currentPrice > 0) {
                // Simple 5% increase suggestion
                $suggestedPrice = $currentPrice * 1.05;
                
                $optimizations[] = [
                    'item_name' => $item->name,
                    'current_price' => $currentPrice,
                    'suggested_price' => round($suggestedPrice, 2),
                    'price_change' => 5.0,
                    'reasoning' => 'Standard 5% price increase for margin improvement',
                    'expected_impact' => 'Moderate positive impact on profitability',
                    'confidence' => 70,
                    'implementation_priority' => 'medium'
                ];
            }
        }
        
        return $optimizations;
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
        if (strpos($prompt, 'forecast') !== false) {
            return '{"forecasts": [{"item_name": "Sample Item", "predicted_demand": 10, "confidence_score": 60, "risk_level": "medium", "recommended_action": "Monitor stock levels", "factors": ["Historical data unavailable"]}], "overall_analysis": "Forecasting temporarily unavailable due to API quota limits", "recommendations": ["Monitor stock levels manually"], "trends": []}';
        } elseif (strpos($prompt, 'reorder') !== false) {
            return '{"suggestions": [{"item_name": "Sample Item", "current_stock": 5, "suggested_quantity": 20, "urgency": "medium", "reason": "Stock getting low", "estimated_cost": 100, "priority_score": 7, "timing": "Within 1 week"}], "summary": "Reorder suggestions temporarily unavailable", "cost_analysis": [], "risk_assessment": []}';
        } elseif (strpos($prompt, 'optimize') !== false) {
            return '{"optimizations": [{"item_name": "Sample Item", "current_price": 10, "suggested_price": 10.50, "price_change": 5, "reasoning": "Standard price increase", "expected_impact": "Moderate positive impact", "confidence": 70, "implementation_priority": "medium"}], "market_analysis": "Pricing optimization temporarily unavailable", "profit_impact": [], "competitive_analysis": []}';
        } elseif (strpos($prompt, 'insights') !== false) {
            return '{"key_metrics": {"total_items": 0, "low_stock": 0}, "trends": [], "recommendations": ["AI insights temporarily unavailable"], "risk_alerts": [], "opportunities": [], "category_analysis": {}, "supplier_analysis": {}}';
        }
        
        return '{"message": "AI analysis temporarily unavailable due to quota limits. Please try again later."}';
    }
}
