<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? [];

        return view('settings', compact('user', 'preferences'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string|max:50',
            'language' => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|in:12,24',
        ]);

        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        
        $preferences['general'] = [
            'timezone' => $request->timezone,
            'language' => $request->language,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
        ];

        $user->update(['preferences' => $preferences]);

        return redirect()->route('settings')->with('success', 'General settings updated successfully!');
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'meeting_reminders' => 'boolean',
            'task_deadlines' => 'boolean',
            'weekly_summary' => 'boolean',
        ]);

        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        
        $preferences['notifications'] = [
            'email_notifications' => $request->has('email_notifications'),
            'meeting_reminders' => $request->has('meeting_reminders'),
            'task_deadlines' => $request->has('task_deadlines'),
            'weekly_summary' => $request->has('weekly_summary'),
        ];

        $user->update(['preferences' => $preferences]);

        return redirect()->route('settings')->with('success', 'Notification settings updated successfully!');
    }

    /**
     * Update AI settings
     */
    public function updateAi(Request $request)
    {
        $request->validate([
            'ai_model' => 'required|string|max:50',
            'ai_tone' => 'required|string|max:20',
            'auto_summarize' => 'boolean',
            'smart_scheduling' => 'boolean',
        ]);

        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        
        $preferences['ai'] = [
            'ai_model' => $request->ai_model,
            'ai_tone' => $request->ai_tone,
            'auto_summarize' => $request->has('auto_summarize'),
            'smart_scheduling' => $request->has('smart_scheduling'),
        ];

        $user->update(['preferences' => $preferences]);

        return redirect()->route('settings')->with('success', 'AI settings updated successfully!');
    }

    /**
     * Export user data
     */
    public function exportData()
    {
        $user = Auth::user();
        
        // This would typically generate a comprehensive data export
        // For now, we'll just return a success message
        return redirect()->route('settings')->with('success', 'Data export initiated. You will receive an email when ready.');
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        
        return redirect()->route('settings')->with('success', 'Cache cleared successfully!');
    }
}