@extends('layouts.app')

@section('title', 'Meetings - SecretaryAI')
@section('page-title', 'Meeting Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('meetings.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ Schedule Meeting
    </a>
    <a href="{{ route('meetings.live.recording') }}" class="btn btn-danger">
        <i class="bi bi-record-circle me-2"></i>Live Recording
    </a>
    <a href="{{ route('meetings.upload') }}" class="btn btn-success">
        <i class="bi bi-upload me-2"></i>Upload Recording
    </a>
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
                    <input type="text" class="form-control" id="searchInput" placeholder="Search meetings...">
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

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary">{{ $meetings->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-success">{{ $meetings->where('status', 'scheduled')->count() }}</div>
                        <small class="text-muted">Scheduled</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-info">{{ $meetings->where('status', 'completed')->count() }}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning">{{ $meetings->where('status', 'in-progress')->count() }}</div>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Meetings List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Meetings</h6>
            </div>
            <div class="card-body p-0">
                @if($meetings->count() > 0)
                    @foreach($meetings as $meeting)
                    <div class="meeting-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $meeting->title }}</h6>
                                    <span class="badge {{ $meeting->status === 'completed' ? 'bg-success' : ($meeting->status === 'scheduled' ? 'bg-primary' : ($meeting->status === 'in-progress' ? 'bg-warning' : 'bg-secondary')) }}">
                                        {{ ucfirst(str_replace('-', ' ', $meeting->status)) }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($meeting->start_time)->format('M j, Y') }}
                                    <i class="bi bi-clock me-1 ms-2"></i>{{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('g:i A') }}
                                    @if($meeting->location)
                                        <i class="bi bi-geo-alt me-1 ms-2"></i>{{ $meeting->location }}
                                    @endif
                                </p>
                                @if($meeting->participants)
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-people me-1"></i>{{ $meeting->participants }}
                                </p>
                                @endif
                                @if($meeting->description)
                                <p class="text-muted small mb-0">{{ Str::limit($meeting->description, 100) }}</p>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($meeting->recording_path)
                                    <a href="{{ route('meetings.download', $meeting) }}" class="btn btn-outline-info btn-sm" title="Download Audio">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-calendar-event text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No meetings found</p>
                        <a href="{{ route('meetings.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Schedule Your First Meeting
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
            filterMeetings();
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
            
            filterMeetings(filter);
        });
    });
});

function filterMeetings(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const meetingItems = document.querySelectorAll('.meeting-item');
    
    meetingItems.forEach(item => {
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
@endsection