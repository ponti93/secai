@extends('layouts.app')

@section('title', 'View Document - SecretaryAI')
@section('page-title', 'Document Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Document Details</h5>
                <div>
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteDocument({{ $document->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <p class="form-control-plaintext fs-5">{{ $document->title }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">{{ ucfirst($document->type) }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge {{ $document->status === 'published' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                @if($document->file_name)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Attached File</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-paperclip me-2"></i>
                                    <span>{{ $document->file_name }}</span>
                                    @if($document->file_size)
                                        <small class="text-muted ms-2">({{ number_format($document->file_size / 1024, 1) }} KB)</small>
                                    @endif
                                </div>
                                <a href="{{ route('documents.download', $document) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-download me-1"></i>Download Original
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Download Options -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Download Document</label>
                            <div class="btn-group" role="group">
                                <a href="{{ route('documents.download', $document) }}?format=txt" class="btn btn-outline-primary">
                                    <i class="bi bi-file-text me-2"></i>Download as TXT
                                </a>
                                <a href="{{ route('documents.download', $document) }}?format=doc" class="btn btn-outline-success">
                                    <i class="bi bi-file-word me-2"></i>Download as DOC
                                </a>
                                <a href="{{ route('documents.download', $document) }}?format=pdf" class="btn btn-outline-danger">
                                    <i class="bi bi-file-pdf me-2"></i>Download as PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content</label>
                    <div class="border p-3 rounded" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                        {!! nl2br(e($document->content)) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created</label>
                            <p class="form-control-plaintext">{{ $document->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p class="form-control-plaintext">{{ $document->updated_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Documents
                    </a>
                    <div>
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Document
                        </a>
                    </div>
                </div>
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
