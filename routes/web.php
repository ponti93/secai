<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Quick login for testing
Route::get('/quick-login', function () {
    $user = \App\Models\User::first();
    if ($user) {
        \Auth::login($user);
        return redirect('/calendar')->with('success', 'Logged in as ' . $user->name);
    }
    return response()->json(['error' => 'No users found']);
});




// Login routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Protected routes - require authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard AI Features
    Route::get('/dashboard/ai/analytics', [App\Http\Controllers\DashboardController::class, 'getProductivityAnalytics'])->name('dashboard.ai.analytics');
    Route::post('/dashboard/ai/trends', [App\Http\Controllers\DashboardController::class, 'analyzeTrends'])->name('dashboard.ai.trends');
    Route::get('/dashboard/ai/recommendations', [App\Http\Controllers\DashboardController::class, 'getSmartRecommendations'])->name('dashboard.ai.recommendations');
    Route::get('/dashboard/ai/insights', [App\Http\Controllers\DashboardController::class, 'getComprehensiveInsights'])->name('dashboard.ai.insights');
    
    // Search
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    
    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/general', [App\Http\Controllers\SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/notifications', [App\Http\Controllers\SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/ai', [App\Http\Controllers\SettingsController::class, 'updateAi'])->name('settings.ai');
    Route::post('/settings/export', [App\Http\Controllers\SettingsController::class, 'exportData'])->name('settings.export');
    Route::post('/settings/cache', [App\Http\Controllers\SettingsController::class, 'clearCache'])->name('settings.cache');

    // Email Management - Web CRUD
    Route::get('/emails', [App\Http\Controllers\EmailController::class, 'index'])->name('emails.index');
    Route::get('/emails/create', [App\Http\Controllers\EmailController::class, 'create'])->name('emails.create');
    Route::post('/emails', [App\Http\Controllers\EmailController::class, 'store'])->name('emails.store');
    Route::get('/emails/{email}', [App\Http\Controllers\EmailController::class, 'show'])->name('emails.show');
    Route::get('/emails/{email}/edit', [App\Http\Controllers\EmailController::class, 'edit'])->name('emails.edit');
    Route::put('/emails/{email}', [App\Http\Controllers\EmailController::class, 'update'])->name('emails.update');
    Route::delete('/emails/{email}', [App\Http\Controllers\EmailController::class, 'destroy'])->name('emails.destroy');
    
    // Email AI Features
    Route::post('/emails/{email}/analyze', [App\Http\Controllers\EmailController::class, 'analyzeEmail'])->name('emails.analyze');
    Route::post('/emails/{email}/suggestions', [App\Http\Controllers\EmailController::class, 'generateReplySuggestions'])->name('emails.suggestions');
    Route::get('/emails/ai/insights', [App\Http\Controllers\EmailController::class, 'getAIInsights'])->name('emails.ai.insights');
    
    // Email AI Composition Features
    Route::post('/emails/ai/generate-subject', [App\Http\Controllers\EmailController::class, 'generateSubject'])->name('emails.ai.generate-subject');
    Route::post('/emails/ai/generate-content', [App\Http\Controllers\EmailController::class, 'generateContent'])->name('emails.ai.generate-content');
    Route::post('/emails/ai/improve-content', [App\Http\Controllers\EmailController::class, 'improveContent'])->name('emails.ai.improve-content');
    Route::post('/emails/ai/grammar-check', [App\Http\Controllers\EmailController::class, 'checkGrammar'])->name('emails.ai.grammar-check');
    
    // Email Test Send
    Route::post('/emails/test-send', [App\Http\Controllers\EmailController::class, 'testSend'])->name('emails.test-send');
    Route::get('/emails/compose', [App\Http\Controllers\EmailController::class, 'create'])->name('emails.compose');

    // Document Management - Web CRUD
    Route::get('/documents', [App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [App\Http\Controllers\DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/edit', [App\Http\Controllers\DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    
    // Document AI Features
    Route::get('/documents/{document}/summarize', [App\Http\Controllers\DocumentController::class, 'summarizeDocument'])->name('documents.summarize');
    Route::post('/documents/ai/generate', [App\Http\Controllers\DocumentController::class, 'generateContent'])->name('documents.ai.generate');
    Route::get('/documents/{document}/keywords', [App\Http\Controllers\DocumentController::class, 'extractKeywords'])->name('documents.keywords');
    Route::get('/documents/{document}/analyze', [App\Http\Controllers\DocumentController::class, 'analyzeDocument'])->name('documents.analyze');

    // Meeting Management - Web CRUD
    Route::get('/meetings', [App\Http\Controllers\MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/create', [App\Http\Controllers\MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [App\Http\Controllers\MeetingController::class, 'store'])->name('meetings.store');
    
    // Audio Upload (specific routes must come before parameterized routes)
    Route::get('/meetings/upload', [App\Http\Controllers\AudioUploadController::class, 'create'])->name('meetings.upload');
    Route::post('/meetings/upload', [App\Http\Controllers\AudioUploadController::class, 'store'])->name('meetings.upload.store');
    
    // Live Recording
    Route::get('/meetings/live-recording', [App\Http\Controllers\LiveRecordingController::class, 'create'])->name('meetings.live.recording');
    Route::post('/meetings/live/start', [App\Http\Controllers\LiveRecordingController::class, 'start'])->name('meetings.live.start');
    Route::post('/meetings/live/save', [App\Http\Controllers\LiveRecordingController::class, 'save'])->name('meetings.live.save');
    Route::post('/meetings/live/stop', [App\Http\Controllers\LiveRecordingController::class, 'stop'])->name('meetings.live.stop');
    Route::get('/meetings/live/status/{meetingId}', [App\Http\Controllers\LiveRecordingController::class, 'status'])->name('meetings.live.status');
    
    // Audio file serving route (fallback for storage issues)
    Route::get('/audio/{path}', function($path) {
        $filePath = storage_path('app/public/' . $path);
        if (file_exists($filePath)) {
            return response()->file($filePath);
        }
        abort(404);
    })->where('path', '.*');
    
    // AI transcription route (web version)
    Route::post('/ai/transcribe-audio', [App\Http\Controllers\AiController::class, 'transcribeAudio'])->name('ai.transcribe-audio');
    
    // AI text generation route (web version)
    Route::post('/ai/generate-text', [App\Http\Controllers\AiController::class, 'generateText'])->name('ai.generate-text');
    
    // Meeting transcription route
    Route::post('/meetings/{meeting}/transcribe', [App\Http\Controllers\MeetingController::class, 'transcribeAudio'])->name('meetings.transcribe');
    
    // Parameterized routes (must come after specific routes)
    Route::get('/meetings/{meeting}', [App\Http\Controllers\MeetingController::class, 'show'])->name('meetings.show');
    Route::get('/meetings/{meeting}/edit', [App\Http\Controllers\MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{meeting}', [App\Http\Controllers\MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [App\Http\Controllers\MeetingController::class, 'destroy'])->name('meetings.destroy');
    Route::get('/meetings/{meeting}/download', [App\Http\Controllers\AudioUploadController::class, 'download'])->name('meetings.download');
    Route::get('/meetings/{meeting}/transcription', [App\Http\Controllers\AudioUploadController::class, 'getTranscription'])->name('meetings.transcription');

    // Calendar Management - Web CRUD
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/create', [App\Http\Controllers\CalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{event}', [App\Http\Controllers\CalendarController::class, 'show'])->name('calendar.show');
    Route::get('/calendar/{event}/edit', [App\Http\Controllers\CalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendar/{event}', [App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.destroy');
    
    // Calendar AI Features
    Route::post('/calendar/ai/suggest-times', [App\Http\Controllers\CalendarController::class, 'suggestMeetingTimes'])->name('calendar.ai.suggest-times');
    Route::get('/calendar/{event}/conflicts', [App\Http\Controllers\CalendarController::class, 'detectConflicts'])->name('calendar.conflicts');
    Route::get('/calendar/{event}/follow-up', [App\Http\Controllers\CalendarController::class, 'generateFollowUpSuggestions'])->name('calendar.follow-up');
    Route::post('/calendar/ai/optimize', [App\Http\Controllers\CalendarController::class, 'optimizeSchedule'])->name('calendar.ai.optimize');
    

    // Inventory Management - Web CRUD
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{item}', [App\Http\Controllers\InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [App\Http\Controllers\InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [App\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');
    
    // Inventory AI Features
    Route::post('/inventory/ai/forecast', [App\Http\Controllers\InventoryController::class, 'forecastDemand'])->name('inventory.ai.forecast');
    Route::get('/inventory/ai/reorder-suggestions', [App\Http\Controllers\InventoryController::class, 'generateReorderSuggestions'])->name('inventory.ai.reorder-suggestions');
    Route::post('/inventory/ai/optimize-pricing', [App\Http\Controllers\InventoryController::class, 'optimizePricing'])->name('inventory.ai.optimize-pricing');
    Route::get('/inventory/ai/insights', [App\Http\Controllers\InventoryController::class, 'generateInsights'])->name('inventory.ai.insights');

    // Expense Management - Web CRUD
    Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{expense}/edit', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    
    // Expense AI Features
    Route::post('/expenses/process-receipt', [App\Http\Controllers\ExpenseController::class, 'processReceipt'])->name('expenses.process-receipt');
    Route::post('/expenses/categorize', [App\Http\Controllers\ExpenseController::class, 'categorizeExpense'])->name('expenses.categorize');
    Route::get('/expenses/ai/insights', [App\Http\Controllers\ExpenseController::class, 'getInsights'])->name('expenses.ai.insights');
    Route::get('/expenses/{expense}/fraud-check', [App\Http\Controllers\ExpenseController::class, 'detectFraud'])->name('expenses.fraud-check');

    // AI Tools
    Route::get('/ai', function () {
        return view('ai.dashboard');
    })->name('ai.dashboard');

});
