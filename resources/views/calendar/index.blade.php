@extends('layouts.app')

@section('title', 'Calendar - SecretaryAI')
@section('page-title', 'Calendar Events')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('calendar.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ Add Event
    </a>
    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
    <button type="button" class="btn btn-info" onclick="showAISuggestTimesModal()">
        <i class="bi bi-robot me-2"></i>AI Suggest Times
    </button>
    <button type="button" class="btn btn-warning" onclick="optimizeSchedule()">
        <i class="bi bi-graph-up me-2"></i>Optimize Schedule
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
                    <input type="text" class="form-control" id="searchInput" placeholder="Search events...">
                </div>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-success" data-filter="scheduled">Scheduled</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="in-progress">In Progress</button>
                    <button type="button" class="btn btn-outline-info" data-filter="completed">Completed</button>
                    <button type="button" class="btn btn-outline-secondary" data-filter="cancelled">Cancelled</button>
                </div>
            </div>
        </div>

        <!-- Google Calendar Status -->

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary">{{ $events->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-success">{{ $events->where('status', 'scheduled')->count() }}</div>
                        <small class="text-muted">Scheduled</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-info">{{ $events->where('status', 'completed')->count() }}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning">{{ $events->where('status', 'in-progress')->count() }}</div>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Calendar View -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Calendar View</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="changeMonth(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="changeMonth(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="calendar-container">
                    <!-- Calendar will be generated here by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Upcoming Events</h6>
            </div>
            <div class="card-body p-0">
                @if($events->count() > 0)
                    @foreach($events->take(5) as $event)
                    <div class="event-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $event->title }}</h6>
                                    @if($event->all_day)
                                        <span class="badge bg-info">All Day</span>
                                    @endif
                                    <span class="badge {{ $event->status === 'completed' ? 'bg-success' : ($event->status === 'scheduled' ? 'bg-primary' : ($event->status === 'in-progress' ? 'bg-warning' : 'bg-secondary')) }}">
                                        {{ ucfirst(str_replace('-', ' ', $event->status)) }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($event->start_time)->format('M j, Y') }}
                                    <i class="bi bi-clock me-1 ms-2"></i>{{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    @if($event->location)
                                        <i class="bi bi-geo-alt me-1 ms-2"></i>{{ $event->location }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('calendar.show', $event) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('calendar.edit', $event) }}" class="btn btn-outline-success btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm" title="Check Conflicts" onclick="checkConflicts({{ $event->id }})">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" title="Follow-up Suggestions" onclick="getFollowUpSuggestions({{ $event->id }})">
                                        <i class="bi bi-lightbulb"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($events->count() > 5)
                        <div class="text-center p-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">View All Events</a>
                        </div>
                    @endif
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-calendar-event text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No events found</p>
                        <a href="{{ route('calendar.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Your First Event
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
// Calendar data from Laravel
const events = {!! json_encode($events->map(function($event) {
    return [
        'id' => $event->id,
        'title' => $event->title,
        'start' => $event->start_time,
        'end' => $event->end_time,
        'status' => $event->status,
        'all_day' => $event->all_day,
        'url' => url('/calendar/' . $event->id)
    ];
})) !!};

let currentDate = new Date();

// Simple JavaScript for basic functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    generateCalendar();
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterEvents();
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
            
            filterEvents(filter);
        });
    });
});

function generateCalendar() {
    const container = document.getElementById('calendar-container');
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    // Month names
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    // Create calendar HTML
    let calendarHTML = `
        <div class="calendar-month-header">
            <h5 class="mb-0">${monthNames[month]} ${year}</h5>
        </div>
        <div class="calendar-grid">
            <div class="calendar-header">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
            </div>
            <div class="calendar-body">
    `;
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDayOfWeek; i++) {
        calendarHTML += '<div class="calendar-day empty"></div>';
    }
    
    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayDate = new Date(year, month, day);
        const dayEvents = getEventsForDate(dayDate);
        const isToday = isSameDay(dayDate, new Date());
        
        calendarHTML += `
            <div class="calendar-day ${isToday ? 'today' : ''} ${dayEvents.length > 0 ? 'has-events' : ''}" 
                 onclick="showDayEvents(${day}, ${month}, ${year})">
                <div class="day-number">${day}</div>
                <div class="day-events">
                    ${dayEvents.slice(0, 3).map(event => `
                        <div class="event-dot ${event.status}" title="${event.title}"></div>
                    `).join('')}
                    ${dayEvents.length > 3 ? `<div class="more-events">+${dayEvents.length - 3}</div>` : ''}
                </div>
            </div>
        `;
    }
    
    calendarHTML += `
            </div>
        </div>
    `;
    
    container.innerHTML = calendarHTML;
}

