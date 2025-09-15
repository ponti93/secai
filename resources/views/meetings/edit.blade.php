@extends('layouts.app')

@section('title', 'Edit Meeting - SecretaryAI')
@section('page-title', 'Edit Meeting')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Meeting</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('meetings.update', $meeting) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Meeting Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $meeting->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($meeting->start_time)->format('Y-m-d\TH:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($meeting->end_time)->format('Y-m-d\TH:i')) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location', $meeting->location) }}" 
                               placeholder="Conference Room A, Zoom, etc.">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meeting_link" class="form-label">Meeting Link</label>
                        <input type="url" class="form-control @error('meeting_link') is-invalid @enderror" 
                               id="meeting_link" name="meeting_link" value="{{ old('meeting_link', $meeting->meeting_link) }}" 
                               placeholder="https://zoom.us/j/123456789">
                        @error('meeting_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="participants" class="form-label">Participants</label>
                        <textarea class="form-control @error('participants') is-invalid @enderror" 
                                  id="participants" name="participants" rows="3" 
                                  placeholder="Enter participant names or email addresses, one per line">{{ old('participants', $meeting->participants) }}</textarea>
                        @error('participants')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="5" 
                                  placeholder="Enter meeting agenda, objectives, or any additional details...">{{ old('description', $meeting->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('meetings.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Meetings
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-2"></i>Update Meeting
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteMeeting({{ $meeting->id }})">
                                <i class="bi bi-trash me-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function deleteMeeting(meetingId) {
    if (confirm('Are you sure you want to delete this meeting? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("meetings.destroy", ":id") }}'.replace(':id', meetingId);
        form.submit();
    }
}

// Auto-fill end time when start time changes
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = new Date(this.value);
    const endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // Add 1 hour
    
    const endTimeInput = document.getElementById('end_time');
    if (!endTimeInput.value) {
        endTimeInput.value = endTime.toISOString().slice(0, 16);
    }
});
</script>
@endsection
