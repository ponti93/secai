@extends('layouts.app')

@section('title', 'AI Tools - SecretaryAI')
@section('page-title', 'AI Dashboard')

@section('content')
<div class="row">
    <!-- AI Status Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-cpu"></i> AI Status
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                </div>
                <h5 class="text-success">AI Connected</h5>
                <p class="text-muted">Gemini API is active and ready</p>
                <button class="btn btn-outline-primary btn-sm" onclick="testAIConnection()">
                    Test Connection
                </button>
            </div>
        </div>
    </div>
    
    <!-- Usage Stats -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up"></i> Usage Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row" id="usage-stats">
                    <div class="col-md-3 text-center">
                        <h4 class="text-primary" id="total-requests">-</h4>
                        <small class="text-muted">Total Requests</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-info" id="total-tokens">-</h4>
                        <small class="text-muted">Tokens Used</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-success" id="total-cost">-</h4>
                        <small class="text-muted">Total Cost</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-warning" id="avg-response">-</h4>
                        <small class="text-muted">Avg Response (ms)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- AI Tools -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-tools"></i> AI Tools
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Text & Document Tools -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-envelope text-primary fs-1 mb-3"></i>
                                <h5>Email Reply Generator</h5>
                                <p class="text-muted">Generate AI-powered email replies</p>
                                <button class="btn btn-primary" onclick="openEmailReplyTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-text text-success fs-1 mb-3"></i>
                                <h5>Document Generator</h5>
                                <p class="text-muted">Create documents with AI assistance</p>
                                <button class="btn btn-success" onclick="openDocumentTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audio & Transcription Tools -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-mic text-info fs-1 mb-3"></i>
                                <h5>Audio Transcription</h5>
                                <p class="text-muted">Convert audio to text with Gemini AI</p>
                                <button class="btn btn-info" onclick="openAudioTranscriptionTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-event text-warning fs-1 mb-3"></i>
                                <h5>Meeting Notes Generator</h5>
                                <p class="text-muted">Generate structured meeting notes</p>
                                <button class="btn btn-warning" onclick="openMeetingNotesTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- OCR & Image Tools -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-image text-danger fs-1 mb-3"></i>
                                <h5>OCR Text Extraction</h5>
                                <p class="text-muted">Extract text from images</p>
                                <button class="btn btn-danger" onclick="openOCRTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-eye text-secondary fs-1 mb-3"></i>
                                <h5>Image Analysis</h5>
                                <p class="text-muted">Analyze images with AI</p>
                                <button class="btn btn-secondary" onclick="openImageAnalysisTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Text Generation -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-pencil-square text-dark fs-1 mb-3"></i>
                                <h5>Enhanced Text Generation</h5>
                                <p class="text-muted">Advanced text generation with options</p>
                                <button class="btn btn-dark" onclick="openTextGenerationTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-receipt text-warning fs-1 mb-3"></i>
                                <h5>Expense Categorizer</h5>
                                <p class="text-muted">Automatically categorize expenses</p>
                                <button class="btn btn-warning" onclick="openExpenseTool()">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent AI Activity -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history"></i> Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <div id="recent-ai-activity">
                    <!-- Recent AI activity will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Tool Modals -->
<!-- Email Reply Tool Modal -->
<div class="modal fade" id="emailReplyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Reply Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="emailReplyForm">
                    <div class="mb-3">
                        <label for="emailContent" class="form-label">Email Content</label>
                        <textarea class="form-control" id="emailContent" rows="6" placeholder="Paste the email you want to reply to..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="replyContext" class="form-label">Additional Context (Optional)</label>
                        <textarea class="form-control" id="replyContext" rows="3" placeholder="Any additional context for the reply..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateEmailReply()">Generate Reply</button>
            </div>
        </div>
    </div>
</div>

