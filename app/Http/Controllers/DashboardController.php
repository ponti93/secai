<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\CalendarEvent;
use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real data
     */
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get real data for dashboard
        $stats = [
            'unread_emails' => Email::where('user_id', $userId)
                ->where('is_read', false)
                ->count(),
            
            'todays_events' => CalendarEvent::where('user_id', $userId)
                ->whereDate('start_time', $today)
                ->count(),
            
            'pending_expenses' => Expense::where('user_id', $userId)
                ->where('status', 'pending')
                ->count(),
            
            'total_documents' => Document::where('user_id', $userId)
                ->count(),
            
            'upcoming_meetings' => Meeting::where('user_id', $userId)
                ->where('start_time', '>', now())
                ->where('start_time', '<=', now()->addDays(7))
                ->count(),
            
            'low_stock_items' => Inventory::where('user_id', $userId)
                ->where('needs_reorder', true)
                ->count()
        ];

        // Get recent activities
        $recentEmails = Email::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'subject', 'from_email', 'created_at', 'is_read']);

        $recentDocuments = Document::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'type', 'created_at']);

        $upcomingMeetings = Meeting::where('user_id', $userId)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get(['id', 'title', 'start_time', 'location']);

        $recentExpenses = Expense::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'description', 'amount', 'status', 'created_at']);

        // Get chart data for expenses by category
        $expenseChartData = Expense::where('user_id', $userId)
            ->where('created_at', '>=', $thisMonth)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        // Get chart data for emails by month
        $emailChartData = Email::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        return view('dashboard', compact(
            'stats',
            'recentEmails',
            'recentDocuments',
            'upcomingMeetings',
            'recentExpenses',
            'expenseChartData',
            'emailChartData'
        ));
    }

    /**
     * Generate productivity analytics using AI
     */
    public function getProductivityAnalytics()
    {
        try {
            $aiService = new \App\Services\AIDashboardService();
            $result = $aiService->generateProductivityAnalytics(Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating productivity analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze trends using AI
     */
    public function analyzeTrends(Request $request)
    {
        $request->validate([
            'timeframe' => 'nullable|string|in:7 days,30 days,90 days',
            'focus_areas' => 'nullable|array'
        ]);

        try {
            $aiService = new \App\Services\AIDashboardService();
            
            $options = [
                'timeframe' => $request->timeframe ?? '30 days',
                'focus_areas' => $request->focus_areas ?? ['all']
            ];
            
            $result = $aiService->analyzeTrends(Auth::id(), $options);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing trends: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate smart recommendations using AI
     */
    public function getSmartRecommendations()
    {
        try {
            $aiService = new \App\Services\AIDashboardService();
            $result = $aiService->generateSmartRecommendations(Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating recommendations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive insights using AI
     */
    public function getComprehensiveInsights()
    {
        try {
            $aiService = new \App\Services\AIDashboardService();
            $result = $aiService->generateComprehensiveInsights(Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating insights: ' . $e->getMessage()
            ], 500);
        }
    }
}