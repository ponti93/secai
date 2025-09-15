<?php

namespace App\Http\Controllers;

use App\Models\AiUsage;
use App\Services\OpenAIService;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiController extends Controller
{
    private $openaiService;
    private $geminiService;

    public function __construct(OpenAIService $openaiService, GeminiService $geminiService)
    {
        $this->openaiService = $openaiService;
        $this->geminiService = $geminiService;
    }

    /**
     * Transcribe audio using OpenAI Whisper
     */
    public function transcribeAudio(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audio_file' => 'required|file|mimes:mp3,wav,webm,ogg,m4a,flac|max:50000', // 50MB max
            'language' => 'sometimes|string|in:en,es,fr,de,it,pt,ru,ja,ko,zh,ar,hi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $audioFile = $request->file('audio_file');
            $language = $request->language ?? 'en';

            $result = $this->openaiService->transcribeAudio($audioFile, $language);

            $this->logAiUsage(auth()->user(), 'audio-transcription', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Audio transcription failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate text using AI
     */
    public function generateText(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:10000',
            'max_tokens' => 'sometimes|integer|min:1|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $prompt = $request->input('prompt');
            $maxTokens = $request->input('max_tokens', 1000);

            // Use Gemini for text generation
            $result = $this->geminiService->generateText($prompt, [
                'max_tokens' => $maxTokens,
                'tone' => 'professional',
                'length' => 'medium'
            ]);

            $this->logAiUsage(auth()->user(), 'text-generation', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Text generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log AI usage
     */
    private function logAiUsage($user, string $feature, array $result): void
    {
        if ($result['success']) {
            AiUsage::create([
                'user_id' => $user->id,
                'feature' => $feature,
                'model' => $result['model'] ?? 'whisper-1',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'cost' => $result['cost'] ?? 0,
                'response_time_ms' => $result['response_time_ms'] ?? 0,
                'request_data' => $result['request_data'] ?? null,
                'response_data' => $result['response_data'] ?? null,
                'status' => 'success',
            ]);
        } else {
            AiUsage::create([
                'user_id' => $user->id,
                'feature' => $feature,
                'model' => 'whisper-1',
                'tokens_used' => 0,
                'cost' => 0,
                'response_time_ms' => 0,
                'status' => 'error',
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }
}

