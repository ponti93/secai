@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Upload Meeting Recording</h2>
            <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Meetings
            </a>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upload New Recording</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('meetings.upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Meeting Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="meeting_date" class="form-label">Meeting Date</label>
                            <input type="datetime-local" class="form-control" id="meeting_date" name="meeting_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Recording File</label>
                            <input type="file" class="form-control" id="file" name="file" accept="audio/*,video/*" required>
                            <div class="form-text">Supported formats: MP3, MP4, WAV, AVI</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="participants" class="form-label">Participants</label>
                            <input type="text" class="form-control" id="participants" name="participants" placeholder="Enter participant emails separated by commas">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="transcribe" name="transcribe" value="1">
                            <label class="form-check-label" for="transcribe">
                                Transcribe audio using AI (coming soon)
                            </label>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-2"></i>Upload Recording
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upload Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Maximum file size: 100MB
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Supported formats: MP3, MP4, WAV, AVI
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            AI will automatically transcribe the recording
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Generate meeting summary and action items
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Recent Uploads</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">No recent uploads</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
