@extends('layouts.app')

@section('title', 'View Event - SecretaryAI')
@section('page-title', 'Event Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Event Details</h5>
                <div>
                    <a href="{{ route('calendar.edit', $event) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEvent({{ $event->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <p class="form-control-plaintext fs-5">{{ $event->title }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Time</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-calendar me-2"></i>{{ \Carbon\Carbon::parse($event->start_time)->format('F j, Y') }}
                                <br>
                                <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">End Time</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-calendar me-2"></i>{{ \Carbon\Carbon::parse($event->end_time)->format('F j, Y') }}
                                <br>
                                <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>

                @if($event->all_day)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <span class="badge bg-info fs-6">All Day Event</span>
                        </div>
                    </div>
                </div>
                @endif

                @if($event->location)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-geo-alt me-2"></i>{{ $event->location }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($event->attendees)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Attendees</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-people me-2"></i>
                                {{ is_string($event->attendees) ? $event->attendees : implode(', ', json_decode($event->attendees, true)) }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($event->description)
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <div class="border p-3 rounded" style="min-height: 100px;">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge {{ $event->status === 'completed' ? 'bg-success' : ($event->status === 'scheduled' ? 'bg-primary' : ($event->status === 'in-progress' ? 'bg-warning' : 'bg-secondary')) }}">
                                    {{ ucfirst(str_replace('-', ' ', $event->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Duration</label>
                            <p class="form-control-plaintext">
                                {{ \Carbon\Carbon::parse($event->start_time)->diffInMinutes(\Carbon\Carbon::parse($event->end_time)) }} minutes
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Calendar
                    </a>
                    <div>
                        <a href="{{ route('calendar.edit', $event) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Event
                        </a>
                    </div>
                </div>
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
function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("calendar.destroy", ":id") }}'.replace(':id', eventId);
        form.submit();
    }
}
</script>
@endsection
