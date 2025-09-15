@extends('layouts.app')

@section('title', 'Emails - SecretaryAI')
@section('page-title', 'Email Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('emails.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ Compose Email
    </a>
    <button type="button" class="btn btn-outline-info" onclick="analyzeAllEmails()">
        <i class="bi bi-robot me-2"></i>AI Analyze All
    </button>
    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Filters</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search emails...">
                </div>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="draft">Draft</button>
                    <button type="button" class="btn btn-outline-success" data-filter="sent">Sent</button>
                    <button type="button" class="btn btn-outline-info" data-filter="important">Important</button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary">{{ $emails->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-warning">{{ $emails->where('status', 'draft')->count() }}</div>
                        <small class="text-muted">Drafts</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success">{{ $emails->where('status', 'sent')->count() }}</div>
                        <small class="text-muted">Sent</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info">{{ $emails->where('is_important', true)->count() }}</div>
                        <small class="text-muted">Important</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Insights -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-robot me-2"></i>AI Insights
                </h6>
            </div>
            <div class="card-body">
                <div id="ai-insights">
                    <div class="text-center">
                        <button class="btn btn-outline-info btn-sm" onclick="loadAIInsights()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Load AI Insights
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Emails List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Emails</h6>
            </div>
            <div class="card-body p-0">
                @if($emails->count() > 0)
                    @foreach($emails as $email)
                    <div class="email-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $email->subject }}</h6>
                                    @if($email->is_important)
                                        <i class="bi bi-star-fill text-warning"></i>
                                    @endif
                                    <span class="badge {{ $email->status === 'sent' ? 'bg-success' : ($email->status === 'draft' ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ ucfirst($email->status) }}
                                    </span>
                                    @if($email->ai_category)
                                        <span class="badge bg-info">{{ ucfirst($email->ai_category) }}</span>
                                    @endif
                                    @if($email->ai_priority)
                                        <span class="badge {{ $email->ai_priority === 'high' ? 'bg-danger' : ($email->ai_priority === 'medium' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ ucfirst($email->ai_priority) }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-person me-1"></i>From: {{ $email->from_email }}
                                    <i class="bi bi-person-check me-1 ms-2"></i>To: {{ $email->to_email }}
                                </p>
                                <p class="text-muted small mb-0">{{ Str::limit($email->content, 100) }}</p>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">{{ $email->created_at->format('M j, Y') }}</div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('emails.show', $email) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('emails.edit', $email) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(!$email->ai_category)
                                        <button class="btn btn-outline-info btn-sm" onclick="analyzeEmail({{ $email->id }})" title="AI Analyze">
                                            <i class="bi bi-robot"></i>
                                        </button>
                                    @endif
                                    @if($email->ai_category)
                                        <button class="btn btn-outline-warning btn-sm" onclick="getReplySuggestions({{ $email->id }})" title="AI Reply Suggestions">
                                            <i class="bi bi-reply"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-envelope text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No emails found</p>
                        <a href="{{ route('emails.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Compose Your First Email
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Simple JavaScript for basic functionality
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterEmails();
        });
    }
    
    // Filter buttons
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            
            // Update active state
            document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            filterEmails(filter);
        });
    });
});

