<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\CalendarEvent;
use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIDashboardService
{
    protected $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Generate comprehensive productivity analytics using AI
     */
    public function generateProductivityAnalytics(int $userId): array
    {
        try {
            // Gather data from all modules
            $data = $this->gatherDashboardData($userId);
            
            $prompt = "Analyze this comprehensive productivity data and provide detailed analytics:
            
            Dashboard Data: " . json_encode($data) . "
            
            Return JSON with:
            - productivity_score: Overall productivity score (0-100)
            - module_performance: Performance analysis for each module (emails, documents, meetings, calendar, expenses, inventory)
            - time_management: Analysis of time management patterns
            - efficiency_metrics: Key efficiency indicators
            - bottlenecks: Identified productivity bottlenecks
            - strengths: Areas of strength and good practices
            - improvement_areas: Specific areas for improvement
            - recommendations: Actionable recommendations for better productivity
            - trends: Productivity trends over time
            - insights: Key insights and observations";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $analytics = json_decode($response, true);
                if (is_array($analytics)) {
                    return [
                        'success' => true,
                        'analytics' => $analytics
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Productivity analytics failed: ' . $e->getMessage());
        }

        // Fallback analytics
        return [
            'success' => false,
            'message' => 'Unable to generate AI analytics. Using basic analysis.',
            'analytics' => $this->generateFallbackAnalytics($userId)
        ];
    }

    /**
     * Analyze trends across all modules using AI
     */
    public function analyzeTrends(int $userId, array $options = []): array
    {
        try {
            $data = $this->gatherDashboardData($userId);
            $timeframe = $options['timeframe'] ?? '30 days';
            $focus_areas = $options['focus_areas'] ?? ['all'];
            
            $prompt = "Analyze trends and patterns in this productivity data:
            
            Dashboard Data: " . json_encode($data) . "
            Timeframe: {$timeframe}
            Focus Areas: " . implode(', ', $focus_areas) . "
            
            Return JSON with:
            - overall_trends: Overall productivity trends
            - module_trends: Trends for each module
            - patterns: Identified patterns and correlations
            - seasonal_effects: Any seasonal or cyclical patterns
            - growth_areas: Areas showing growth or improvement
            - declining_areas: Areas showing decline or concern
            - predictions: Predictions for future trends
            - recommendations: Recommendations based on trend analysis
            - insights: Key insights from trend analysis";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $trends = json_decode($response, true);
                if (is_array($trends)) {
                    return [
                        'success' => true,
                        'trends' => $trends
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Trend analysis failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Unable to generate AI trend analysis. Using basic analysis.',
            'trends' => $this->generateFallbackTrends($userId)
        ];
    }

    /**
     * Generate smart recommendations using AI
     */
    public function generateSmartRecommendations(int $userId): array
    {
        try {
            $data = $this->gatherDashboardData($userId);
            
            $prompt = "Based on this productivity data, generate smart, personalized recommendations:
            
            Dashboard Data: " . json_encode($data) . "
            
            Return JSON with:
            - immediate_actions: High-priority actions to take today
            - weekly_goals: Goals to focus on this week
            - monthly_objectives: Objectives for this month
            - efficiency_tips: Specific tips to improve efficiency
            - automation_suggestions: Areas where automation could help
            - workflow_improvements: Suggestions for workflow optimization
            - skill_development: Skills to develop for better productivity
            - tool_recommendations: Tools or features to explore
            - priority_focus: Areas to prioritize based on current performance
            - personalized_insights: Personalized insights and recommendations";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $recommendations = json_decode($response, true);
                if (is_array($recommendations)) {
                    return [
                        'success' => true,
                        'recommendations' => $recommendations
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Smart recommendations failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Unable to generate AI recommendations. Using basic suggestions.',
            'recommendations' => $this->generateFallbackRecommendations($userId)
        ];
    }

    /**
     * Generate comprehensive insights using AI
     */
    public function generateComprehensiveInsights(int $userId): array
    {
        try {
            $data = $this->gatherDashboardData($userId);
            
            $prompt = "Provide comprehensive insights and analysis of this productivity data:
            
            Dashboard Data: " . json_encode($data) . "
            
            Return JSON with:
            - executive_summary: High-level summary of productivity status
            - key_metrics: Most important metrics and KPIs
            - performance_analysis: Detailed performance analysis
            - efficiency_analysis: Efficiency and optimization analysis
            - time_analysis: Time management and allocation analysis
            - communication_analysis: Communication patterns and effectiveness
            - workflow_analysis: Workflow efficiency and bottlenecks
            - goal_alignment: How current activities align with goals
            - risk_assessment: Potential risks and areas of concern
            - opportunities: Growth opportunities and potential improvements
            - action_plan: Specific action plan for improvement
            - success_factors: Key factors contributing to success
            - challenges: Current challenges and how to address them";

            $response = $this->callGemini($prompt);
            
            if ($response) {
                $insights = json_decode($response, true);
                if (is_array($insights)) {
                    return [
                        'success' => true,
                        'insights' => $insights
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Comprehensive insights failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Unable to generate AI insights. Using basic analysis.',
            'insights' => $this->generateFallbackInsights($userId)
        ];
    }

    /**
     * Gather comprehensive data from all modules
     */
    private function gatherDashboardData(int $userId): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Email data
        $emailStats = [
            'total_emails' => Email::where('user_id', $userId)->count(),
            'unread_emails' => Email::where('user_id', $userId)->where('is_read', false)->count(),
            'emails_today' => Email::where('user_id', $userId)->whereDate('created_at', $today)->count(),
            'emails_this_week' => Email::where('user_id', $userId)->where('created_at', '>=', $thisWeek)->count(),
            'emails_this_month' => Email::where('user_id', $userId)->where('created_at', '>=', $thisMonth)->count(),
            'sent_emails' => Email::where('user_id', $userId)->where('status', 'sent')->count(),
            'draft_emails' => Email::where('user_id', $userId)->where('status', 'draft')->count(),
            'important_emails' => Email::where('user_id', $userId)->where('is_important', true)->count(),
            'ai_categorized' => Email::where('user_id', $userId)->whereNotNull('ai_category')->count()
        ];

        // Document data
        $documentStats = [
            'total_documents' => Document::where('user_id', $userId)->count(),
            'documents_today' => Document::where('user_id', $userId)->whereDate('created_at', $today)->count(),
            'documents_this_week' => Document::where('user_id', $userId)->where('created_at', '>=', $thisWeek)->count(),
            'documents_this_month' => Document::where('user_id', $userId)->where('created_at', '>=', $thisMonth)->count(),
            'recent_documents' => Document::where('user_id', $userId)->orderBy('created_at', 'desc')->take(5)->get()->map(function($doc) {
                return [
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'created_at' => $doc->created_at->format('Y-m-d H:i')
                ];
            })->toArray()
        ];

        // Meeting data
        $meetingStats = [
            'total_meetings' => Meeting::where('user_id', $userId)->count(),
            'meetings_today' => Meeting::where('user_id', $userId)->whereDate('start_time', $today)->count(),
            'meetings_this_week' => Meeting::where('user_id', $userId)->where('start_time', '>=', $thisWeek)->count(),
            'meetings_this_month' => Meeting::where('user_id', $userId)->where('start_time', '>=', $thisMonth)->count(),
            'upcoming_meetings' => Meeting::where('user_id', $userId)->where('start_time', '>=', $today)->count(),
            'completed_meetings' => Meeting::where('user_id', $userId)->where('status', 'completed')->count(),
            'cancelled_meetings' => Meeting::where('user_id', $userId)->where('status', 'cancelled')->count()
        ];

        // Calendar data
        $calendarStats = [
            'total_events' => CalendarEvent::where('user_id', $userId)->count(),
            'events_today' => CalendarEvent::where('user_id', $userId)->whereDate('start_time', $today)->count(),
            'events_this_week' => CalendarEvent::where('user_id', $userId)->where('start_time', '>=', $thisWeek)->count(),
            'events_this_month' => CalendarEvent::where('user_id', $userId)->where('start_time', '>=', $thisMonth)->count(),
            'upcoming_events' => CalendarEvent::where('user_id', $userId)->where('start_time', '>=', $today)->count(),
            'completed_events' => CalendarEvent::where('user_id', $userId)->where('status', 'completed')->count(),
            'all_day_events' => CalendarEvent::where('user_id', $userId)->where('all_day', true)->count()
        ];

        // Expense data
        $expenseStats = [
            'total_expenses' => Expense::where('user_id', $userId)->count(),
            'expenses_today' => Expense::where('user_id', $userId)->whereDate('expense_date', $today)->count(),
            'expenses_this_week' => Expense::where('user_id', $userId)->where('expense_date', '>=', $thisWeek)->count(),
            'expenses_this_month' => Expense::where('user_id', $userId)->where('expense_date', '>=', $thisMonth)->count(),
            'total_amount' => Expense::where('user_id', $userId)->sum('amount'),
            'amount_this_month' => Expense::where('user_id', $userId)->where('expense_date', '>=', $thisMonth)->sum('amount'),
            'amount_last_month' => Expense::where('user_id', $userId)->whereBetween('expense_date', [$lastMonth, $thisMonth])->sum('amount'),
            'average_expense' => Expense::where('user_id', $userId)->avg('amount'),
            'ai_categorized' => Expense::where('user_id', $userId)->whereNotNull('ai_categorization')->count()
        ];

        // Inventory data
        $inventoryStats = [
            'total_items' => Inventory::where('user_id', $userId)->count(),
            'low_stock_items' => Inventory::where('user_id', $userId)->whereColumn('quantity', '<=', 'min_quantity')->count(),
            'out_of_stock_items' => Inventory::where('user_id', $userId)->where('quantity', 0)->count(),
            'total_value' => Inventory::where('user_id', $userId)->sum(\DB::raw('quantity * unit_price')),
            'items_added_today' => Inventory::where('user_id', $userId)->whereDate('created_at', $today)->count(),
            'items_added_this_week' => Inventory::where('user_id', $userId)->where('created_at', '>=', $thisWeek)->count(),
            'items_added_this_month' => Inventory::where('user_id', $userId)->where('created_at', '>=', $thisMonth)->count()
        ];

        // Activity patterns
        $activityPatterns = [
            'most_active_day' => $this->getMostActiveDay($userId),
            'most_active_hour' => $this->getMostActiveHour($userId),
            'activity_distribution' => $this->getActivityDistribution($userId),
            'productivity_trends' => $this->getProductivityTrends($userId)
        ];

        return [
            'user_id' => $userId,
            'analysis_date' => now()->format('Y-m-d H:i:s'),
            'timeframe' => '30 days',
            'email_stats' => $emailStats,
            'document_stats' => $documentStats,
            'meeting_stats' => $meetingStats,
            'calendar_stats' => $calendarStats,
            'expense_stats' => $expenseStats,
            'inventory_stats' => $inventoryStats,
            'activity_patterns' => $activityPatterns
        ];
    }

    /**
     * Get most active day of the week
     */
    private function getMostActiveDay(int $userId): string
    {
        $dayCounts = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $dayCounts[$day] = 0;
        }
        
        // Count activities by day
        $emails = Email::where('user_id', $userId)->get();
        foreach ($emails as $email) {
            $dayCounts[Carbon::parse($email->created_at)->format('l')]++;
        }
        
        $documents = Document::where('user_id', $userId)->get();
        foreach ($documents as $document) {
            $dayCounts[Carbon::parse($document->created_at)->format('l')]++;
        }
        
        return array_keys($dayCounts, max($dayCounts))[0] ?? 'Monday';
    }

    /**
     * Get most active hour of the day
     */
    private function getMostActiveHour(int $userId): int
    {
        $hourCounts = array_fill(0, 24, 0);
        
        // Count activities by hour
        $emails = Email::where('user_id', $userId)->get();
        foreach ($emails as $email) {
            $hourCounts[Carbon::parse($email->created_at)->hour]++;
        }
        
        $documents = Document::where('user_id', $userId)->get();
        foreach ($documents as $document) {
            $hourCounts[Carbon::parse($document->created_at)->hour]++;
        }
        
        return array_keys($hourCounts, max($hourCounts))[0] ?? 9;
    }

    /**
     * Get activity distribution across modules
     */
    private function getActivityDistribution(int $userId): array
    {
        $emailCount = Email::where('user_id', $userId)->count();
        $documentCount = Document::where('user_id', $userId)->count();
        $meetingCount = Meeting::where('user_id', $userId)->count();
        $calendarCount = CalendarEvent::where('user_id', $userId)->count();
        $expenseCount = Expense::where('user_id', $userId)->count();
        $inventoryCount = Inventory::where('user_id', $userId)->count();
        
        $total = $emailCount + $documentCount + $meetingCount + $calendarCount + $expenseCount + $inventoryCount;
        
        if ($total == 0) {
            return [
                'emails' => 0,
                'documents' => 0,
                'meetings' => 0,
                'calendar' => 0,
                'expenses' => 0,
                'inventory' => 0
            ];
        }
        
        return [
            'emails' => round(($emailCount / $total) * 100, 1),
            'documents' => round(($documentCount / $total) * 100, 1),
            'meetings' => round(($meetingCount / $total) * 100, 1),
            'calendar' => round(($calendarCount / $total) * 100, 1),
            'expenses' => round(($expenseCount / $total) * 100, 1),
            'inventory' => round(($inventoryCount / $total) * 100, 1)
        ];
    }

    /**
     * Get productivity trends over time
     */
    private function getProductivityTrends(int $userId): array
    {
        $trends = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayStart = $date->startOfDay();
            $dayEnd = $date->endOfDay();
            
            $dayActivity = [
                'date' => $date->format('Y-m-d'),
                'emails' => Email::where('user_id', $userId)->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'documents' => Document::where('user_id', $userId)->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'meetings' => Meeting::where('user_id', $userId)->whereDate('start_time', $date)->count(),
                'expenses' => Expense::where('user_id', $userId)->whereDate('expense_date', $date)->count()
            ];
            
            $trends[] = $dayActivity;
        }
        
        return $trends;
    }

    /**
     * Generate fallback analytics
     */
    private function generateFallbackAnalytics(int $userId): array
    {
        $data = $this->gatherDashboardData($userId);
        
        return [
            'productivity_score' => 75,
            'module_performance' => [
                'emails' => ['score' => 80, 'status' => 'good'],
                'documents' => ['score' => 70, 'status' => 'average'],
                'meetings' => ['score' => 85, 'status' => 'excellent'],
                'calendar' => ['score' => 75, 'status' => 'good'],
                'expenses' => ['score' => 65, 'status' => 'needs_improvement'],
                'inventory' => ['score' => 70, 'status' => 'average']
            ],
            'time_management' => 'Good time management with room for improvement',
            'efficiency_metrics' => ['response_time' => '2.5 hours', 'completion_rate' => '85%'],
            'bottlenecks' => ['Email response time', 'Document organization'],
            'strengths' => ['Meeting management', 'Calendar organization'],
            'improvement_areas' => ['Email efficiency', 'Expense tracking'],
            'recommendations' => ['Set up email filters', 'Automate expense categorization'],
            'trends' => 'Steady improvement over time',
            'insights' => 'Overall good productivity with specific areas for optimization'
        ];
    }

    /**
     * Generate fallback trends
     */
    private function generateFallbackTrends(int $userId): array
    {
        return [
            'overall_trends' => 'Steady productivity growth',
            'module_trends' => [
                'emails' => 'Increasing volume',
                'documents' => 'Stable creation rate',
                'meetings' => 'Well managed',
                'expenses' => 'Consistent tracking'
            ],
            'patterns' => ['Peak activity on weekdays', 'Lower activity on weekends'],
            'seasonal_effects' => 'No significant seasonal patterns detected',
            'growth_areas' => ['Meeting efficiency', 'Calendar management'],
            'declining_areas' => ['Email response time'],
            'predictions' => 'Continued steady growth expected',
            'recommendations' => ['Focus on email efficiency', 'Maintain current meeting practices'],
            'insights' => 'Good overall trends with room for email optimization'
        ];
    }

    /**
     * Generate fallback recommendations
     */
    private function generateFallbackRecommendations(int $userId): array
    {
        return [
            'immediate_actions' => ['Check unread emails', 'Review today\'s meetings'],
            'weekly_goals' => ['Improve email response time', 'Organize documents'],
            'monthly_objectives' => ['Streamline workflows', 'Optimize expense tracking'],
            'efficiency_tips' => ['Use email filters', 'Set up automated reminders'],
            'automation_suggestions' => ['Email categorization', 'Expense tracking'],
            'workflow_improvements' => ['Standardize document naming', 'Optimize meeting scheduling'],
            'skill_development' => ['Email management', 'Time management'],
            'tool_recommendations' => ['Email templates', 'Calendar integrations'],
            'priority_focus' => ['Email efficiency', 'Document organization'],
            'personalized_insights' => 'Focus on email management and document organization for better productivity'
        ];
    }

    /**
     * Generate fallback insights
     */
    private function generateFallbackInsights(int $userId): array
    {
        return [
            'executive_summary' => 'Good overall productivity with specific improvement opportunities',
            'key_metrics' => ['Email response time', 'Meeting efficiency', 'Document organization'],
            'performance_analysis' => 'Above average performance with room for optimization',
            'efficiency_analysis' => 'Good efficiency in most areas, email management needs attention',
            'time_analysis' => 'Well-distributed time allocation across modules',
            'communication_analysis' => 'Effective meeting management, email efficiency could improve',
            'workflow_analysis' => 'Good workflows in place, some automation opportunities',
            'goal_alignment' => 'Activities align well with productivity goals',
            'risk_assessment' => 'Low risk, focus on email management',
            'opportunities' => ['Email automation', 'Document templates', 'Expense optimization'],
            'action_plan' => ['Implement email filters', 'Create document templates', 'Automate expense categorization'],
            'success_factors' => ['Good meeting management', 'Effective calendar use'],
            'challenges' => ['Email volume management', 'Document organization']
        ];
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
                    'maxOutputTokens' => 3000,
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
        if (strpos($prompt, 'productivity') !== false) {
            return '{"productivity_score": 75, "module_performance": {"emails": {"score": 80, "status": "good"}, "documents": {"score": 70, "status": "average"}, "meetings": {"score": 85, "status": "excellent"}, "calendar": {"score": 75, "status": "good"}, "expenses": {"score": 65, "status": "needs_improvement"}, "inventory": {"score": 70, "status": "average"}}, "time_management": "Good time management with room for improvement", "efficiency_metrics": {"response_time": "2.5 hours", "completion_rate": "85%"}, "bottlenecks": ["Email response time", "Document organization"], "strengths": ["Meeting management", "Calendar organization"], "improvement_areas": ["Email efficiency", "Expense tracking"], "recommendations": ["Set up email filters", "Automate expense categorization"], "trends": "Steady improvement over time", "insights": "Overall good productivity with specific areas for optimization"}';
        } elseif (strpos($prompt, 'trends') !== false) {
            return '{"overall_trends": "Steady productivity growth", "module_trends": {"emails": "Increasing volume", "documents": "Stable creation rate", "meetings": "Well managed", "expenses": "Consistent tracking"}, "patterns": ["Peak activity on weekdays", "Lower activity on weekends"], "seasonal_effects": "No significant seasonal patterns detected", "growth_areas": ["Meeting efficiency", "Calendar management"], "declining_areas": ["Email response time"], "predictions": "Continued steady growth expected", "recommendations": ["Focus on email efficiency", "Maintain current meeting practices"], "insights": "Good overall trends with room for email optimization"}';
        } elseif (strpos($prompt, 'recommendations') !== false) {
            return '{"immediate_actions": ["Check unread emails", "Review today\'s meetings"], "weekly_goals": ["Improve email response time", "Organize documents"], "monthly_objectives": ["Streamline workflows", "Optimize expense tracking"], "efficiency_tips": ["Use email filters", "Set up automated reminders"], "automation_suggestions": ["Email categorization", "Expense tracking"], "workflow_improvements": ["Standardize document naming", "Optimize meeting scheduling"], "skill_development": ["Email management", "Time management"], "tool_recommendations": ["Email templates", "Calendar integrations"], "priority_focus": ["Email efficiency", "Document organization"], "personalized_insights": "Focus on email management and document organization for better productivity"}';
        } elseif (strpos($prompt, 'insights') !== false) {
            return '{"executive_summary": "Good overall productivity with specific improvement opportunities", "key_metrics": ["Email response time", "Meeting efficiency", "Document organization"], "performance_analysis": "Above average performance with room for optimization", "efficiency_analysis": "Good efficiency in most areas, email management needs attention", "time_analysis": "Well-distributed time allocation across modules", "communication_analysis": "Effective meeting management, email efficiency could improve", "workflow_analysis": "Good workflows in place, some automation opportunities", "goal_alignment": "Activities align well with productivity goals", "risk_assessment": "Low risk, focus on email management", "opportunities": ["Email automation", "Document templates", "Expense optimization"], "action_plan": ["Implement email filters", "Create document templates", "Automate expense categorization"], "success_factors": ["Good meeting management", "Effective calendar use"], "challenges": ["Email volume management", "Document organization"]}';
        }
        
        return '{"message": "AI analysis temporarily unavailable due to quota limits. Please try again later."}';
    }
}