function getEventsForDate(date) {
    return events.filter(event => {
        const eventDate = new Date(event.start);
        return isSameDay(eventDate, date);
    });
}

function isSameDay(date1, date2) {
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

function changeMonth(direction) {
    currentDate.setMonth(currentDate.getMonth() + direction);
    generateCalendar();
}

function showDayEvents(day, month, year) {
    const date = new Date(year, month, day);
    const dayEvents = getEventsForDate(date);
    
    if (dayEvents.length > 0) {
        let eventsList = dayEvents.map(event => `
            <div class="event-item border-bottom p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">${event.title}</h6>
                        <small class="text-muted">${new Date(event.start).toLocaleTimeString()}</small>
                    </div>
                    <a href="${event.url}" class="btn btn-outline-primary btn-sm">View</a>
                </div>
            </div>
        `).join('');
        
        // Show modal or alert with events
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Events for ${day}/${month + 1}/${year}</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').remove()"></button>
                    </div>
                    <div class="modal-body">
                        ${eventsList}
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Remove modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
}

function filterEvents(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const eventItems = document.querySelectorAll('.event-item');
    
    eventItems.forEach(item => {
        const title = item.querySelector('h6').textContent.toLowerCase();
        const description = item.querySelector('.text-muted').textContent.toLowerCase();
        const statusBadge = item.querySelector('.badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase().trim() : '';
        
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        
        let matchesFilter = true;
        if (filter !== 'all') {
            matchesFilter = status === filter;
        }
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}
</script>

<style>
.calendar-month-header {
    background-color: #f8f9fa;
    padding: 1rem;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #e9ecef;
    border: 1px solid #e9ecef;
}

.calendar-header {
    display: contents;
}

.calendar-day-header {
    background-color: #f8f9fa;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
}

.calendar-body {
    display: contents;
}

.calendar-day {
    background-color: white;
    min-height: 100px;
    padding: 0.5rem;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.today {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.calendar-day.has-events {
    background-color: #fff3e0;
}

.calendar-day.empty {
    background-color: #f8f9fa;
    cursor: default;
}

.day-number {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.day-events {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

.event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.event-dot.scheduled { background-color: #007bff; }
.event-dot.in-progress { background-color: #ffc107; }
.event-dot.completed { background-color: #28a745; }
.event-dot.cancelled { background-color: #6c757d; }
.event-dot.confirmed { background-color: #17a2b8; }

.more-events {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 600;
}
</style>

<script>
// AI Calendar Features
function showAISuggestTimesModal() {
    const modalHtml = `
        <div class="modal fade" id="aiSuggestTimesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Meeting Time Suggestions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="aiSuggestTimesForm">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Meeting Duration (minutes) *</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="15" max="480" value="60" required>
                            </div>
                            <div class="mb-3">
                                <label for="meeting_type" class="form-label">Meeting Type</label>
                                <select class="form-select" id="meeting_type" name="meeting_type">
                                    <option value="general">General Meeting</option>
                                    <option value="client">Client Meeting</option>
                                    <option value="team">Team Meeting</option>
                                    <option value="presentation">Presentation</option>
                                    <option value="interview">Interview</option>
                                    <option value="training">Training</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preferred Times</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="morning" name="preferred_times[]" value="morning" checked>
                                            <label class="form-check-label" for="morning">Morning (9 AM - 12 PM)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="afternoon" name="preferred_times[]" value="afternoon" checked>
                                            <label class="form-check-label" for="afternoon">Afternoon (1 PM - 5 PM)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="evening" name="preferred_times[]" value="evening">
                                            <label class="form-check-label" for="evening">Evening (6 PM - 8 PM)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="early" name="preferred_times[]" value="early">
                                            <label class="form-check-label" for="early">Early Morning (7 AM - 9 AM)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Days of Week</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="monday" name="days_of_week[]" value="monday" checked>
                                            <label class="form-check-label" for="monday">Monday</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="tuesday" name="days_of_week[]" value="tuesday" checked>
                                            <label class="form-check-label" for="tuesday">Tuesday</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="wednesday" name="days_of_week[]" value="wednesday" checked>
                                            <label class="form-check-label" for="wednesday">Wednesday</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="thursday" name="days_of_week[]" value="thursday" checked>
                                            <label class="form-check-label" for="thursday">Thursday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="friday" name="days_of_week[]" value="friday" checked>
                                            <label class="form-check-label" for="friday">Friday</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="saturday" name="days_of_week[]" value="saturday">
                                            <label class="form-check-label" for="saturday">Saturday</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sunday" name="days_of_week[]" value="sunday">
                                            <label class="form-check-label" for="sunday">Sunday</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="suggestMeetingTimes()">
                            <i class="bi bi-robot me-2"></i>Get Suggestions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('aiSuggestTimesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('aiSuggestTimesModal'));
    modal.show();
}

function suggestMeetingTimes() {
    const form = document.getElementById('aiSuggestTimesForm');
    const formData = new FormData(form);
    
    // Get checkbox values
    const preferredTimes = Array.from(document.querySelectorAll('input[name="preferred_times[]"]:checked')).map(cb => cb.value);
    const daysOfWeek = Array.from(document.querySelectorAll('input[name="days_of_week[]"]:checked')).map(cb => cb.value);
    
    formData.append('preferred_times', JSON.stringify(preferredTimes));
    formData.append('days_of_week', JSON.stringify(daysOfWeek));
    
    // Show loading state
    const suggestBtn = document.querySelector('[onclick="suggestMeetingTimes()"]');
    const originalText = suggestBtn.innerHTML;
    suggestBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    suggestBtn.disabled = true;
    
    fetch('{{ route("calendar.ai.suggest-times") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuggestionsModal(data);
        } else {
            alert('Failed to get suggestions: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting suggestions. Please try again.');
    })
    .finally(() => {
        // Reset button state
        suggestBtn.innerHTML = originalText;
        suggestBtn.disabled = false;
    });
}

function checkConflicts(eventId) {
    fetch(`/calendar/${eventId}/conflicts`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showConflictsModal(data);
        } else {
            alert('Failed to check conflicts: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error checking conflicts. Please try again.');
    });
}

function getFollowUpSuggestions(eventId) {
    fetch(`/calendar/${eventId}/follow-up`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showFollowUpModal(data);
        } else {
            alert('Failed to get follow-up suggestions: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting follow-up suggestions. Please try again.');
    });
}

function optimizeSchedule() {
    // Show loading state
    const optimizeBtn = document.querySelector('[onclick="optimizeSchedule()"]');
    const originalText = optimizeBtn.innerHTML;
    optimizeBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Optimizing...';
    optimizeBtn.disabled = true;
    
    fetch('{{ route("calendar.ai.optimize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            work_hours: '9 AM - 5 PM',
            break_preferences: '15 min between meetings',
            focus_time: '2-3 hours daily'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showOptimizationModal(data);
        } else {
            alert('Failed to optimize schedule: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error optimizing schedule. Please try again.');
    })
    .finally(() => {
        // Reset button state
        optimizeBtn.innerHTML = originalText;
        optimizeBtn.disabled = false;
    });
}

function showSuggestionsModal(data) {
    const modalHtml = `
        <div class="modal fade" id="suggestionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Meeting Time Suggestions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Suggested Times:</h6>
                            <div class="list-group">
                                ${data.suggestions.map((suggestion, index) => `
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">${suggestion.start} - ${suggestion.end}</h6>
                                                <p class="mb-1 text-muted">${suggestion.reason || 'Good time slot'}</p>
                                            </div>
                                            <span class="badge bg-primary">${Math.round((suggestion.confidence || 0.8) * 100)}%</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ${data.recommendations.length > 0 ? `
                            <div class="mb-3">
                                <h6>Recommendations:</h6>
                                <ul class="list-unstyled">
                                    ${data.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${rec}</li>`).join('')}
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
    const existingModal = document.getElementById('suggestionsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('suggestionsModal'));
    modal.show();
}

function showConflictsModal(data) {
    const modalHtml = `
        <div class="modal fade" id="conflictsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Conflict Analysis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${data.has_conflicts ? `
                            <div class="alert alert-warning">
                                <h6>Conflicts Detected</h6>
                                <p>${data.analysis}</p>
                                <p><strong>Severity:</strong> <span class="badge bg-${data.severity === 'high' ? 'danger' : data.severity === 'medium' ? 'warning' : 'info'}">${data.severity}</span></p>
                            </div>
                            <div class="mb-3">
                                <h6>Conflicting Events:</h6>
                                <ul class="list-group">
                                    ${data.conflicts.map(conflict => `
                                        <li class="list-group-item">
                                            <strong>${conflict.title}</strong><br>
                                            <small class="text-muted">${conflict.start_time} - ${conflict.end_time}</small>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                            ${data.suggestions.length > 0 ? `
                                <div class="mb-3">
                                    <h6>Suggestions:</h6>
                                    <ul class="list-unstyled">
                                        ${data.suggestions.map(suggestion => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${suggestion}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                        ` : `
                            <div class="alert alert-success">
                                <h6>No Conflicts Found</h6>
                                <p>This time slot is available and doesn't conflict with any existing events.</p>
                            </div>
                        `}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('conflictsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('conflictsModal'));
    modal.show();
}

function showFollowUpModal(data) {
    const modalHtml = `
        <div class="modal fade" id="followUpModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Follow-up Suggestions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${data.pre_meeting.length > 0 ? `
                            <div class="mb-3">
                                <h6>Pre-meeting Preparation:</h6>
                                <ul class="list-unstyled">
                                    ${data.pre_meeting.map(item => `<li class="mb-1"><i class="bi bi-check-circle me-2"></i>${item}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.post_meeting.length > 0 ? `
                            <div class="mb-3">
                                <h6>Post-meeting Actions:</h6>
                                <ul class="list-unstyled">
                                    ${data.post_meeting.map(item => `<li class="mb-1"><i class="bi bi-arrow-right me-2"></i>${item}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.reminders.length > 0 ? `
                            <div class="mb-3">
                                <h6>Suggested Reminders:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    ${data.reminders.map(reminder => `<span class="badge bg-info">${reminder}</span>`).join('')}
                                </div>
                            </div>
                        ` : ''}
                        ${data.action_items.length > 0 ? `
                            <div class="mb-3">
                                <h6>Potential Action Items:</h6>
                                <ul class="list-unstyled">
                                    ${data.action_items.map(item => `<li class="mb-1"><i class="bi bi-list-task me-2"></i>${item}</li>`).join('')}
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
    const existingModal = document.getElementById('followUpModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('followUpModal'));
    modal.show();
}

function showOptimizationModal(data) {
    const modalHtml = `
        <div class="modal fade" id="optimizationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Optimization</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Analysis:</h6>
                            <p>${data.analysis}</p>
                        </div>
                        ${data.issues.length > 0 ? `
                            <div class="mb-3">
                                <h6>Issues Identified:</h6>
                                <ul class="list-unstyled">
                                    ${data.issues.map(issue => `<li class="mb-1"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>${issue}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.suggestions.length > 0 ? `
                            <div class="mb-3">
                                <h6>Optimization Suggestions:</h6>
                                <ul class="list-unstyled">
                                    ${data.suggestions.map(suggestion => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${suggestion}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.productivity_tips.length > 0 ? `
                            <div class="mb-3">
                                <h6>Productivity Tips:</h6>
                                <ul class="list-unstyled">
                                    ${data.productivity_tips.map(tip => `<li class="mb-1"><i class="bi bi-graph-up me-2"></i>${tip}</li>`).join('')}
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
    const existingModal = document.getElementById('optimizationModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('optimizationModal'));
    modal.show();
}
</script>
@endsection