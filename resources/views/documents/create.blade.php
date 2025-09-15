@extends('layouts.app')

@section('title', 'Create Document - SecretaryAI')
@section('page-title', 'Create Document')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create New Document</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Document Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type">
                            <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="letter" {{ old('type') == 'letter' ? 'selected' : '' }}>Letter</option>
                            <option value="memo" {{ old('type') == 'memo' ? 'selected' : '' }}>Memo</option>
                            <option value="report" {{ old('type') == 'report' ? 'selected' : '' }}>Report</option>
                            <option value="proposal" {{ old('type') == 'proposal' ? 'selected' : '' }}>Proposal</option>
                            <option value="contract" {{ old('type') == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="agenda" {{ old('type') == 'agenda' ? 'selected' : '' }}>Agenda</option>
                            <option value="minutes" {{ old('type') == 'minutes' ? 'selected' : '' }}>Meeting Minutes</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File (Optional)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" 
                               id="file" name="file" accept=".pdf,.doc,.docx,.txt,.rtf">
                        <div class="form-text">Supported formats: PDF, DOC, DOCX, TXT, RTF (Max 10MB)</div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span></span>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="showAIGenerateModal()">
                                <i class="bi bi-robot me-2"></i>AI Generate Content
                            </button>
                        </div>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="15" required 
                                  placeholder="Enter your document content here...">{{ old('content', request('generated_content')) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Documents
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Create Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// AI Document Generation
function showAIGenerateModal() {
    const modalHtml = `
        <div class="modal fade" id="aiGenerateModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Content Generator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="aiGenerateForm">
                            <div class="mb-3">
                                <label for="prompt" class="form-label">What would you like to create? *</label>
                                <textarea class="form-control" id="prompt" name="prompt" rows="3" 
                                    placeholder="Describe the content you want to generate... (e.g., 'Create a quarterly sales report for Q3 2024')" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="document_type" class="form-label">Document Type</label>
                                        <select class="form-select" id="document_type" name="document_type">
                                            <option value="general">General</option>
                                            <option value="report">Report</option>
                                            <option value="proposal">Proposal</option>
                                            <option value="memo">Memo</option>
                                            <option value="letter">Letter</option>
                                            <option value="agenda">Agenda</option>
                                            <option value="minutes">Meeting Minutes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tone" class="form-label">Tone</label>
                                        <select class="form-select" id="tone" name="tone">
                                            <option value="professional">Professional</option>
                                            <option value="casual">Casual</option>
                                            <option value="formal">Formal</option>
                                            <option value="technical">Technical</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="length" class="form-label">Length</label>
                                        <select class="form-select" id="length" name="length">
                                            <option value="short">Short (1-2 pages)</option>
                                            <option value="medium" selected>Medium (2-5 pages)</option>
                                            <option value="long">Long (5+ pages)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="format" class="form-label">Format</label>
                                        <select class="form-select" id="format" name="format">
                                            <option value="structured" selected>Structured</option>
                                            <option value="narrative">Narrative</option>
                                            <option value="outline">Outline</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_intro" name="include_intro" checked>
                                <label class="form-check-label" for="include_intro">Include Introduction</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_conclusion" name="include_conclusion" checked>
                                <label class="form-check-label" for="include_conclusion">Include Conclusion</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="generateContent()">
                            <i class="bi bi-robot me-2"></i>Generate Content
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('aiGenerateModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('aiGenerateModal'));
    modal.show();
}

function generateContent() {
    const form = document.getElementById('aiGenerateForm');
    const formData = new FormData(form);
    
    // Show loading state
    const generateBtn = document.querySelector('[onclick="generateContent()"]');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
    generateBtn.disabled = true;
    
    fetch('{{ route("documents.ai.generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fill the content textarea with generated content
            document.getElementById('content').value = data.content;
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('aiGenerateModal'));
            modal.hide();
            
            // Show success message
            alert('Content generated successfully! You can now edit it before saving.');
        } else {
            alert('Failed to generate content: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating content. Please try again.');
    })
    .finally(() => {
        // Reset button state
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}
</script>
@endsection