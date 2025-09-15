@extends('layouts.app')

@section('title', 'Dashboard - SecretaryAI')
@section('page-title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-2">Welcome back, {{ Auth::user()->name ?? 'User' }}! 👋</h1>
        <p class="text-muted">Here's what's happening with your AI Secretary today</p>
    </div>
    <div class="btn-group" role="group">
        <button class="btn btn-outline-primary" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
        </button>
        <button class="btn btn-info" onclick="getProductivityAnalytics()">
            <i class="bi bi-graph-up me-2"></i>AI Analytics
        </button>
        <button class="btn btn-warning" onclick="showTrendsModal()">
            <i class="bi bi-trending-up me-2"></i>Trend Analysis
        </button>
        <button class="btn btn-success" onclick="getSmartRecommendations()">
            <i class="bi bi-lightbulb me-2"></i>AI Recommendations
        </button>
        <button class="btn btn-primary" onclick="getComprehensiveInsights()">
            <i class="bi bi-robot me-2"></i>AI Insights
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">{{ $stats['unread_emails'] }}</div>
                    <div class="stats-label">Unread Emails</div>
                    <div class="stats-subtitle">
                        @if($stats['unread_emails'] > 0)
                            {{ $stats['unread_emails'] }} new message{{ $stats['unread_emails'] > 1 ? 's' : '' }}
                        @else
                            All caught up!
                        @endif
                    </div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-envelope"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">{{ $stats['todays_events'] }}</div>
                    <div class="stats-label">Today's Events</div>
                    <div class="stats-subtitle">
                        @if($stats['todays_events'] > 0)
                            {{ $stats['todays_events'] }} event{{ $stats['todays_events'] > 1 ? 's' : '' }} scheduled
                        @else
                            No events today
                        @endif
                    </div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">{{ $stats['pending_expenses'] }}</div>
                    <div class="stats-label">Pending Expenses</div>
                    <div class="stats-subtitle">
                        @if($stats['pending_expenses'] > 0)
                            {{ $stats['pending_expenses'] }} awaiting approval
                        @else
                            All expenses processed
                        @endif
                    </div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-file-text"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">{{ $stats['total_documents'] }}</div>
                    <div class="stats-label">Total Documents</div>
                    <div class="stats-subtitle">
                        @if($stats['total_documents'] > 0)
                            {{ $stats['total_documents'] }} document{{ $stats['total_documents'] > 1 ? 's' : '' }} created
                        @else
                            No documents yet
                        @endif
                    </div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cpu"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('meetings.live.recording') }}" class="quick-action-btn">
                            <div class="icon">
                                <i class="bi bi-record-circle text-danger"></i>
                            </div>
                            <div class="title">Live Recording</div>
                            <div class="subtitle">Record meeting now</div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('meetings.create') }}" class="quick-action-btn">
                            <div class="icon">
                                <i class="bi bi-calendar-plus text-primary"></i>
                            </div>
                            <div class="title">Schedule Meeting</div>
                            <div class="subtitle">Plan ahead</div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('emails.create') }}" class="quick-action-btn">
                            <div class="icon">
                                <i class="bi bi-envelope-plus text-success"></i>
                            </div>
                            <div class="title">Compose Email</div>
                            <div class="subtitle">Send message</div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('documents.create') }}" class="quick-action-btn">
                            <div class="icon">
                                <i class="bi bi-file-plus text-info"></i>
                            </div>
                            <div class="title">Create Document</div>
                            <div class="subtitle">New file</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Recent Activity</h6>
            </div>
            <div class="card-body">
                @if($recentEmails->count() > 0)
                    @foreach($recentEmails->take(3) as $email)
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ Str::limit($email->subject, 50) }}</div>
                            <div class="activity-time">{{ $email->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                @endif
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Created meeting from email thread</div>
                        <div class="activity-time">15 minutes ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Completed transcription for Board Meeting</div>
                        <div class="activity-time">1 hour ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Generated meeting minutes document</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('emails.compose') }}" class="quick-action-btn">
                            <i class="bi bi-pencil-square icon"></i>
                            <div class="title">Compose Email</div>
                            <div class="subtitle">Draft with AI assistance</div>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="{{ route('calendar.index') }}" class="quick-action-btn">
                            <i class="bi bi-camera-video icon"></i>
                            <div class="title">Schedule Event</div>
                            <div class="subtitle">Create with Meet link</div>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="{{ route('meetings.upload') }}" class="quick-action-btn">
                            <i class="bi bi-cloud-upload icon"></i>
                            <div class="title">Upload Meeting</div>
                            <div class="subtitle">Audio transcription</div>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="{{ route('documents.create') }}" class="quick-action-btn">
                            <i class="bi bi-magic icon"></i>
                            <div class="title">New Document</div>
                            <div class="subtitle">AI-assisted drafting</div>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="{{ route('ai.dashboard') }}" class="quick-action-btn">
                            <i class="bi bi-cpu icon"></i>
                            <div class="title">AI Tools</div>
                            <div class="subtitle">Transcribe, OCR, Generate</div>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="{{ route('expenses.index') }}" class="quick-action-btn">
                            <i class="bi bi-receipt icon"></i>
                            <div class="title">Expenses</div>
                            <div class="subtitle">Track & categorize</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Refresh data every 30 seconds
    setInterval(loadDashboardData, 30000);
});