<!-- Document Generator Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="documentForm">
                    <div class="mb-3">
                        <label for="documentPrompt" class="form-label">What would you like to create?</label>
                        <textarea class="form-control" id="documentPrompt" rows="4" placeholder="Describe the document you want to create..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="documentType" class="form-label">Document Type</label>
                            <select class="form-select" id="documentType">
                                <option value="general">General</option>
                                <option value="letter">Letter</option>
                                <option value="memo">Memo</option>
                                <option value="report">Report</option>
                                <option value="proposal">Proposal</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="documentTone" class="form-label">Tone</label>
                            <select class="form-select" id="documentTone">
                                <option value="professional">Professional</option>
                                <option value="casual">Casual</option>
                                <option value="formal">Formal</option>
                                <option value="friendly">Friendly</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div id="documentResult" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Generated Document:</h6>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="copyDocument()">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadDocument()">
                                <i class="bi bi-download"></i> Download
                            </button>
                        </div>
                    </div>
                    <div class="border p-3 bg-light rounded" id="documentText" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="generateDocument()">Generate Document</button>
            </div>
        </div>
    </div>
</div>

<!-- Audio Transcription Modal -->
<div class="modal fade" id="audioTranscriptionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audio Transcription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="audioTranscriptionForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="audioFile" class="form-label">Audio File</label>
                        <input type="file" class="form-control" id="audioFile" accept="audio/*" required>
                        <div class="form-text">Supported formats: MP3, WAV, WebM, OGG, M4A (Max 50MB)</div>
                    </div>
                    <div class="mb-3">
                        <label for="audioLanguage" class="form-label">Language</label>
                        <select class="form-select" id="audioLanguage">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="pt">Portuguese</option>
                            <option value="ru">Russian</option>
                            <option value="ja">Japanese</option>
                            <option value="ko">Korean</option>
                            <option value="zh">Chinese</option>
                        </select>
                    </div>
                </form>
                <div id="transcriptionResult" class="mt-3" style="display: none;">
                    <h6>Transcription Result:</h6>
                    <div class="border p-3 bg-light rounded" id="transcriptionText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="transcribeAudio()">Transcribe Audio</button>
            </div>
        </div>
    </div>
</div>

<!-- OCR Tool Modal -->
<div class="modal fade" id="ocrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">OCR Text Extraction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ocrForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imageFile" class="form-label">Image File</label>
                        <input type="file" class="form-control" id="imageFile" accept="image/*" required>
                        <div class="form-text">Supported formats: JPG, PNG, GIF, BMP, WebP (Max 10MB)</div>
                    </div>
                    <div class="mb-3">
                        <label for="ocrLanguage" class="form-label">Language</label>
                        <select class="form-select" id="ocrLanguage">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="pt">Portuguese</option>
                            <option value="ru">Russian</option>
                            <option value="ja">Japanese</option>
                            <option value="ko">Korean</option>
                            <option value="zh">Chinese</option>
                        </select>
                    </div>
                </form>
                <div id="ocrResult" class="mt-3" style="display: none;">
                    <h6>Extracted Text:</h6>
                    <div class="border p-3 bg-light rounded" id="ocrText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="performOCR()">Extract Text</button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Text Generation Modal -->
<div class="modal fade" id="textGenerationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enhanced Text Generation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="textGenerationForm">
                    <div class="mb-3">
                        <label for="textPrompt" class="form-label">What would you like to generate?</label>
                        <textarea class="form-control" id="textPrompt" rows="4" placeholder="Describe what you want to generate..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="textTone" class="form-label">Tone</label>
                            <select class="form-select" id="textTone">
                                <option value="professional">Professional</option>
                                <option value="casual">Casual</option>
                                <option value="formal">Formal</option>
                                <option value="friendly">Friendly</option>
                                <option value="technical">Technical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="textLength" class="form-label">Length</label>
                            <select class="form-select" id="textLength">
                                <option value="short">Short</option>
                                <option value="medium" selected>Medium</option>
                                <option value="long">Long</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="textStyle" class="form-label">Style</label>
                            <select class="form-select" id="textStyle">
                                <option value="formal">Formal</option>
                                <option value="informal">Informal</option>
                                <option value="academic">Academic</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeBullets">
                                <label class="form-check-label" for="includeBullets">
                                    Include bullet points
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeNumbers">
                                <label class="form-check-label" for="includeNumbers">
                                    Include numbered lists
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="textGenerationResult" class="mt-3" style="display: none;">
                    <h6>Generated Text:</h6>
                    <div class="border p-3 bg-light rounded" id="generatedText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" onclick="generateText()">Generate Text</button>
            </div>
        </div>
    </div>
