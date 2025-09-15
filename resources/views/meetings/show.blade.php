@extends('layouts.app')

@section('title', 'View Meeting - SecretaryAI')
@section('page-title', 'Meeting Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Meeting Details</h5>
                <div>
                    <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteMeeting({{ $meeting->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <p class="form-control-plaintext fs-5">{{ $meeting->title }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Time</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-calendar me-2"></i>{{ \Carbon\Carbon::parse($meeting->start_time)->format('F j, Y') }}
                                <br>
                                <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">End Time</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-calendar me-2"></i>{{ \Carbon\Carbon::parse($meeting->end_time)->format('F j, Y') }}
                                <br>
                                <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($meeting->end_time)->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>

                @if($meeting->location)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-geo-alt me-2"></i>{{ $meeting->location }}
                            </p>
                        </div>
                    </div>
                    @if($meeting->meeting_link)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Meeting Link</label>
                            <p class="form-control-plaintext">
                                <a href="{{ $meeting->meeting_link }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-link-45deg me-1"></i>Join Meeting
                                </a>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                @if($meeting->participants)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Participants</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-people me-2"></i>
                                {!! nl2br(e($meeting->participants)) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($meeting->description)
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <div class="border p-3 rounded" style="min-height: 100px;">
                        {!! nl2br(e($meeting->description)) !!}
                    </div>
                </div>
                @endif

                @if($meeting->recording_path)
                <div class="mb-4">
                    <label class="form-label fw-bold">Recording</label>
                    <div class="card border">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-mic text-primary me-2"></i>
                                <div>
                                    <h6 class="mb-1">{{ $meeting->audio_file_name ?? 'Meeting Recording' }}</h6>
                                    <small class="text-muted">
                                        @if($meeting->audio_file_size)
                                            {{ number_format($meeting->audio_file_size / 1024 / 1024, 2) }} MB
                                        @endif
                                        @if($meeting->audio_mime_type)
                                            • {{ $meeting->audio_mime_type }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Audio Player -->
                            <div class="audio-player">
                                <audio id="meetingAudio" controls class="w-100" preload="metadata" style="width: 100%; height: 40px;">
                                    <source src="{{ Storage::url($meeting->recording_path) }}" type="{{ $meeting->audio_mime_type ?? 'audio/webm' }}">
                                    <source src="{{ url('/audio/' . $meeting->recording_path) }}" type="{{ $meeting->audio_mime_type ?? 'audio/webm' }}">
                                    <source src="{{ Storage::url($meeting->recording_path) }}" type="audio/mpeg">
                                    <source src="{{ url('/audio/' . $meeting->recording_path) }}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            
                            <!-- Player Controls -->
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="player-info">
                                    <small class="text-muted">
                                        <span id="currentTime">00:00</span> / <span id="duration">00:00</span>
                                    </small>
                                </div>
                                <div class="player-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="playRecording()">
                                        <i class="bi bi-play-circle me-1"></i>Play
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadRecording()">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="transcribeMeetingAudio()">
                                        <i class="bi bi-mic me-1"></i>Transcribe
                                    </button>
                                    @if($meeting->transcript)
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleTranscript()">
                                        <i class="bi bi-file-text me-1"></i>Show Transcript
                                    </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Transcript Section -->
                            @if($meeting->transcript)
                            <div id="transcriptSection" class="mt-3" style="display: none;">
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Transcript</h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="copyTranscript()" title="Copy Transcript">
                                                <i class="bi bi-clipboard me-1"></i>Copy
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="summarizeTranscript()" title="Summarize Transcript">
                                                <i class="bi bi-file-text me-1"></i>Summarize
                                            </button>
                                        </div>
                                    </div>
                                    <div class="transcript-content" id="transcriptContent" style="max-height: 200px; overflow-y: auto;">
                                        {!! nl2br(e($meeting->transcript)) !!}
                                    </div>
                                    
                                    <!-- Summary Section -->
                                    <div id="summarySection" class="mt-3" style="display: none;">
                                        <div class="border-top pt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Summary</h6>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="copySummary()" title="Copy Summary">
                                                    <i class="bi bi-clipboard me-1"></i>Copy Summary
                                                </button>
                                            </div>
                                            <div class="summary-content" id="summaryContent" style="max-height: 150px; overflow-y: auto; background-color: #f8f9fa; padding: 1rem; border-radius: 6px;">
                                                <!-- Summary will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge {{ $meeting->status === 'completed' ? 'bg-success' : ($meeting->status === 'scheduled' ? 'bg-primary' : ($meeting->status === 'in-progress' ? 'bg-warning' : 'bg-secondary')) }}">
                                    {{ ucfirst(str_replace('-', ' ', $meeting->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Duration</label>
                            <p class="form-control-plaintext">
                                {{ \Carbon\Carbon::parse($meeting->start_time)->diffInMinutes(\Carbon\Carbon::parse($meeting->end_time)) }} minutes
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('meetings.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Meetings
                    </a>
                    <div>
                        <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Meeting
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
function deleteMeeting(meetingId) {
    if (confirm('Are you sure you want to delete this meeting? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("meetings.destroy", ":id") }}'.replace(':id', meetingId);
        form.submit();
    }
}

// Audio Player Functionality
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('meetingAudio');
    const currentTimeSpan = document.getElementById('currentTime');
    const durationSpan = document.getElementById('duration');
    
    if (audio) {
        // Update time display
        audio.addEventListener('loadedmetadata', function() {
            durationSpan.textContent = formatTime(audio.duration);
        });
        
        audio.addEventListener('timeupdate', function() {
            currentTimeSpan.textContent = formatTime(audio.currentTime);
        });
        
        // Handle audio events
        audio.addEventListener('play', function() {
            console.log('Audio started playing');
        });
        
        audio.addEventListener('pause', function() {
            console.log('Audio paused');
        });
        
        audio.addEventListener('ended', function() {
            console.log('Audio finished playing');
        });
        
        audio.addEventListener('error', function(e) {
            console.error('Audio error:', e);
            alert('Error loading audio file. Please try downloading the file instead.');
        });
    }
});

// Format time in MM:SS format
function formatTime(seconds) {
    if (isNaN(seconds)) return '00:00';
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    
    return String(minutes).padStart(2, '0') + ':' + String(remainingSeconds).padStart(2, '0');
}

// Play recording
function playRecording() {
    const audio = document.getElementById('meetingAudio');
    if (audio) {
        console.log('Manual play button clicked');
        console.log('Audio element:', audio);
        console.log('Audio src:', audio.src);
        
        audio.play().then(() => {
            console.log('Audio started playing successfully');
        }).catch(e => {
            console.error('Play failed:', e);
            alert('Cannot play audio. This might be due to browser restrictions or audio format compatibility. Please try downloading the file instead.');
        });
    }
}

// Download recording
function downloadRecording() {
    const audio = document.getElementById('meetingAudio');
    if (audio && audio.src) {
        const link = document.createElement('a');
        link.href = audio.src;
        link.download = '{{ $meeting->audio_file_name ?? "meeting_recording" }}';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        // Fallback: try direct download URL
        const downloadUrl = '{{ url("/audio/" . $meeting->recording_path) }}';
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = '{{ $meeting->audio_file_name ?? "meeting_recording" }}';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Toggle transcript visibility
function toggleTranscript() {
    const transcriptSection = document.getElementById('transcriptSection');
    const button = event.target.closest('button');
    
    if (transcriptSection.style.display === 'none') {
        transcriptSection.style.display = 'block';
        button.innerHTML = '<i class="bi bi-file-text me-1"></i>Hide Transcript';
    } else {
        transcriptSection.style.display = 'none';
        button.innerHTML = '<i class="bi bi-file-text me-1"></i>Show Transcript';
    }
}

// Transcribe meeting audio
function transcribeMeetingAudio() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Transcribing...';
    button.disabled = true;
    
    // Get the audio file URL
    const audioUrl = '{{ url("/audio/" . $meeting->recording_path) }}';
    
    // Create a form to submit the audio file
    const formData = new FormData();
    
    // Fetch the audio file and convert to blob
    fetch(audioUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            formData.append('audio_file', blob, '{{ $meeting->audio_file_name ?? "meeting_recording.webm" }}');
            formData.append('language', 'en');
            
            return fetch('{{ route("meetings.transcribe", $meeting->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update the transcript in the database
                updateMeetingTranscript(data.transcript);
            } else {
                showNotification('Error transcribing audio: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error transcribing audio: ' + error.message, 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Update meeting transcript in database
function updateMeetingTranscript(transcript) {
    fetch('{{ route("meetings.update", $meeting->id) }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            transcript: transcript,
            _method: 'PUT'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update the transcript display
            const transcriptContent = document.getElementById('transcriptContent');
            if (transcriptContent) {
                transcriptContent.textContent = transcript;
            }
            
            // Show success message
            showNotification('Audio transcribed successfully! The transcript has been saved.', 'success');
            
            // Reload the page to show the updated transcript
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showNotification('Error saving transcript: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving transcript: ' + error.message, 'error');
    });
}

// Copy transcript to clipboard
function copyTranscript() {
    const transcriptContent = document.getElementById('transcriptContent');
    const text = transcriptContent.textContent || transcriptContent.innerText;
    
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Transcript copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy transcript', 'error');
    });
}

// Copy summary to clipboard
function copySummary() {
    const summaryContent = document.getElementById('summaryContent');
    const text = summaryContent.textContent || summaryContent.innerText;
    
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Summary copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy summary', 'error');
    });
}

// Summarize transcript using AI
function summarizeTranscript() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Summarizing...';
    button.disabled = true;
    
    const transcriptContent = document.getElementById('transcriptContent');
    const transcript = transcriptContent.textContent || transcriptContent.innerText;
    
    // Call AI summarization API
    fetch('/ai/generate-text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            prompt: `Please provide a concise summary of the following meeting transcript. Include key points, decisions made, action items, and important discussions:\n\n${transcript}`,
            max_tokens: 500
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show summary section
            const summarySection = document.getElementById('summarySection');
            const summaryContent = document.getElementById('summaryContent');
            
            summaryContent.innerHTML = data.content.replace(/\n/g, '<br>');
            summarySection.style.display = 'block';
            
            showNotification('Transcript summarized successfully!', 'success');
        } else {
            showNotification('Error summarizing transcript: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error summarizing transcript: ' + error.message, 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Show Bootstrap notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection

<style>
.audio-player {
    margin: 1rem 0;
}

.audio-player audio {
    width: 100%;
    height: 40px;
    border-radius: 6px;
}

.player-info {
    font-family: 'Courier New', monospace;
}

.player-actions .btn {
    margin-left: 0.5rem;
}

.transcript-content {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    line-height: 1.5;
}
</style>
