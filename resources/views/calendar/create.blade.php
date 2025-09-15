@extends('layouts.app')

@section('title', 'Add Event - SecretaryAI')
@section('page-title', 'Add Calendar Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add New Event</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('calendar.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="all_day" name="all_day" 
                                   {{ old('all_day') ? 'checked' : '' }}>
                            <label class="form-check-label" for="all_day">
                                All Day Event
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location') }}" 
                               placeholder="Conference Room A, Zoom, etc.">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="attendees" class="form-label">Attendees</label>
                        <textarea class="form-control @error('attendees') is-invalid @enderror" 
                                  id="attendees" name="attendees" rows="3" 
                                  placeholder="Enter attendee names or email addresses, separated by commas">{{ old('attendees') }}</textarea>
                        @error('attendees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="5" 
                                  placeholder="Enter event description, agenda, or any additional details...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Calendar
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calendar-plus me-2"></i>Add Event
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill end time when start time changes
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = new Date(this.value);
    const endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // Add 1 hour
    
    const endTimeInput = document.getElementById('end_time');
    if (!endTimeInput.value) {
        endTimeInput.value = endTime.toISOString().slice(0, 16);
    }
});

// Handle all-day event checkbox
document.getElementById('all_day').addEventListener('change', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    if (this.checked) {
        // For all-day events, set time to 00:00
        const startTime = new Date(startTimeInput.value);
        startTime.setHours(0, 0, 0, 0);
        startTimeInput.value = startTime.toISOString().slice(0, 16);
        
        const endTime = new Date(startTime);
        endTime.setHours(23, 59, 59, 999);
        endTimeInput.value = endTime.toISOString().slice(0, 16);
    }
});
</script>
@endsection