</div>

<!-- Meeting Notes Generator Modal -->
<div class="modal fade" id="meetingNotesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Meeting Notes Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="meetingNotesForm">
                    <div class="mb-3">
                        <label for="meetingTranscript" class="form-label">Meeting Transcript</label>
                        <textarea class="form-control" id="meetingTranscript" rows="8" placeholder="Paste the meeting transcript here..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeActionItems" checked>
                                <label class="form-check-label" for="includeActionItems">
                                    Include action items
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeDecisions" checked>
                                <label class="form-check-label" for="includeDecisions">
                                    Include decisions
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeKeyPoints" checked>
                                <label class="form-check-label" for="includeKeyPoints">
                                    Include key points
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="notesFormat" class="form-label">Format</label>
                            <select class="form-select" id="notesFormat">
                                <option value="structured">Structured</option>
                                <option value="simple">Simple</option>
                                <option value="detailed">Detailed</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div id="meetingNotesResult" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Generated Meeting Notes:</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyMeetingNotes()">
                            <i class="bi bi-clipboard"></i> Copy Notes
                        </button>
                    </div>
                    <div class="border p-3 bg-light rounded" id="meetingNotesText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="generateMeetingNotes()">Generate Notes</button>
            </div>
        </div>
    </div>
</div>

<!-- Expense Categorizer Modal -->
<div class="modal fade" id="expenseCategorizerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Expense Categorizer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseCategorizerForm">
                    <div class="mb-3">
                        <label for="expenseDescription" class="form-label">Expense Description</label>
                        <textarea class="form-control" id="expenseDescription" rows="3" placeholder="Enter the expense description (e.g., 'Lunch at McDonald's', 'Office supplies from Staples')"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expenseAmount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="expenseAmount" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expenseContext" class="form-label">Additional Context (Optional)</label>
                                <input type="text" class="form-control" id="expenseContext" placeholder="Any additional context...">
                            </div>
                        </div>
                    </div>
                </form>
                <div id="expenseCategorizerResult" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Categorization Result:</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyExpenseResult()">
                            <i class="bi bi-clipboard"></i> Copy Result
                        </button>
                    </div>
                    <div class="border p-3 bg-light rounded" id="expenseCategorizerText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="categorizeExpense()">Categorize Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Analysis Modal -->
<div class="modal fade" id="imageAnalysisModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="imageAnalysisForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="analysisImageFile" class="form-label">Image File</label>
                        <input type="file" class="form-control" id="analysisImageFile" accept="image/*" required>
                        <div class="form-text">Supported formats: JPG, PNG, GIF, BMP, WebP (Max 10MB)</div>
                    </div>
                    <div class="mb-3">
                        <label for="analysisPrompt" class="form-label">Analysis Prompt</label>
                        <textarea class="form-control" id="analysisPrompt" rows="3" placeholder="What would you like to know about this image?"></textarea>
                    </div>
                </form>
                <div id="imageAnalysisResult" class="mt-3" style="display: none;">
                    <h6>Analysis Result:</h6>
                    <div class="border p-3 bg-light rounded" id="imageAnalysisText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="analyzeImage()">Analyze Image</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAIStats();
    loadRecentActivity();
});

function loadAIStats() {
    fetch('/api/ai/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-requests').textContent = data.stats.total_requests || 0;
                document.getElementById('total-tokens').textContent = data.stats.total_tokens || 0;
                document.getElementById('total-cost').textContent = '$' + (parseFloat(data.stats.total_cost) || 0).toFixed(4);
                document.getElementById('avg-response').textContent = Math.round(data.stats.average_response_time || 0);
            }
        })
        .catch(error => console.error('Error loading AI stats:', error));
}