function loadDashboardData() {
    // Load stats
    fetch('/api/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('unread-emails').textContent = data.data.emails.unread || 12;
                document.getElementById('todays-meetings').textContent = data.data.meetings.today || 5;
                document.getElementById('pending-documents').textContent = data.data.documents.pending || 8;
                document.getElementById('ai-tasks').textContent = data.data.inventory.low_stock || 15;
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            // Keep default values if API fails
        });
}

function refreshDashboard() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refreshing...';
    button.disabled = true;
    
    loadDashboardData();
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// AI Dashboard Features
function getProductivityAnalytics() {
    // Show loading state
    const analyticsBtn = document.querySelector('[onclick="getProductivityAnalytics()"]');
    const originalText = analyticsBtn.innerHTML;
    analyticsBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    analyticsBtn.disabled = true;
    
    fetch('{{ route("dashboard.ai.analytics") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAnalyticsModal(data.analytics);
        } else {
            alert('Failed to get analytics: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting analytics. Please try again.');
    })
    .finally(() => {
        // Reset button state
        analyticsBtn.innerHTML = originalText;
        analyticsBtn.disabled = false;
    });
}

function showTrendsModal() {
    const modalHtml = `
        <div class="modal fade" id="trendsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Trend Analysis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="trendsForm">
                            <div class="mb-3">
                                <label for="timeframe" class="form-label">Analysis Timeframe</label>
                                <select class="form-select" id="timeframe" name="timeframe">
                                    <option value="7 days">7 Days</option>
                                    <option value="30 days" selected>30 Days</option>
                                    <option value="90 days">90 Days</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Focus Areas</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="emails" name="focus_areas[]" value="emails" checked>
                                            <label class="form-check-label" for="emails">Emails</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="documents" name="focus_areas[]" value="documents" checked>
                                            <label class="form-check-label" for="documents">Documents</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="meetings" name="focus_areas[]" value="meetings" checked>
                                            <label class="form-check-label" for="meetings">Meetings</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="calendar" name="focus_areas[]" value="calendar" checked>
                                            <label class="form-check-label" for="calendar">Calendar</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="expenses" name="focus_areas[]" value="expenses" checked>
                                            <label class="form-check-label" for="expenses">Expenses</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="inventory" name="focus_areas[]" value="inventory" checked>
                                            <label class="form-check-label" for="inventory">Inventory</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" onclick="runTrendAnalysis()">
                            <i class="bi bi-trending-up me-2"></i>Analyze Trends
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('trendsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('trendsModal'));
    modal.show();
}

function runTrendAnalysis() {
    const form = document.getElementById('trendsForm');
    const formData = new FormData(form);
    
    // Get checkbox values
    const focusAreas = Array.from(document.querySelectorAll('input[name="focus_areas[]"]:checked')).map(cb => cb.value);
    
    formData.append('focus_areas', JSON.stringify(focusAreas));
    
    // Show loading state
    const trendsBtn = document.querySelector('[onclick="runTrendAnalysis()"]');
    const originalText = trendsBtn.innerHTML;
    trendsBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    trendsBtn.disabled = true;
    
    fetch('{{ route("dashboard.ai.trends") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTrendsResults(data.trends);
        } else {
            alert('Failed to analyze trends: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error analyzing trends. Please try again.');
    })
    .finally(() => {
        // Reset button state
        trendsBtn.innerHTML = originalText;
        trendsBtn.disabled = false;
    });
}

function getSmartRecommendations() {
    // Show loading state
    const recommendationsBtn = document.querySelector('[onclick="getSmartRecommendations()"]');
    const originalText = recommendationsBtn.innerHTML;
    recommendationsBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    recommendationsBtn.disabled = true;
    
    fetch('{{ route("dashboard.ai.recommendations") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showRecommendationsModal(data.recommendations);
        } else {
            alert('Failed to get recommendations: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting recommendations. Please try again.');
    })
    .finally(() => {
        // Reset button state
        recommendationsBtn.innerHTML = originalText;
        recommendationsBtn.disabled = false;
    });
}

function getComprehensiveInsights() {
    // Show loading state
    const insightsBtn = document.querySelector('[onclick="getComprehensiveInsights()"]');
    const originalText = insightsBtn.innerHTML;
    insightsBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    insightsBtn.disabled = true;
    
    fetch('{{ route("dashboard.ai.insights") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showInsightsModal(data.insights);
        } else {
            alert('Failed to get insights: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting insights. Please try again.');
    })
    .finally(() => {
        // Reset button state
        insightsBtn.innerHTML = originalText;
        insightsBtn.disabled = false;
    });
}

function showAnalyticsModal(analytics) {
    const modalHtml = `
        <div class="modal fade" id="analyticsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Productivity Analytics</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h2 class="display-4 text-primary">${analytics.productivity_score || 0}</h2>
                                        <p class="text-muted">Overall Productivity Score</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Time Management</h6>
                                        <p class="mb-0">${analytics.time_management || 'Analysis not available'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Module Performance</h6>
                            <div class="row">
                                ${Object.entries(analytics.module_performance || {}).map(([module, data]) => `
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title text-capitalize">${module}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-${data.score > 80 ? 'success' : data.score > 60 ? 'warning' : 'danger'}">
                                                        ${data.score || 0}/100
                                                    </span>
                                                    <small class="text-muted">${data.status || 'Unknown'}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Strengths</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(analytics.strengths || []).map(strength => `<li class="mb-1"><i class="bi bi-check-circle me-2 text-success"></i>${strength}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Improvement Areas</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(analytics.improvement_areas || []).map(area => `<li class="mb-1"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>${area}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${analytics.recommendations && analytics.recommendations.length > 0 ? `
                            <div class="mt-4">
                                <h6>Recommendations</h6>
                                <ul class="list-unstyled">
                                    ${analytics.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${rec}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('analyticsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('analyticsModal'));
    modal.show();
}

function showTrendsResults(trends) {
    const modalHtml = `
        <div class="modal fade" id="trendsResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Trend Analysis Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <h6>Overall Trends</h6>
                            <p>${trends.overall_trends || 'No trends available'}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Module Trends</h6>
                            <div class="row">
                                ${Object.entries(trends.module_trends || {}).map(([module, trend]) => `
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title text-capitalize">${module}</h6>
                                                <p class="mb-0">${trend}</p>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Growth Areas</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(trends.growth_areas || []).map(area => `<li class="mb-1"><i class="bi bi-arrow-up me-2 text-success"></i>${area}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Declining Areas</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(trends.declining_areas || []).map(area => `<li class="mb-1"><i class="bi bi-arrow-down me-2 text-danger"></i>${area}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${trends.recommendations && trends.recommendations.length > 0 ? `
                            <div class="mt-4">
                                <h6>Recommendations</h6>
                                <ul class="list-unstyled">
                                    ${trends.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${rec}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('trendsResultsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('trendsResultsModal'));
    modal.show();
}

function showRecommendationsModal(recommendations) {
    const modalHtml = `
        <div class="modal fade" id="recommendationsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Smart Recommendations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Immediate Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(recommendations.immediate_actions || []).map(action => `<li class="mb-1"><i class="bi bi-arrow-right me-2 text-primary"></i>${action}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Weekly Goals</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(recommendations.weekly_goals || []).map(goal => `<li class="mb-1"><i class="bi bi-calendar-week me-2 text-info"></i>${goal}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Efficiency Tips</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(recommendations.efficiency_tips || []).map(tip => `<li class="mb-1"><i class="bi bi-lightbulb me-2 text-warning"></i>${tip}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Automation Suggestions</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(recommendations.automation_suggestions || []).map(suggestion => `<li class="mb-1"><i class="bi bi-gear me-2 text-success"></i>${suggestion}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${recommendations.personalized_insights ? `
                            <div class="mt-4">
                                <h6>Personalized Insights</h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-robot me-2"></i>${recommendations.personalized_insights}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('recommendationsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('recommendationsModal'));
    modal.show();
}

function showInsightsModal(insights) {
    const modalHtml = `
        <div class="modal fade" id="insightsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Comprehensive Insights</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <h6>Executive Summary</h6>
                            <div class="alert alert-primary">
                                <i class="bi bi-info-circle me-2"></i>${insights.executive_summary || 'No summary available'}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Key Metrics</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre class="mb-0">${JSON.stringify(insights.key_metrics || {}, null, 2)}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Performance Analysis</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">${insights.performance_analysis || 'No analysis available'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Opportunities</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(insights.opportunities || []).map(opportunity => `<li class="mb-1"><i class="bi bi-arrow-up me-2 text-success"></i>${opportunity}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Challenges</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            ${(insights.challenges || []).map(challenge => `<li class="mb-1"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>${challenge}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${insights.action_plan && insights.action_plan.length > 0 ? `
                            <div class="mt-4">
                                <h6>Action Plan</h6>
                                <ul class="list-unstyled">
                                    ${insights.action_plan.map(action => `<li class="mb-1"><i class="bi bi-check-square me-2"></i>${action}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('insightsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('insightsModal'));
    modal.show();
}
</script>
@endsection
