<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Services\OpenAIService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    private $openaiService;
    private $geminiService;

    public function __construct(OpenAIService $openaiService, GeminiService $geminiService)
    {
        $this->openaiService = $openaiService;
        $this->geminiService = $geminiService;
    }
    /**
     * Display a listing of meetings
     */
    public function index()
    {
        $meetings = Meeting::where('user_id', Auth::id())
            ->orderBy('start_time', 'desc')
            ->get();
        
        return view('meetings.index', compact('meetings'));
    }

    /**
     * Show the form for creating a new meeting
     */
    public function create()
    {
        return view('meetings.create');
    }

    /**
     * Store a newly created meeting
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'participants' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
        ]);

        Auth::user()->meetings()->create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'participants' => $request->participants,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'status' => 'scheduled',
        ]);

        return redirect()->route('meetings.index')->with('success', 'Meeting created successfully!');
    }

    /**
     * Display the specified meeting
     */
    public function show(Meeting $meeting)
    {
        // Check if user owns this meeting
        if ($meeting->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('meetings.show', compact('meeting'));
    }

    /**
     * Show the form for editing the specified meeting
     */
    public function edit(Meeting $meeting)
    {
        // Check if user owns this meeting
        if ($meeting->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('meetings.edit', compact('meeting'));
    }

    /**
     * Update the specified meeting
     */
    public function update(Request $request, Meeting $meeting)
    {
        // Check if user owns this meeting
        if ($meeting->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'participants' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'transcript' => 'nullable|string',
        ]);

        $meeting->update($request->only([
            'title', 'description', 'start_time', 'end_time', 
            'participants', 'location', 'meeting_link', 'transcript'
        ]));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting updated successfully!',
                'meeting' => $meeting
            ]);
        }

        return redirect()->route('meetings.index')->with('success', 'Meeting updated successfully!');
    }

    /**
     * Remove the specified meeting
     */
    public function destroy(Meeting $meeting)
    {
        // Check if user owns this meeting
        if ($meeting->user_id !== Auth::id()) {
            abort(403);
        }

        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Meeting deleted successfully!');
    }

    /**
     * Transcribe meeting audio using OpenAI
     */
    public function transcribeAudio(Request $request, Meeting $meeting)
    {
        // Check if user owns this meeting
        if ($meeting->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,webm,ogg,m4a,flac|max:50000',
            'language' => 'sometimes|string|in:en,es,fr,de,it,pt,ru,ja,ko,zh,ar,hi',
        ]);

        try {
            $audioFile = $request->file('audio_file');
            $language = $request->language ?? 'en';

            // Try OpenAI first
            $result = $this->openaiService->transcribeAudio($audioFile, $language);

            // If OpenAI fails due to quota/billing, try Gemini as fallback
            if (!$result['success'] && (strpos($result['error'], 'quota') !== false || strpos($result['error'], 'billing') !== false)) {
                // Convert audio file to base64 for Gemini
                $audioData = base64_encode(file_get_contents($audioFile->getPathname()));
                $mimeType = $audioFile->getMimeType();
                
                $result = $this->geminiService->transcribeAudio($audioData, $mimeType, $language);
            }

            if ($result['success']) {
                // Update meeting with transcript
                $meeting->update([
                    'transcript' => $result['transcript'],
                    'transcription_status' => 'completed'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Audio transcribed successfully!',
                    'transcript' => $result['transcript'],
                    'provider' => $result['provider'] ?? 'openai'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Transcription failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
