@extends('layouts.app')

@section('title', 'Live Recording - SecretaryAI')
@section('page-title', 'Live Meeting Recording')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Meetings
    </a>
    <button type="button" class="btn btn-outline-info" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Live Meeting Recording</h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recording Controls</h5>
                </div>
                <div class="card-body">
                    <!-- Recording Setup Form -->
                    <div id="setupForm">
                        <form id="recordingSetupForm">
                            @csrf
                            <div class="mb-3">
                                <label for="title" class="form-label">Meeting Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       placeholder="Enter meeting title">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Enter meeting description (optional)"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="participants" class="form-label">Participants</label>
                                <input type="text" class="form-control" id="participants" name="participants" 
                                       placeholder="Enter participant emails separated by commas (optional)">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Microphone Access</label>
                                <div class="alert alert-info">
                                    <i class="bi bi-mic me-2"></i>
                                    <strong>Permission Required:</strong> Click "Start Recording" to allow microphone access.
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" id="startRecordingBtn">
                                    <i class="bi bi-record-circle me-2"></i>Start Recording
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('meetings.index') }}'">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Recording Interface -->
                    <div id="recordingInterface" style="display: none;">
                        <div class="text-center mb-4">
                            <div class="recording-indicator mb-3">
                                <div class="recording-dot"></div>
                                <span class="ms-2">Recording in progress...</span>
                            </div>
                            <div class="recording-timer">
                                <h3 id="recordingTime">00:00:00</h3>
                            </div>
                        </div>
                        
                        <div class="recording-controls text-center">
                            <button type="button" class="btn btn-danger btn-lg me-3" id="stopRecordingBtn">
                                <i class="bi bi-stop-circle me-2"></i>Stop Recording
                            </button>
                            <button type="button" class="btn btn-warning" id="pauseRecordingBtn">
                                <i class="bi bi-pause-circle me-2"></i>Pause
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" id="recordingProgress" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Recording quality: High</small>
                        </div>
                    </div>

                    <!-- Recording Complete -->
                    <div id="recordingComplete" style="display: none;">
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Recording Complete!</strong> Your meeting has been saved successfully.
                        </div>
                        
                        <!-- Playback Section -->
                        <div id="playbackSection" class="mt-4" style="display: none;">
                            <div class="card border">
                                <div class="card-header">
                                    <h6 class="mb-0">Recording Playback</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-mic text-primary me-2"></i>
                                        <div>
                                            <h6 class="mb-1" id="playbackTitle">Live Recording</h6>
                                            <small class="text-muted" id="playbackInfo">Recording saved</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Audio Player -->
                                    <div class="audio-player">
                                        <audio id="playbackAudio" controls class="w-100" preload="metadata">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                    
                                    <!-- Player Controls -->
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div class="player-info">
                                            <small class="text-muted">
                                                <span id="playbackCurrentTime">00:00</span> / <span id="playbackDuration">00:00</span>
                                            </small>
                                        </div>
                                        <div class="player-actions">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadPlaybackRecording()">
                                                <i class="bi bi-download me-1"></i>Download
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="{{ route('meetings.index') }}" class="btn btn-primary">
                                <i class="bi bi-list me-2"></i>View All Meetings
                            </a>
                            <button type="button" class="btn btn-outline-primary ms-2" onclick="startNewRecording()">
                                <i class="bi bi-plus-circle me-2"></i>Start New Recording
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Recording Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Ensure good microphone quality
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Speak clearly and at normal volume
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Minimize background noise
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Keep your device charged
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Test microphone before starting
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Recording Status</h6>
                </div>
                <div class="card-body">
                    <div id="statusInfo">
                        <p class="mb-1"><strong>Status:</strong> <span id="currentStatus">Ready</span></p>
                        <p class="mb-1"><strong>Duration:</strong> <span id="currentDuration">00:00:00</span></p>
                        <p class="mb-0"><strong>Quality:</strong> <span id="currentQuality">High</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.recording-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #dc3545;
}

.recording-dot {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.recording-timer {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #dc3545;
}

.recording-controls {
    margin: 2rem 0;
}

#recordingProgress {
    transition: width 0.3s ease;
}

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

<script>
let mediaRecorder;
let audioChunks = [];
let recordingStartTime;
let recordingTimer;
let isRecording = false;
let isPaused = false;
let currentMeetingId = null;