function loadRecentActivity() {
    // This would load recent AI activity
    document.getElementById('recent-ai-activity').innerHTML = `
        <div class="text-center text-muted">
            <i class="bi bi-clock-history fs-1"></i>
            <p class="mt-2">No recent activity</p>
        </div>
    `;
}

function testAIConnection() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Testing...';
    button.disabled = true;
    
    fetch('/api/ai/test')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('AI connection successful!');
            } else {
                alert('AI connection failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error testing AI connection: ' + error.message);
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
}

function openEmailReplyTool() {
    new bootstrap.Modal(document.getElementById('emailReplyModal')).show();
}

function openDocumentTool() {
    new bootstrap.Modal(document.getElementById('documentModal')).show();
}

function openAudioTranscriptionTool() {
    new bootstrap.Modal(document.getElementById('audioTranscriptionModal')).show();
}

function openOCRTool() {
    new bootstrap.Modal(document.getElementById('ocrModal')).show();
}

function openTextGenerationTool() {
    new bootstrap.Modal(document.getElementById('textGenerationModal')).show();
}

function openMeetingNotesTool() {
    new bootstrap.Modal(document.getElementById('meetingNotesModal')).show();
}

function openImageAnalysisTool() {
    new bootstrap.Modal(document.getElementById('imageAnalysisModal')).show();
}

function openMeetingTool() {
    alert('Meeting tool will be implemented');
}

function openExpenseTool() {
    new bootstrap.Modal(document.getElementById('expenseCategorizerModal')).show();
}

