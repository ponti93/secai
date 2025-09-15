@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Settings</h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.general') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                        <option value="UTC" {{ old('timezone', $preferences['general']['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ old('timezone', $preferences['general']['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                        <option value="America/Chicago" {{ old('timezone', $preferences['general']['timezone'] ?? '') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                        <option value="America/Denver" {{ old('timezone', $preferences['general']['timezone'] ?? '') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                        <option value="America/Los_Angeles" {{ old('timezone', $preferences['general']['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    </select>
                                    @error('timezone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                                        <option value="en" {{ old('language', $preferences['general']['language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="es" {{ old('language', $preferences['general']['language'] ?? '') == 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ old('language', $preferences['general']['language'] ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                                        <option value="de" {{ old('language', $preferences['general']['language'] ?? '') == 'de' ? 'selected' : '' }}>German</option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_format" class="form-label">Date Format</label>
                                    <select class="form-select" id="date_format" name="date_format">
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_format" class="form-label">Time Format</label>
                                    <select class="form-select" id="time_format" name="time_format">
                                        <option value="24">24 Hour</option>
                                        <option value="12">12 Hour</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Email Notifications</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.notifications') }}" method="POST">
                        @csrf
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" {{ old('email_notifications', $preferences['notifications']['email_notifications'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notifications">
                                Enable email notifications
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="meeting_reminders" name="meeting_reminders" {{ old('meeting_reminders', $preferences['notifications']['meeting_reminders'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="meeting_reminders">
                                Meeting reminders
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="task_deadlines" name="task_deadlines" {{ old('task_deadlines', $preferences['notifications']['task_deadlines'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="task_deadlines">
                                Task deadline alerts
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="weekly_summary" name="weekly_summary" {{ old('weekly_summary', $preferences['notifications']['weekly_summary'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="weekly_summary">
                                Weekly summary reports
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Notification Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">AI Assistant Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.ai') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="ai_model" class="form-label">AI Model</label>
                            <select class="form-select @error('ai_model') is-invalid @enderror" id="ai_model" name="ai_model">
                                <option value="gemini-1.5-flash" {{ old('ai_model', $preferences['ai']['ai_model'] ?? 'gemini-1.5-flash') == 'gemini-1.5-flash' ? 'selected' : '' }}>Gemini 1.5 Flash</option>
                                <option value="gemini-1.5-pro" {{ old('ai_model', $preferences['ai']['ai_model'] ?? '') == 'gemini-1.5-pro' ? 'selected' : '' }}>Gemini 1.5 Pro</option>
                            </select>
                            @error('ai_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="ai_tone" class="form-label">AI Response Tone</label>
                            <select class="form-select @error('ai_tone') is-invalid @enderror" id="ai_tone" name="ai_tone">
                                <option value="professional" {{ old('ai_tone', $preferences['ai']['ai_tone'] ?? 'professional') == 'professional' ? 'selected' : '' }}>Professional</option>
                                <option value="casual" {{ old('ai_tone', $preferences['ai']['ai_tone'] ?? '') == 'casual' ? 'selected' : '' }}>Casual</option>
                                <option value="friendly" {{ old('ai_tone', $preferences['ai']['ai_tone'] ?? '') == 'friendly' ? 'selected' : '' }}>Friendly</option>
                                <option value="formal" {{ old('ai_tone', $preferences['ai']['ai_tone'] ?? '') == 'formal' ? 'selected' : '' }}>Formal</option>
                            </select>
                            @error('ai_tone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_summarize" name="auto_summarize" {{ old('auto_summarize', $preferences['ai']['auto_summarize'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_summarize">
                                Auto-summarize meetings
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="smart_scheduling" name="smart_scheduling" {{ old('smart_scheduling', $preferences['ai']['smart_scheduling'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="smart_scheduling">
                                Smart scheduling suggestions
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save AI Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Google Calendar</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Connection Status:</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-clockwise me-2"></i>Sync Now
                        </button>
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-2"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Data & Privacy</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('settings.export') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                <i class="bi bi-download me-2"></i>Export Data
                            </button>
                        </form>
                        <form action="{{ route('settings.cache') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-trash me-2"></i>Clear Cache
                            </button>
                        </form>
                        <button class="btn btn-outline-danger btn-sm" onclick="alert('Account deletion not implemented yet')">
                            <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