// DOM Elements
const setupForm = document.getElementById('setupForm');
const recordingInterface = document.getElementById('recordingInterface');
const recordingComplete = document.getElementById('recordingComplete');
const startRecordingBtn = document.getElementById('startRecordingBtn');
const stopRecordingBtn = document.getElementById('stopRecordingBtn');
const pauseRecordingBtn = document.getElementById('pauseRecordingBtn');
const recordingTime = document.getElementById('recordingTime');
const recordingProgress = document.getElementById('recordingProgress');
const currentStatus = document.getElementById('currentStatus');
const currentDuration = document.getElementById('currentDuration');

// Start recording
startRecordingBtn.addEventListener('click', async () => {
    try {
        // Get form data
        const formData = new FormData(document.getElementById('recordingSetupForm'));
        
        // Start recording session
        const response = await fetch('{{ route("meetings.live.start") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentMeetingId = result.meeting_id;
            await startAudioRecording();
        } else {
            alert('Failed to start recording session');
        }
    } catch (error) {
        console.error('Error starting recording:', error);
        alert('Error starting recording. Please check your microphone permissions.');
    }
});

// Start audio recording
async function startAudioRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                sampleRate: 44100
            } 
        });
        
        mediaRecorder = new MediaRecorder(stream, {
            mimeType: 'audio/webm;codecs=opus'
        });
        
        audioChunks = [];
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                audioChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            await saveRecording(audioBlob);
            
            // Stop all tracks
            stream.getTracks().forEach(track => track.stop());
        };
        
        mediaRecorder.start(1000); // Collect data every second
        recordingStartTime = Date.now();
        isRecording = true;
        
        // Update UI
        setupForm.style.display = 'none';
        recordingInterface.style.display = 'block';
        currentStatus.textContent = 'Recording';
        
        // Start timer
        startTimer();
        
    } catch (error) {
        console.error('Error accessing microphone:', error);
        alert('Microphone access denied. Please allow microphone access and try again.');
    }
}

// Start recording timer
function startTimer() {
    recordingTimer = setInterval(() => {
        if (isRecording && !isPaused) {
            const elapsed = Date.now() - recordingStartTime;
            const seconds = Math.floor(elapsed / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            
            const timeString = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes % 60).padStart(2, '0') + ':' +
                String(seconds % 60).padStart(2, '0');
            
            recordingTime.textContent = timeString;
            currentDuration.textContent = timeString;
            
            // Update progress bar (max 2 hours)
            const progress = Math.min((elapsed / (2 * 60 * 60 * 1000)) * 100, 100);
            recordingProgress.style.width = progress + '%';
        }
    }, 1000);
}

// Stop recording
stopRecordingBtn.addEventListener('click', () => {
    if (mediaRecorder && isRecording) {
        mediaRecorder.stop();
        isRecording = false;
        clearInterval(recordingTimer);
        
        // Update UI
        recordingInterface.style.display = 'none';
        recordingComplete.style.display = 'block';
        currentStatus.textContent = 'Completed';
        
        // Show playback section immediately
        const playbackSection = document.getElementById('playbackSection');
        if (playbackSection) {
            playbackSection.style.display = 'block';
        }
    }
});

// Pause/Resume recording
pauseRecordingBtn.addEventListener('click', () => {
    if (isRecording) {
        if (isPaused) {
            // Resume
            mediaRecorder.resume();
            isPaused = false;
            pauseRecordingBtn.innerHTML = '<i class="bi bi-pause-circle me-2"></i>Pause';
            currentStatus.textContent = 'Recording';
        } else {
            // Pause
            mediaRecorder.pause();
            isPaused = true;
            pauseRecordingBtn.innerHTML = '<i class="bi bi-play-circle me-2"></i>Resume';
            currentStatus.textContent = 'Paused';
        }
    }
});

// Save recording
async function saveRecording(audioBlob) {
    try {
        const reader = new FileReader();
        reader.onload = async () => {
            const base64Audio = reader.result;
            
            const response = await fetch('{{ route("meetings.live.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    meeting_id: currentMeetingId,
                    audio_data: base64Audio,
                    duration: Math.floor((Date.now() - recordingStartTime) / 1000)
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('Recording saved successfully');
                // Set up playback
                setupPlayback(audioBlob);
            } else {
                alert('Failed to save recording');
            }
        };
        
        reader.readAsDataURL(audioBlob);
    } catch (error) {
        console.error('Error saving recording:', error);
        alert('Error saving recording');
    }
}