function generateEmailReply() {
    const emailContent = document.getElementById('emailContent').value;
    const context = document.getElementById('replyContext').value;
    
    if (!emailContent.trim()) {
        alert('Please enter email content');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Generating...';
    button.disabled = true;
    
    fetch('/api/ai/email-reply', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            email_content: emailContent,
            context: context
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show the generated reply
            alert('Generated Reply:\n\n' + data.content);
        } else {
            alert('Error generating reply: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function generateDocument() {
    const prompt = document.getElementById('documentPrompt').value;
    const type = document.getElementById('documentType').value;
    const tone = document.getElementById('documentTone').value;
    
    if (!prompt.trim()) {
        alert('Please enter a prompt');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Generating...';
    button.disabled = true;
    
    fetch('/api/ai/generate-document', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            prompt: prompt,
            document_type: type,
            tone: tone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show the generated document in modal
            document.getElementById('documentText').textContent = data.content;
            document.getElementById('documentResult').style.display = 'block';
        } else {
            alert('Error generating document: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function copyDocument() {
    const documentText = document.getElementById('documentText').textContent;
    
    if (!documentText.trim()) {
        alert('No document to copy');
        return;
    }
    
    navigator.clipboard.writeText(documentText).then(() => {
        // Show success feedback
        const button = event.target;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        alert('Failed to copy document. Please try again.');
    });
}

function downloadDocument() {
    const documentText = document.getElementById('documentText').textContent;
    const documentType = document.getElementById('documentType').value;
    
    if (!documentText.trim()) {
        alert('No document to download');
        return;
    }
    
    // Create a blob with the document content
    const blob = new Blob([documentText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    
    // Create a temporary link element
    const link = document.createElement('a');
    link.href = url;
    link.download = `generated-${documentType}-${Date.now()}.txt`;
    
    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    window.URL.revokeObjectURL(url);
}

// Audio Transcription
function transcribeAudio() {
    const audioFile = document.getElementById('audioFile').files[0];
    const language = document.getElementById('audioLanguage').value;
    
    if (!audioFile) {
        alert('Please select an audio file');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Transcribing...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('audio_file', audioFile);
    formData.append('language', language);
    
    fetch('/api/ai/transcribe-audio', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('transcriptionText').textContent = data.transcript;
            document.getElementById('transcriptionResult').style.display = 'block';
        } else {
            alert('Error transcribing audio: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// OCR
function performOCR() {
    const imageFile = document.getElementById('imageFile').files[0];
    const language = document.getElementById('ocrLanguage').value;
    
    if (!imageFile) {
        alert('Please select an image file');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Extracting...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('image_file', imageFile);
    formData.append('language', language);
    
    fetch('/api/ai/ocr', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('ocrText').textContent = data.text;
            document.getElementById('ocrResult').style.display = 'block';
        } else {
            alert('Error extracting text: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// Enhanced Text Generation
function generateText() {
    const prompt = document.getElementById('textPrompt').value;
    const tone = document.getElementById('textTone').value;
    const length = document.getElementById('textLength').value;
    const style = document.getElementById('textStyle').value;
    const includeBullets = document.getElementById('includeBullets').checked;
    const includeNumbers = document.getElementById('includeNumbers').checked;
    
    if (!prompt.trim()) {
        alert('Please enter a prompt');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Generating...';
    button.disabled = true;
    
    fetch('/api/ai/generate-text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            prompt: prompt,
            tone: tone,
            length: length,
            style: style,
            include_bullets: includeBullets,
            include_numbers: includeNumbers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generatedText').textContent = data.content;
            document.getElementById('textGenerationResult').style.display = 'block';
        } else {
            alert('Error generating text: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// Meeting Notes Generation
function generateMeetingNotes() {
    const transcript = document.getElementById('meetingTranscript').value;
    const includeActionItems = document.getElementById('includeActionItems').checked;
    const includeDecisions = document.getElementById('includeDecisions').checked;
    const includeKeyPoints = document.getElementById('includeKeyPoints').checked;
    const format = document.getElementById('notesFormat').value;
    
    if (!transcript.trim()) {
        alert('Please enter a meeting transcript');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Generating...';
    button.disabled = true;
    
    fetch('/api/ai/meeting-notes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            transcript: transcript,
            include_action_items: includeActionItems,
            include_decisions: includeDecisions,
            include_key_points: includeKeyPoints,
            format: format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('meetingNotesText').textContent = data.content;
            document.getElementById('meetingNotesResult').style.display = 'block';
        } else {
            alert('Error generating meeting notes: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function copyMeetingNotes() {
    const notesText = document.getElementById('meetingNotesText').textContent;
    
    if (!notesText.trim()) {
        alert('No meeting notes to copy');
        return;
    }
    
    navigator.clipboard.writeText(notesText).then(() => {
        // Show success feedback
        const button = event.target;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        alert('Failed to copy meeting notes. Please try again.');
    });
}

function categorizeExpense() {
    const description = document.getElementById('expenseDescription').value;
    const amount = document.getElementById('expenseAmount').value;
    const context = document.getElementById('expenseContext').value;
    
    if (!description.trim()) {
        alert('Please enter an expense description');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Categorizing...';
    button.disabled = true;
    
    fetch('/api/ai/categorize-expense', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            expense_description: description,
            amount: parseFloat(amount) || 0,
            context: context
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('expenseCategorizerText').textContent = data.content;
            document.getElementById('expenseCategorizerResult').style.display = 'block';
        } else {
            alert('Error categorizing expense: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function copyExpenseResult() {
    const resultText = document.getElementById('expenseCategorizerText').textContent;
    
    if (!resultText.trim()) {
        alert('No result to copy');
        return;
    }
    
    navigator.clipboard.writeText(resultText).then(() => {
        // Show success feedback
        const button = event.target;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        alert('Failed to copy result. Please try again.');
    });
}

// Image Analysis
function analyzeImage() {
    const imageFile = document.getElementById('analysisImageFile').files[0];
    const prompt = document.getElementById('analysisPrompt').value;
    
    if (!imageFile) {
        alert('Please select an image file');
        return;
    }
    
    if (!prompt.trim()) {
        alert('Please enter an analysis prompt');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Analyzing...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('image_file', imageFile);
    formData.append('prompt', prompt);
    
    fetch('/api/ai/analyze-image', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('imageAnalysisText').textContent = data.response;
            document.getElementById('imageAnalysisResult').style.display = 'block';
        } else {
            alert('Error analyzing image: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}
</script>
@endsection
