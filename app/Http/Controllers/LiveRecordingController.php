<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LiveRecordingController extends Controller
{
    /**
     * Show the live recording interface
     */
    public function create()
    {
        return view('meetings.live-recording');
    }

    /**
     * Start a new live recording session
     */
    public function start(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participants' => 'nullable|string',
        ]);

        // Create a meeting record for the live recording
        $meeting = Meeting::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => now(),
            'end_time' => now(), // Will be updated when recording stops
            'location' => 'Live Recording',
            'participants' => $request->participants ? json_encode(explode(',', $request->participants)) : null,
            'status' => 'in_progress',
            'transcription_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'meeting_id' => $meeting->id,
            'message' => 'Recording session started'
        ]);
    }

    /**
     * Save the recorded audio
     */
    public function save(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'audio_data' => 'required|string', // Base64 encoded audio
            'duration' => 'required|numeric|min:1',
        ]);

        $meeting = Meeting::where('id', $request->meeting_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Decode base64 audio data
        $audioData = base64_decode(preg_replace('#^data:audio/\w+;base64,#i', '', $request->audio_data));
        
        // Generate filename
        $fileName = 'live_recording_' . $meeting->id . '_' . time() . '.webm';
        $filePath = 'meetings/live/' . $fileName;
        
        // Store the audio file
        Storage::disk('public')->put($filePath, $audioData);
        
        // Update meeting with recording details
        $meeting->update([
            'end_time' => now(),
            'status' => 'completed',
            'recording_path' => $filePath,
            'audio_file_name' => $fileName,
            'audio_file_size' => strlen($audioData),
            'audio_mime_type' => 'audio/webm',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording saved successfully',
            'meeting_id' => $meeting->id
        ]);
    }

    /**
     * Stop the recording and save
     */
    public function stop(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
        ]);

        $meeting = Meeting::where('id', $request->meeting_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $meeting->update([
            'end_time' => now(),
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording stopped',
            'meeting_id' => $meeting->id
        ]);
    }

    /**
     * Get recording status
     */
    public function status($meetingId)
    {
        $meeting = Meeting::where('id', $meetingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'meeting_id' => $meeting->id,
            'status' => $meeting->status,
            'start_time' => $meeting->start_time,
            'end_time' => $meeting->end_time,
            'has_recording' => !is_null($meeting->recording_path),
        ]);
    }
}
