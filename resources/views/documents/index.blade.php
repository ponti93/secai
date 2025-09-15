@extends('layouts.app')

@section('title', 'Documents - SecretaryAI')
@section('page-title', 'Document Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ New Document
    </a>
    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
    <button type="button" class="btn btn-info" onclick="showAIGenerateModal()">
        <i class="bi bi-robot me-2"></i>AI Generate
    </button>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Filters</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search documents...">
                </div>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-info" data-filter="report">Reports</button>
                    <button type="button" class="btn btn-outline-success" data-filter="letter">Letters</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="proposal">Proposals</button>
                    <button type="button" class="btn btn-outline-secondary" data-filter="general">General</button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary">{{ $documents->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-info">{{ $documents->where('type', 'report')->count() }}</div>
                        <small class="text-muted">Reports</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success">{{ $documents->where('type', 'letter')->count() }}</div>
                        <small class="text-muted">Letters</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning">{{ $documents->where('type', 'proposal')->count() }}</div>
                        <small class="text-muted">Proposals</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Documents List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Documents</h6>
            </div>
            <div class="card-body p-0">
                @if($documents->count() > 0)
                    @foreach($documents as $document)
                    <div class="document-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $document->title }}</h6>
                                    <span class="badge bg-info">{{ ucfirst($document->type) }}</span>
                                    @if($document->file_name)
                                        <i class="bi bi-paperclip text-muted ms-2"></i>
                                    @endif
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-calendar me-1"></i>{{ $document->created_at->format('M j, Y') }}
                                    @if($document->file_name)
                                        <i class="bi bi-file-earmark me-1 ms-2"></i>{{ $document->file_name }}
                                    @endif
                                </p>
                                <p class="text-muted small mb-0">{{ Str::limit($document->content, 100) }}</p>
                            </div>
                            <div class="text-end">
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-success btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm" title="AI Summarize" onclick="summarizeDocument({{ $document->id }})">
                                        <i class="bi bi-file-text"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" title="AI Keywords" onclick="extractKeywords({{ $document->id }})">
                                        <i class="bi bi-tags"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" title="AI Analyze" onclick="analyzeDocument({{ $document->id }})">
                                        <i class="bi bi-graph-up"></i>
                                    </button>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Download">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('documents.download', $document) }}?format=txt">
                                                <i class="bi bi-file-text me-2"></i>Download as TXT
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('documents.download', $document) }}?format=doc">
                                                <i class="bi bi-file-word me-2"></i>Download as DOC
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('documents.download', $document) }}?format=pdf">
                                                <i class="bi bi-file-pdf me-2"></i>Download as PDF
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-file-earmark-text text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No documents found</p>
                        <a href="{{ route('documents.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Your First Document
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Simple JavaScript for basic functionality
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterDocuments();
        });
    }
    
    // Filter buttons
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            
            // Update active state
            document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            filterDocuments(filter);
        });
    });
});

function filterDocuments(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const documentItems = document.querySelectorAll('.document-item');
    
    documentItems.forEach(item => {
        const title = item.querySelector('h6').textContent.toLowerCase();
        const content = item.querySelector('.text-muted').textContent.toLowerCase();
        const typeBadge = item.querySelector('.badge');
        const type = typeBadge ? typeBadge.textContent.toLowerCase().trim() : '';
        
        const matchesSearch = title.includes(searchTerm) || content.includes(searchTerm);
        
        let matchesFilter = true;
        if (filter !== 'all') {
            matchesFilter = type === filter;
        }
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

// AI Document Features
function showAIGenerateModal() {
    const modalHtml = `
        <div class="modal fade" id="aiGenerateModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Document Generator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="aiGenerateForm">
                            <div class="mb-3">
                                <label for="prompt" class="form-label">What would you like to create? *</label>
                                <textarea class="form-control" id="prompt" name="prompt" rows="3" 
                                    placeholder="Describe the document you want to create... (e.g., 'Create a quarterly sales report for Q3 2024')" required></textarea>
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
                        <button type="button" class="btn btn-primary" onclick="generateDocument()">
                            <i class="bi bi-robot me-2"></i>Generate Document
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

function generateDocument() {
    const form = document.getElementById('aiGenerateForm');
    const formData = new FormData(form);
    
    // Show loading state
    const generateBtn = document.querySelector('[onclick="generateDocument()"]');
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
            // Redirect to create page with generated content
            const createUrl = '{{ route("documents.create") }}?generated_content=' + encodeURIComponent(data.content);
            window.location.href = createUrl;
        } else {
            alert('Failed to generate document: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating document. Please try again.');
    })
    .finally(() => {
        // Reset button state
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}

function summarizeDocument(documentId) {
    fetch(`/documents/${documentId}/summarize`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSummaryModal(data);
        } else {
            alert('Failed to summarize document: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error summarizing document. Please try again.');
    });
}

function extractKeywords(documentId) {
    fetch(`/documents/${documentId}/keywords`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showKeywordsModal(data.keywords);
        } else {
            alert('Failed to extract keywords: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error extracting keywords. Please try again.');
    });
}

function analyzeDocument(documentId) {
    fetch(`/documents/${documentId}/analyze`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAnalysisModal(data);
        } else {
            alert('Failed to analyze document: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error analyzing document. Please try again.');
    });
}

function showSummaryModal(data) {
    const modalHtml = `
        <div class="modal fade" id="summaryModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Document Summary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Summary</h6>
                            <p>${data.summary}</p>
                        </div>
                        ${data.key_points.length > 0 ? `
                            <div class="mb-3">
                                <h6>Key Points</h6>
                                <ul>
                                    ${data.key_points.map(point => `<li>${point}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.main_topics.length > 0 ? `
                            <div class="mb-3">
                                <h6>Main Topics</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    ${data.main_topics.map(topic => `<span class="badge bg-primary">${topic}</span>`).join('')}
                                </div>
                            </div>
                        ` : ''}
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Original: ${data.word_count} words</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Summary: ${data.summary_word_count} words</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('summaryModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('summaryModal'));
    modal.show();
}

function showKeywordsModal(keywords) {
    const modalHtml = `
        <div class="modal fade" id="keywordsModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Document Keywords</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2">
                            ${keywords.map(keyword => `<span class="badge bg-info">${keyword}</span>`).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('keywordsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('keywordsModal'));
    modal.show();
}

function showAnalysisModal(data) {
    const modalHtml = `
        <div class="modal fade" id="analysisModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Document Analysis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 text-primary">${data.readability_score}/10</div>
                                    <small class="text-muted">Readability Score</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 text-success">${data.quality_score}/10</div>
                                    <small class="text-muted">Quality Score</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Tone: <span class="badge bg-secondary">${data.tone}</span></h6>
                        </div>
                        <div class="mb-3">
                            <h6>Word Count: ${data.word_count} words</h6>
                            <h6>Reading Time: ${data.reading_time} minutes</h6>
                        </div>
                        ${data.strengths.length > 0 ? `
                            <div class="mb-3">
                                <h6>Strengths</h6>
                                <ul>
                                    ${data.strengths.map(strength => `<li>${strength}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${data.suggestions.length > 0 ? `
                            <div class="mb-3">
                                <h6>Suggestions for Improvement</h6>
                                <ul>
                                    ${data.suggestions.map(suggestion => `<li>${suggestion}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('analysisModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('analysisModal'));
    modal.show();
}
</script>
@endsection