@extends('layouts.app')

@section('title', 'Edit Document - SecretaryAI')
@section('page-title', 'Edit Document')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Document</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $document->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Document Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type">
                            <option value="general" {{ old('type', $document->type) == 'general' ? 'selected' : '' }}>General</option>
                            <option value="letter" {{ old('type', $document->type) == 'letter' ? 'selected' : '' }}>Letter</option>
                            <option value="memo" {{ old('type', $document->type) == 'memo' ? 'selected' : '' }}>Memo</option>
                            <option value="report" {{ old('type', $document->type) == 'report' ? 'selected' : '' }}>Report</option>
                            <option value="proposal" {{ old('type', $document->type) == 'proposal' ? 'selected' : '' }}>Proposal</option>
                            <option value="contract" {{ old('type', $document->type) == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="agenda" {{ old('type', $document->type) == 'agenda' ? 'selected' : '' }}>Agenda</option>
                            <option value="minutes" {{ old('type', $document->type) == 'minutes' ? 'selected' : '' }}>Meeting Minutes</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($document->file_name)
                    <div class="mb-3">
                        <label class="form-label">Current File</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-paperclip me-2"></i>
                            <span>{{ $document->file_name }}</span>
                            @if($document->file_size)
                                <small class="text-muted ms-2">({{ number_format($document->file_size / 1024, 1) }} KB)</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="file" class="form-label">Upload New File (Optional)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" 
                               id="file" name="file" accept=".pdf,.doc,.docx,.txt,.rtf">
                        <div class="form-text">Supported formats: PDF, DOC, DOCX, TXT, RTF (Max 10MB)</div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="15" required 
                                  placeholder="Enter your document content here...">{{ old('content', $document->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Documents
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-2"></i>Update Document
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteDocument({{ $document->id }})">
                                <i class="bi bi-trash me-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("documents.destroy", ":id") }}'.replace(':id', documentId);
        form.submit();
    }
}
</script>
@endsection
