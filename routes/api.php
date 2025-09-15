<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // User management
    Route::apiResource('users', UserController::class);
    
    // Note: Basic CRUD operations are now handled via web routes
    // Only AI-specific and API-only features remain here
    
    // Calendar AI Features
    Route::get('/calendar-ai/scheduling-suggestions', [App\Http\Controllers\CalendarAiController::class, 'getSchedulingSuggestions']);
    Route::get('/calendar-ai/analyze-patterns', [App\Http\Controllers\CalendarAiController::class, 'analyzePatterns']);
    Route::post('/calendar-ai/detect-conflicts', [App\Http\Controllers\CalendarAiController::class, 'detectConflicts']);
    Route::get('/calendar-ai/optimize-meetings', [App\Http\Controllers\CalendarAiController::class, 'optimizeMeetingTimes']);
    Route::get('/calendar-ai/productivity-insights', [App\Http\Controllers\CalendarAiController::class, 'generateProductivityInsights']);
    Route::get('/calendar-ai/analytics', [App\Http\Controllers\CalendarAiController::class, 'getAnalytics']);
    
    // Google Calendar API
    Route::get('/calendar/google/events', [App\Http\Controllers\GoogleCalendarController::class, 'getGoogleEvents']);
    Route::post('/calendar/google/events', [App\Http\Controllers\GoogleCalendarController::class, 'createGoogleEvent']);
    Route::post('/calendar/google/sync', [App\Http\Controllers\GoogleCalendarController::class, 'syncEvents']);
    
// Expense AI Features (keep only the AI-specific routes)
Route::post('/expenses/process-receipt', [App\Http\Controllers\ExpenseAiController::class, 'processReceipt']);
Route::post('/expenses/categorize', [App\Http\Controllers\ExpenseAiController::class, 'categorizeExpense']);
Route::post('/expenses/detect-fraud', [App\Http\Controllers\ExpenseAiController::class, 'detectFraud']);
Route::get('/expenses/budget', [App\Http\Controllers\ExpenseAiController::class, 'getBudgetAnalysis']);
Route::post('/expenses/budget', [App\Http\Controllers\ExpenseAiController::class, 'setBudget']);
Route::get('/expenses/analytics', [App\Http\Controllers\ExpenseAiController::class, 'getAnalytics']);
Route::get('/expenses/export/{format}', [App\Http\Controllers\ExpenseAiController::class, 'exportReport']);
    
    // AI services
    Route::prefix('ai')->group(function () {
        Route::post('/email-reply', [AiController::class, 'generateEmailReply']);
        Route::post('/generate-document', [AiController::class, 'generateDocument']);
        Route::post('/summarize-transcript', [AiController::class, 'summarizeTranscript']);
        Route::post('/extract-action-items', [AiController::class, 'extractActionItems']);
        Route::post('/generate-agenda', [AiController::class, 'generateAgenda']);
        Route::post('/categorize-expense', [AiController::class, 'categorizeExpense']);
        Route::post('/inventory-suggestions', [AiController::class, 'getInventorySuggestions']);
        Route::post('/email-template', [AiController::class, 'generateEmailTemplate']);
        Route::post('/analyze', [AiController::class, 'analyzeContent']);
        
        // Enhanced Gemini features
        Route::post('/transcribe-audio', [AiController::class, 'transcribeAudio']);
        Route::post('/ocr', [AiController::class, 'performOCR']);
        Route::post('/generate-text', [AiController::class, 'generateText']);
        Route::post('/meeting-notes', [AiController::class, 'generateMeetingNotes']);
        Route::post('/audio-to-document', [AiController::class, 'generateDocumentFromAudio']);
        Route::post('/analyze-image', [AiController::class, 'analyzeImage']);
        
        Route::get('/models', [AiController::class, 'getAvailableModels']);
        Route::get('/test', [AiController::class, 'testConnection']);
        Route::get('/search', [AiController::class, 'search']);
        Route::get('/stats', [AiController::class, 'getStats']);
    });
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'getRecentActivity']);
    Route::get('/dashboard/quick-actions', [DashboardController::class, 'getQuickActions']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'AI Secretary API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