function filterEmails(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const emailItems = document.querySelectorAll('.email-item');
    
    emailItems.forEach(item => {
        const subject = item.querySelector('h6').textContent.toLowerCase();
        const content = item.querySelector('.text-muted').textContent.toLowerCase();
        const statusBadge = item.querySelector('.badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase().trim() : '';
        
        const matchesSearch = subject.includes(searchTerm) || content.includes(searchTerm);
        
        let matchesFilter = true;
        if (filter !== 'all') {
            if (filter === 'important') {
                const starIcon = item.querySelector('.bi-star-fill');
                matchesFilter = starIcon !== null;
            } else {
                matchesFilter = status === filter;
            }
        }
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

// AI Functions
function analyzeEmail(emailId) {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch(`/emails/${emailId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show AI data
            window.location.reload();
        } else {
            alert('AI analysis failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('AI analysis failed. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function getReplySuggestions(emailId) {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch(`/emails/${emailId}/suggestions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        if (data.success && data.suggestions && data.suggestions.length > 0) {
            showReplySuggestions(data.suggestions);
        } else {
            console.error('No suggestions received:', data);
            alert('Failed to generate reply suggestions. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate reply suggestions. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function showReplySuggestions(suggestions) {
    console.log('Received suggestions:', suggestions);
    
    if (!suggestions || suggestions.length === 0) {
        alert('No reply suggestions available. Please try again.');
        return;
    }
    
    let modalHtml = `
        <div class="modal fade" id="replySuggestionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Reply Suggestions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
    `;
    
    suggestions.forEach((suggestion, index) => {
        const subject = suggestion.subject || 'Reply';
        const content = suggestion.content || 'No content available';
        
        modalHtml += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Suggestion ${index + 1}</h6>
                </div>
                <div class="card-body">
                    <h6>${subject}</h6>
                    <p>${content}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="useSuggestion(${index})">Use This</button>
                </div>
            </div>
        `;
    });
    
    modalHtml += `
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('replySuggestionsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('replySuggestionsModal'));
    modal.show();
}

function useSuggestion(index) {
    // This would typically open the compose email form with the suggestion
    alert('This would open the compose form with suggestion ' + (index + 1));
}

function analyzeAllEmails() {
    if (!confirm('This will analyze all emails with AI. This may take a while. Continue?')) {
        return;
    }
    
    const button = event.target;
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    button.disabled = true;
    
    // Get all unanalyzed emails (those without AI category badge)
    const allEmailItems = document.querySelectorAll('.email-item');
    const unanalyzedEmails = Array.from(allEmailItems).filter(item => {
        const aiCategoryBadge = item.querySelector('.badge.bg-info');
        return !aiCategoryBadge;
    });
    let processed = 0;
    
    if (unanalyzedEmails.length === 0) {
        alert('All emails have already been analyzed!');
        button.innerHTML = originalContent;
        button.disabled = false;
        return;
    }
    
    unanalyzedEmails.forEach((emailItem, index) => {
        setTimeout(() => {
            const analyzeButton = emailItem.querySelector('button[onclick*="analyzeEmail"]');
            if (analyzeButton) {
                analyzeButton.click();
                processed++;
                
                if (processed === unanalyzedEmails.length) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            }
        }, index * 1000); // Delay each analysis by 1 second
    });
}

function loadAIInsights() {
    const button = event.target;
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';
    button.disabled = true;
    
    fetch('/emails/ai/insights')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAIInsights(data.insights);
        } else {
            alert('Failed to load AI insights.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load AI insights.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function displayAIInsights(insights) {
    const container = document.getElementById('ai-insights');
    
    let html = `
        <div class="mb-3">
            <h6>Analysis Summary</h6>
            <p class="small text-muted">${insights.total_analyzed} emails analyzed</p>
        </div>
    `;
    
    if (insights.categories && Object.keys(insights.categories).length > 0) {
        html += `
            <div class="mb-3">
                <h6>Categories</h6>
                <div class="d-flex flex-wrap gap-1">
        `;
        Object.entries(insights.categories).forEach(([category, count]) => {
            html += `<span class="badge bg-info">${category} (${count})</span>`;
        });
        html += `</div></div>`;
    }
    
    if (insights.priorities && Object.keys(insights.priorities).length > 0) {
        html += `
            <div class="mb-3">
                <h6>Priorities</h6>
                <div class="d-flex flex-wrap gap-1">
        `;
        Object.entries(insights.priorities).forEach(([priority, count]) => {
            const badgeClass = priority === 'high' ? 'bg-danger' : (priority === 'medium' ? 'bg-warning' : 'bg-secondary');
            html += `<span class="badge ${badgeClass}">${priority} (${count})</span>`;
        });
        html += `</div></div>`;
    }
    
    container.innerHTML = html;
}
</script>
@endsection