// Setup playback after recording is saved
function setupPlayback(audioBlob) {
    const playbackSection = document.getElementById('playbackSection');
    const playbackAudio = document.getElementById('playbackAudio');
    const playbackTitle = document.getElementById('playbackTitle');
    const playbackInfo = document.getElementById('playbackInfo');
    
    // Create object URL for the audio blob
    const audioUrl = URL.createObjectURL(audioBlob);
    
    // Clear any existing source and set new one
    playbackAudio.innerHTML = '';
    const source = document.createElement('source');
    source.src = audioUrl;
    source.type = 'audio/webm';
    playbackAudio.appendChild(source);
    
    // Force reload of audio element
    playbackAudio.load();
    
    // Update title and info
    const title = document.getElementById('title').value || 'Live Recording';
    playbackTitle.textContent = title;
    playbackInfo.textContent = `Duration: ${formatTime(Math.floor((Date.now() - recordingStartTime) / 1000))}`;
    
    // Show playback section
    playbackSection.style.display = 'block';
    
    // Setup playback audio events
    setupPlaybackAudioEvents();
    
    console.log('Playback setup complete, audio URL:', audioUrl);
}

// Setup playback audio events
function setupPlaybackAudioEvents() {
    const playbackAudio = document.getElementById('playbackAudio');
    const playbackCurrentTime = document.getElementById('playbackCurrentTime');
    const playbackDuration = document.getElementById('playbackDuration');
    
    if (playbackAudio) {
        // Add debugging
        console.log('Setting up playback audio events');
        console.log('Audio element:', playbackAudio);
        console.log('Audio src:', playbackAudio.src);
        
        playbackAudio.addEventListener('loadedmetadata', function() {
            console.log('Audio metadata loaded, duration:', playbackAudio.duration);
            playbackDuration.textContent = formatTime(playbackAudio.duration);
        });
        
        playbackAudio.addEventListener('canplay', function() {
            console.log('Audio can play');
        });
        
        playbackAudio.addEventListener('play', function() {
            console.log('Audio started playing');
        });
        
        playbackAudio.addEventListener('pause', function() {
            console.log('Audio paused');
        });
        
        playbackAudio.addEventListener('timeupdate', function() {
            playbackCurrentTime.textContent = formatTime(playbackAudio.currentTime);
        });
        
        playbackAudio.addEventListener('error', function(e) {
            console.error('Playback audio error:', e);
            console.error('Audio error details:', playbackAudio.error);
            alert('Error loading playback audio. Please try downloading the file instead.');
        });
        
        // Force click event handling
        playbackAudio.addEventListener('click', function(e) {
            console.log('Audio player clicked');
            e.preventDefault();
            e.stopPropagation();
        });
        
        // Add a manual play button as backup
        const playButton = document.createElement('button');
        playButton.innerHTML = '<i class="bi bi-play-circle me-1"></i>Play Recording';
        playButton.className = 'btn btn-primary btn-sm mt-2';
        playButton.onclick = function() {
            console.log('Manual play button clicked');
            playbackAudio.play().catch(e => {
                console.error('Manual play failed:', e);
                alert('Cannot play audio. Please try downloading the file.');
            });
        };
        
        const playerActions = document.querySelector('.player-actions');
        if (playerActions) {
            playerActions.appendChild(playButton);
        }
    }
}

// Download playback recording
function downloadPlaybackRecording() {
    const playbackAudio = document.getElementById('playbackAudio');
    if (playbackAudio && playbackAudio.src) {
        const link = document.createElement('a');
        link.href = playbackAudio.src;
        link.download = `live_recording_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.webm`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        // Fallback: try to get the audio URL from the blob
        console.log('No audio src available for download');
        alert('Audio not ready for download yet. Please wait a moment and try again.');
    }
}

// Start new recording
function startNewRecording() {
    setupForm.style.display = 'block';
    recordingInterface.style.display = 'none';
    recordingComplete.style.display = 'none';
    currentStatus.textContent = 'Ready';
    currentDuration.textContent = '00:00:00';
    recordingProgress.style.width = '0%';
    currentMeetingId = null;
    
    // Hide playback section
    const playbackSection = document.getElementById('playbackSection');
    if (playbackSection) {
        playbackSection.style.display = 'none';
    }
    
    // Reset form
    document.getElementById('recordingSetupForm').reset();
}

// Check for microphone support
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    alert('Your browser does not support microphone recording. Please use a modern browser.');
}
</script>
@endsection
