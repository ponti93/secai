<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\AIEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Display a listing of emails
     */
    public function index()
    {
        $emails = Email::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('emails.index', compact('emails'));
    }

    /**
     * Show the form for creating a new email
     */
    public function create()
    {
        return view('emails.create');
    }

    /**
     * Store a newly created email and send it
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
            'to_email' => 'required|email|max:255',
            'content' => 'required|string',
            'cc_emails' => 'nullable|string',
            'bcc_emails' => 'nullable|string',
            'is_important' => 'nullable|boolean',
        ]);

        // Parse CC and BCC emails
        $ccEmails = $request->cc_emails ? array_filter(array_map('trim', explode(',', $request->cc_emails))) : [];
        $bccEmails = $request->bcc_emails ? array_filter(array_map('trim', explode(',', $request->bcc_emails))) : [];

        // Create email record
        $email = Auth::user()->emails()->create([
            'subject' => $request->subject,
            'from_email' => $request->from_email,
            'to_email' => $request->to_email,
            'content' => $request->content,
            'cc_emails' => $ccEmails,
            'bcc_emails' => $bccEmails,
            'is_important' => $request->has('is_important'),
            'status' => 'sending',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        try {
            // Send the email using SMTP
            $mail = Mail::raw($request->content, function ($message) use ($request, $ccEmails, $bccEmails) {
                $message->to($request->to_email)
                        ->subject($request->subject)
                        ->from($request->from_email, config('app.name'));
                
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
                
                if (!empty($bccEmails)) {
                    $message->bcc($bccEmails);
                }
            });

            // Update email status to sent
            $email->update(['status' => 'sent']);

            Log::info('Email sent successfully', [
                'to' => $request->to_email,
                'subject' => $request->subject,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('emails.index')->with('success', 'Email sent successfully!');

        } catch (\Exception $e) {
            // Update email status to failed
            $email->update(['status' => 'failed']);

            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'to' => $request->to_email,
                'subject' => $request->subject,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified email
     */
    public function show(Email $email)
    {
        // Check if user owns this email
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('emails.show', compact('email'));
    }

    /**
     * Show the form for editing the specified email
     */
    public function edit(Email $email)
    {
        // Check if user owns this email
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('emails.edit', compact('email'));
    }

    /**
     * Update the specified email
     */
    public function update(Request $request, Email $email)
    {
        // Check if user owns this email
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'sender' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:urgent,schedule,followup,info',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
        ]);

        $email->update($request->only([
            'subject', 'sender', 'recipient', 'content', 'type', 'priority'
        ]));

        return redirect()->route('emails.index')->with('success', 'Email updated successfully!');
    }

    /**
     * Remove the specified email
     */
    public function destroy(Email $email)
    {
        // Check if user owns this email
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }

        $email->delete();
        return redirect()->route('emails.index')->with('success', 'Email deleted successfully!');
    }

    /**
     * Analyze email with AI
     */
    public function analyzeEmail(Email $email)
    {
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }

        $aiService = new AIEmailService();
        
        // Categorize email
        $categorization = $aiService->categorizeEmail($email->subject, $email->content);
        
        // Analyze sentiment
        $sentiment = $aiService->analyzeSentiment($email->content);
        
        // Extract key information
        $keyInfo = $aiService->extractKeyInfo($email->content);
        
        // Generate summary
        $summary = $aiService->generateSummary($email->content);
        
        // Update email with AI data
        $email->update([
            'ai_category' => $categorization['category'],
            'ai_priority' => $categorization['priority'],
            'ai_sentiment' => $sentiment,
            'ai_key_info' => $keyInfo,
            'ai_suggested_action' => $categorization['suggested_action'],
            'ai_summary' => $summary,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $categorization['category'],
                'priority' => $categorization['priority'],
                'sentiment' => $sentiment,
                'key_info' => $keyInfo,
                'suggested_action' => $categorization['suggested_action'],
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate AI reply suggestions
     */
    public function generateReplySuggestions(Email $email)
    {
        if ($email->user_id !== Auth::id()) {
            abort(403);
        }

        $aiService = new AIEmailService();
        $suggestions = $aiService->generateReplySuggestions($email->content, $email->subject);
        
        Log::info('Generated reply suggestions', [
            'email_id' => $email->id,
            'suggestions_count' => count($suggestions),
            'suggestions' => $suggestions
        ]);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Get AI insights for all emails
     */
    public function getAIInsights()
    {
        $emails = Email::where('user_id', Auth::id())
            ->whereNotNull('ai_category')
            ->get();

        $insights = [
            'total_analyzed' => $emails->count(),
            'categories' => $emails->groupBy('ai_category')->map->count(),
            'priorities' => $emails->groupBy('ai_priority')->map->count(),
            'sentiments' => $emails->pluck('ai_sentiment')->filter()->groupBy('sentiment')->map->count(),
            'suggested_actions' => $emails->groupBy('ai_suggested_action')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'insights' => $insights
        ]);
    }

    /**
     * Generate email subject using AI
     */
    public function generateSubject(Request $request)
    {
        $request->validate([
            'to_email' => 'nullable|email',
            'content' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $aiService = new AIEmailService();
        
        // Use description if provided, otherwise use content
        $context = $request->description ?: $request->content;
        
        $prompt = "Generate a professional email subject line based on this description: '{$context}'
        
        To: {$request->to_email}
        
        Return only the subject line, no quotes or extra text. Make it clear and professional.";

        $subject = $aiService->callGemini($prompt);
        
        Log::info('Generated subject: ' . ($subject ?: 'NULL'));

        return response()->json([
            'success' => true,
            'subject' => $subject ?: 'Meeting Request'
        ]);
    }

    /**
     * Generate email content using AI
     */
    public function generateContent(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string',
            'to_email' => 'nullable|email',
            'description' => 'required|string'
        ]);

        $aiService = new AIEmailService();
        
        $prompt = "Generate a professional email based on this description: '{$request->description}'
        
        Additional context:
        Subject: {$request->subject}
        To: {$request->to_email}
        
        Make it professional, clear, and appropriate for the context. Include proper greeting and closing.
        Use the description as a guide for the tone and content of the email.";

        $content = $aiService->callGemini($prompt);
        
        Log::info('Generated content: ' . substr($content ?: 'NULL', 0, 100));

        return response()->json([
            'success' => true,
            'content' => $content ?: 'Dear ' . ($request->to_email ? explode('@', $request->to_email)[0] : 'Recipient') . ",\n\n[Your message here]\n\nBest regards,\n[Your name]"
        ]);
    }

    /**
     * Improve email content using AI
     */
    public function improveContent(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $aiService = new AIEmailService();
        
        $prompt = "Improve this email content to make it more professional, clear, and effective:
        
        Original Content: {$request->content}
        
        Return the improved version with better structure, grammar, and tone.";

        $improvedContent = $aiService->callGemini($prompt);

        return response()->json([
            'success' => true,
            'improved_content' => $improvedContent ?: $request->content
        ]);
    }

    /**
     * Check grammar using AI
     */
    public function checkGrammar(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $aiService = new AIEmailService();
        
        $prompt = "Check and correct the grammar, spelling, and punctuation in this email content. Return the corrected version:
        
        Content: {$request->content}
        
        Return only the corrected content, no explanations.";

        $correctedContent = $aiService->callGemini($prompt);

        return response()->json([
            'success' => true,
            'corrected_content' => $correctedContent ?: $request->content
        ]);
    }

    /**
     * Call Gemini AI API (moved from service for direct access)
     */
    private function callGemini(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . config('services.gemini.api_key'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send test email
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email',
            'from_email' => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            // Send the test email using SMTP
            Mail::raw($request->content, function ($message) use ($request) {
                $message->to($request->to_email)
                        ->subject($request->subject)
                        ->from($request->from_email, config('app.name'));
            });

            Log::info('Test email sent successfully', [
                'to' => $request->to_email,
                'subject' => $request->subject,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'to' => $request->to_email,
                'subject' => $request->subject,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ]);
        }
    }
}
