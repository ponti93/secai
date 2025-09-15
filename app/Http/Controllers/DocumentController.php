<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index()
    {
        $documents = Document::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:letter,memo,report,proposal,contract,agenda,minutes,general',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,txt,rtf,odt,mp3,mp4,wav,avi,mov', // 50MB max, more file types
        ]);

        $documentData = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? 'general',
            'status' => 'draft',
        ];

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filePath = $file->storeAs('documents', $fileName, 'public');
            
            $documentData['file_name'] = $originalName;
            $documentData['file_path'] = $filePath;
            $documentData['mime_type'] = $file->getMimeType();
            $documentData['file_size'] = $file->getSize();
            
            // Auto-detect document type based on file extension
            $documentData['type'] = $this->detectDocumentType($extension, $file->getMimeType());
        }

        Document::create($documentData);

        return redirect()->route('documents.index')->with('success', 'Document created successfully!');
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('documents.edit', compact('document'));
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:letter,memo,report,proposal,contract,agenda,minutes,general',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? 'general',
        ];

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');
            
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_path'] = $filePath;
            $updateData['mime_type'] = $file->getMimeType();
            $updateData['file_size'] = $file->getSize();
        }

        $document->update($updateData);

        return redirect()->route('documents.index')->with('success', 'Document updated successfully!');
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted successfully!');
    }

    /**
     * Download the specified document file
     */
    public function download(Document $document, Request $request)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $format = $request->get('format', 'txt'); // Default to txt format
        
        // If document has an attached file, download that
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->file_name);
        }

        // Generate downloadable file from content
        $fileName = $this->generateFileName($document, $format);
        $content = $this->generateFileContent($document, $format);
        
        return response($content)
            ->header('Content-Type', $this->getMimeType($format))
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Generate filename for download
     */
    private function generateFileName(Document $document, string $format): string
    {
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document->title);
        return $baseName . '.' . $format;
    }

    /**
     * Generate file content based on format
     */
    private function generateFileContent(Document $document, string $format): string
    {
        switch ($format) {
            case 'txt':
                return $this->generateTxtContent($document);
            case 'doc':
                return $this->generateDocContent($document);
            case 'pdf':
                return $this->generatePdfContent($document);
            default:
                return $this->generateTxtContent($document);
        }
    }

    /**
     * Generate TXT content
     */
    private function generateTxtContent(Document $document): string
    {
        $content = "Title: " . $document->title . "\n";
        $content .= "Type: " . ucfirst($document->type) . "\n";
        $content .= "Status: " . ucfirst($document->status) . "\n";
        $content .= "Created: " . $document->created_at->format('F j, Y \a\t g:i A') . "\n";
        $content .= "Last Updated: " . $document->updated_at->format('F j, Y \a\t g:i A') . "\n";
        $content .= "\n" . str_repeat("=", 50) . "\n\n";
        $content .= $document->content;
        
        return $content;
    }

    /**
     * Generate DOC content (RTF format for Word compatibility)
     */
    private function generateDocContent(Document $document): string
    {
        $content = "{\\rtf1\\ansi\\deff0 {\\fonttbl {\\f0 Times New Roman;}}";
        $content .= "\\f0\\fs24 ";
        $content .= "\\b Title: " . $document->title . "\\b0\\par\\par";
        $content .= "\\b Type: " . ucfirst($document->type) . "\\b0\\par";
        $content .= "\\b Status: " . ucfirst($document->status) . "\\b0\\par";
        $content .= "\\b Created: " . $document->created_at->format('F j, Y \\a\\t g:i A') . "\\b0\\par";
        $content .= "\\b Last Updated: " . $document->updated_at->format('F j, Y \\a\\t g:i A') . "\\b0\\par\\par";
        $content .= "\\par " . str_repeat("=", 50) . "\\par\\par";
        $content .= str_replace(["\n", "\r"], ["\\par ", ""], $document->content);
        $content .= "}";
        
        return $content;
    }

    /**
     * Generate PDF content (simplified HTML that can be converted to PDF)
     */
    private function generatePdfContent(Document $document): string
    {
        $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>" . htmlspecialchars($document->title) . "</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .meta { color: #666; font-size: 12px; margin-bottom: 20px; }
        .content { line-height: 1.6; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>" . htmlspecialchars($document->title) . "</h1>
    </div>
    <div class='meta'>
        <p><strong>Type:</strong> " . htmlspecialchars(ucfirst($document->type)) . "</p>
        <p><strong>Status:</strong> " . htmlspecialchars(ucfirst($document->status)) . "</p>
        <p><strong>Created:</strong> " . $document->created_at->format('F j, Y \a\t g:i A') . "</p>
        <p><strong>Last Updated:</strong> " . $document->updated_at->format('F j, Y \a\t g:i A') . "</p>
    </div>
    <div class='content'>
        " . nl2br(htmlspecialchars($document->content)) . "
    </div>
</body>
</html>";
        
        return $html;
    }

    /**
     * Get MIME type for format
     */
    private function getMimeType(string $format): string
    {
        switch ($format) {
            case 'txt':
                return 'text/plain';
            case 'doc':
                return 'application/msword';
            case 'pdf':
                return 'text/html'; // We're generating HTML that can be saved as PDF
            default:
                return 'text/plain';
        }
    }
    
    /**
     * Auto-detect document type based on file extension and MIME type
     */
    private function detectDocumentType($extension, $mimeType)
    {
        // Audio/Video files
        if (in_array($extension, ['mp3', 'wav', 'mp4', 'avi', 'mov'])) {
            return 'general'; // Audio/video files are treated as general
        }
        
        // Document type detection based on extension
        $typeMap = [
            'pdf' => 'report',
            'doc' => 'memo',
            'docx' => 'memo',
            'txt' => 'memo',
            'rtf' => 'letter',
            'odt' => 'memo',
        ];
        
        return $typeMap[$extension] ?? 'general';
    }

    /**
     * Summarize document using AI
     */
    public function summarizeDocument(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $aiService = new \App\Services\AIDocumentService();
            $content = $this->getDocumentContent($document);
            $documentType = $this->getDocumentType($document->file_path);
            
            $result = $aiService->summarizeDocument($content, $documentType);
            
            return response()->json([
                'success' => $result['success'],
                'summary' => $result['summary'],
                'key_points' => $result['key_points'],
                'main_topics' => $result['main_topics'],
                'word_count' => $result['word_count'],
                'summary_word_count' => $result['summary_word_count']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error summarizing document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate document content using AI
     */
    public function generateContent(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'document_type' => 'nullable|string|in:report,proposal,memo,letter,agenda,minutes,general',
            'tone' => 'nullable|string|in:professional,casual,formal,technical',
            'length' => 'nullable|string|in:short,medium,long',
            'format' => 'nullable|string|in:structured,narrative,outline'
        ]);

        try {
            $aiService = new \App\Services\AIDocumentService();
            
            $options = [
                'tone' => $request->tone ?? 'professional',
                'length' => $request->length ?? 'medium',
                'format' => $request->format ?? 'structured',
                'include_intro' => $request->boolean('include_intro', true),
                'include_conclusion' => $request->boolean('include_conclusion', true)
            ];
            
            $result = $aiService->generateDocumentContent(
                $request->prompt,
                $request->document_type ?? 'general',
                $options
            );
            
            return response()->json([
                'success' => $result['success'],
                'content' => $result['content'],
                'word_count' => $result['word_count'],
                'document_type' => $result['document_type'],
                'options' => $result['options']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract keywords from document
     */
    public function extractKeywords(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $aiService = new \App\Services\AIDocumentService();
            $content = $this->getDocumentContent($document);
            $maxKeywords = request('max_keywords', 10);
            
            $result = $aiService->extractKeywords($content, $maxKeywords);
            
            return response()->json([
                'success' => $result['success'],
                'keywords' => $result['keywords']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error extracting keywords: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze document content
     */
    public function analyzeDocument(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $aiService = new \App\Services\AIDocumentService();
            $content = $this->getDocumentContent($document);
            
            $result = $aiService->analyzeDocument($content);
            
            return response()->json([
                'success' => $result['success'],
                'readability_score' => $result['readability_score'],
                'tone' => $result['tone'],
                'quality_score' => $result['quality_score'],
                'suggestions' => $result['suggestions'],
                'strengths' => $result['strengths'],
                'word_count' => $result['word_count'],
                'reading_time' => $result['reading_time']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document content for AI processing
     */
    private function getDocumentContent(Document $document): string
    {
        try {
            $filePath = storage_path('app/' . $document->file_path);
            
            if (!file_exists($filePath)) {
                return '';
            }
            
            $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
            
            switch ($extension) {
                case 'txt':
                case 'md':
                    return file_get_contents($filePath);
                
                case 'pdf':
                    // For PDF, we'll need a PDF parser library
                    // For now, return a placeholder
                    return 'PDF content extraction not implemented yet.';
                
                case 'doc':
                case 'docx':
                    // For Word docs, we'll need a Word parser library
                    // For now, return a placeholder
                    return 'Word document content extraction not implemented yet.';
                
                default:
                    return file_get_contents($filePath);
            }
        } catch (\Exception $e) {
            Log::error('Error reading document content: ' . $e->getMessage());
            return '';
        }
    }
}
