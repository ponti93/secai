<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiUsage;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiController extends Controller
{
    private $geminiService;

    public function __construct(\App\Services\GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Generate email reply
     */
    public function generateEmailReply(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_content' => 'required|string',
            'context' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateEmailReply(
            $request->email_content,
            $request->context ?? ''
        );

        $this->logAiUsage($request->user(), 'email-reply', $result);

        return response()->json($result);
    }

    /**
     * Generate document content
     */
    public function generateDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'document_type' => 'sometimes|string|in:letter,memo,report,proposal,contract,agenda,minutes,general',
            'tone' => 'sometimes|string|in:professional,casual,formal,friendly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateDocument(
            $request->prompt,
            $request->document_type ?? 'general',
            $request->tone ?? 'professional'
        );

        $this->logAiUsage($request->user(), 'document-generation', $result);

        return response()->json($result);
    }

    /**
     * Summarize meeting transcript
     */
    public function summarizeTranscript(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transcript' => 'required|string',
            'meeting_type' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->summarizeTranscript(
            $request->transcript,
            $request->meeting_type ?? 'general'
        );

        $this->logAiUsage($request->user(), 'meeting-summary', $result);

        return response()->json($result);
    }

    /**
     * Extract action items from transcript
     */
    public function extractActionItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transcript' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->extractActionItems($request->transcript);

        $this->logAiUsage($request->user(), 'action-items', $result);

        return response()->json($result);
    }

    /**
     * Generate meeting agenda
     */
    public function generateAgenda(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meeting_title' => 'required|string',
            'meeting_type' => 'required|string',
            'participants' => 'required|array|min:1',
            'duration' => 'required|integer|min:15|max:480',
            'topics' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateAgenda(
            $request->meeting_title,
            $request->meeting_type,
            $request->participants,
            $request->duration,
            $request->topics ?? []
        );

        $this->logAiUsage($request->user(), 'meeting-agenda', $result);

        return response()->json($result);
    }

    /**
     * Categorize expense
     */
    public function categorizeExpense(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expense_description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'context' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->categorizeExpense(
            $request->expense_description,
            $request->amount,
            $request->context ?? ''
        );

        $this->logAiUsage($request->user(), 'expense-categorization', $result);

        return response()->json($result);
    }

    /**
     * Get inventory reorder suggestions
     */
    public function getInventorySuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'inventory_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateReorderSuggestions($request->inventory_data);

        $this->logAiUsage($request->user(), 'inventory-suggestions', $result);

        return response()->json($result);
    }

    /**
     * Generate email template
     */
    public function generateEmailTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_type' => 'required|string',
            'context' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateEmailTemplate(
            $request->template_type,
            $request->context ?? []
        );

        $this->logAiUsage($request->user(), 'email-template', $result);

        return response()->json($result);
    }

    /**
     * Analyze content
     */
    public function analyzeContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'type' => 'required|string|in:email,document,calendar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->analyzeContent(
            $request->content,
            $request->type
        );

        $this->logAiUsage($request->user(), 'content-analysis', $result);

        return response()->json($result);
    }

    /**
     * Get available AI models
     */
    public function getAvailableModels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'models' => [
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Fast)',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro (Advanced)',
            ]
        ]);
    }

    /**
     * Test AI API connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->geminiService->testConnection();
        return response()->json($result);
    }

    /**
     * Search across all content
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if (!$query || strlen(trim($query)) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $user = $request->user();
        $results = [];

        // Search emails
        $emails = $user->emails()
            ->where(function($q) use ($query) {
                $q->where('subject', 'ilike', "%{$query}%")
                  ->orWhere('content', 'ilike', "%{$query}%");
            })
            ->limit(10)
            ->get();

        foreach ($emails as $email) {
            $results[] = [
                'id' => $email->id,
                'type' => 'email',
                'title' => $email->subject,
                'description' => substr($email->content, 0, 100) . '...',
                'url' => '/emails',
                'timestamp' => $email->created_at->toLocaleString()
            ];
        }

        // Search documents
        $documents = $user->documents()
            ->where(function($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                  ->orWhere('content', 'ilike', "%{$query}%");
            })
            ->limit(10)
            ->get();

        foreach ($documents as $document) {
            $results[] = [
                'id' => $document->id,
                'type' => 'document',
                'title' => $document->title,
                'description' => substr($document->content, 0, 100) . '...',
                'url' => '/documents',
                'timestamp' => $document->created_at->toLocaleString()
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'query' => $query,
            'total' => count($results)
        ]);
    }

    /**
     * Get AI usage statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $stats = [
            'total_requests' => $user->aiUsage()->count(),
            'total_tokens' => $user->aiUsage()->sum('tokens_used'),
            'total_cost' => $user->aiUsage()->sum('cost'),
            'average_response_time' => $user->aiUsage()->avg('response_time_ms'),
            'most_used_features' => $user->aiUsage()
                ->selectRaw('feature, COUNT(*) as count')
                ->groupBy('feature')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->pluck('count', 'feature')
                ->toArray(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Transcribe audio using Gemini
     */
    public function transcribeAudio(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audio_file' => 'required|file|mimes:mp3,wav,webm,ogg,m4a|max:50000', // 50MB max
            'language' => 'sometimes|string|in:en,es,fr,de,it,pt,ru,ja,ko,zh',
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
            $audioData = base64_encode(file_get_contents($audioFile->getPathname()));
            $mimeType = $audioFile->getMimeType();
            $language = $request->language ?? 'en';

            $result = $this->geminiService->transcribeAudio($audioData, $mimeType, $language);

            $this->logAiUsage($request->user(), 'audio-transcription', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Audio transcription failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform OCR on image using Gemini
     */
    public function performOCR(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_file' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp|max:10000', // 10MB max
            'language' => 'sometimes|string|in:en,es,fr,de,it,pt,ru,ja,ko,zh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imageFile = $request->file('image_file');
            $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
            $mimeType = $imageFile->getMimeType();
            $language = $request->language ?? 'en';

            $result = $this->geminiService->performOCR($imageData, $mimeType, $language);

            $this->logAiUsage($request->user(), 'ocr', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'OCR failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enhanced text generation
     */
    public function generateText(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:10000',
            'tone' => 'sometimes|string|in:professional,casual,formal,friendly,technical',
            'length' => 'sometimes|string|in:short,medium,long',
            'style' => 'sometimes|string|in:formal,informal,academic,business',
            'include_bullets' => 'sometimes|boolean',
            'include_numbers' => 'sometimes|boolean',
            'max_tokens' => 'sometimes|integer|min:100|max:8192',
            'temperature' => 'sometimes|numeric|min:0|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $options = $request->only([
            'tone', 'length', 'style', 'include_bullets', 
            'include_numbers', 'max_tokens', 'temperature'
        ]);

        $result = $this->geminiService->generateText($request->prompt, $options);

        $this->logAiUsage($request->user(), 'text-generation', $result);

        return response()->json($result);
    }

    /**
     * Generate meeting notes from transcript
     */
    public function generateMeetingNotes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transcript' => 'required|string|max:50000',
            'include_action_items' => 'sometimes|boolean',
            'include_decisions' => 'sometimes|boolean',
            'include_key_points' => 'sometimes|boolean',
            'format' => 'sometimes|string|in:structured,simple,detailed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $options = $request->only([
            'include_action_items', 'include_decisions', 
            'include_key_points', 'format'
        ]);

        $result = $this->geminiService->generateMeetingNotes($request->transcript, $options);

        $this->logAiUsage($request->user(), 'meeting-notes', $result);

        return response()->json($result);
    }

    /**
     * Generate document from audio transcription
     */
    public function generateDocumentFromAudio(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transcript' => 'required|string|max:50000',
            'document_type' => 'required|string|in:meeting-minutes,report,summary,agenda',
            'options' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->geminiService->generateDocumentFromAudio(
            $request->transcript,
            $request->document_type,
            $request->options ?? []
        );

        $this->logAiUsage($request->user(), 'audio-to-document', $result);

        return response()->json($result);
    }

    /**
     * Analyze image using Gemini
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_file' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp|max:10000',
            'prompt' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imageFile = $request->file('image_file');
            $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
            $mimeType = $imageFile->getMimeType();

            $result = $this->geminiService->analyzeImage($imageData, $mimeType, $request->prompt);

            $this->logAiUsage($request->user(), 'image-analysis', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image analysis failed: ' . $e->getMessage()
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
                'model' => $result['model'] ?? 'gemini-1.5-flash',
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
                'model' => 'gemini-1.5-flash',
                'tokens_used' => 0,
                'cost' => 0,
                'response_time_ms' => 0,
                'status' => 'error',
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }
}
