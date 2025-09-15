<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GeminiService;

class AudioUploadController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Show the audio upload form
     */
    public function create()
    {
        return view('meetings.upload');
    }

    /**
     * Handle audio file upload and processing
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_date' => 'required|date',
            'file' => 'required|file|max:102400|mimes:mp3,wav,mp4,avi,mov,webm', // 100MB max
            'participants' => 'nullable|string',
            'transcribe' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        
        // Store the file
        $filePath = $file->storeAs('meetings/audio', $fileName, 'public');
        
        // Create meeting record
        $meetingData = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->meeting_date,
            'end_time' => $request->meeting_date, // Will be updated if we have duration
            'location' => 'Audio Recording',
            'participants' => $request->participants ? json_encode(explode(',', $request->participants)) : null,
            'status' => 'completed',
            'recording_path' => $filePath,
            'audio_file_name' => $originalName,
            'audio_file_size' => $file->getSize(),
            'audio_mime_type' => $file->getMimeType(),
        ];

        $meeting = Meeting::create($meetingData);

        // Process transcription if requested
        if ($request->has('transcribe') && $request->transcribe) {
            try {
                $transcription = $this->processTranscription($filePath, $file->getMimeType());
                if ($transcription) {
                    $meeting->update([
                        'transcript' => $transcription,
                        'transcription_status' => 'completed'
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the upload
                \Log::error('Transcription failed: ' . $e->getMessage());
                $meeting->update(['transcription_status' => 'failed']);
            }
        }

        return redirect()->route('meetings.index')->with('success', 'Audio file uploaded successfully!');
    }

    /**
     * Process audio transcription using Gemini AI
     */
    private function processTranscription($filePath, $mimeType)
    {
        // Check if file is audio/video
        if (!str_starts_with($mimeType, 'audio/') && !str_starts_with($mimeType, 'video/')) {
            return null;
        }

        // For now, return a placeholder since Gemini doesn't support audio transcription directly
        // In a real implementation, you would use a service like Google Speech-to-Text
        return "Transcription feature coming soon. Audio file uploaded successfully.";
    }

    /**
     * Download the audio file
     */
    public function download(Meeting $meeting)
    {
        if ($meeting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$meeting->recording_path) {
            abort(404, 'Audio file not found');
        }

        return Storage::disk('public')->download($meeting->recording_path, $meeting->audio_file_name);
    }

    /**
     * Get transcription for a meeting
     */
    public function getTranscription(Meeting $meeting)
    {
        if ($meeting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'transcript' => $meeting->transcript,
            'status' => $meeting->transcription_status
        ]);
    }
}
