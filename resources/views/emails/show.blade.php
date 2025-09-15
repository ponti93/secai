@extends('layouts.app')

@section('title', 'View Email - SecretaryAI')
@section('page-title', 'Email Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Email Details</h5>
                <div>
                    <a href="{{ route('emails.edit', $email) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEmail({{ $email->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>
                            <p class="form-control-plaintext fs-5">{{ $email->subject }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">From</label>
                            <p class="form-control-plaintext">{{ $email->from_email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">To</label>
                            <p class="form-control-plaintext">{{ $email->to_email }}</p>
                        </div>
                    </div>
                </div>

                @if($email->cc_emails)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">CC</label>
                            <p class="form-control-plaintext">{{ $email->cc_emails }}</p>
                        </div>
                    </div>
                    @if($email->bcc_emails)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">BCC</label>
                            <p class="form-control-plaintext">{{ $email->bcc_emails }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold">Message</label>
                    <div class="border p-3 rounded" style="min-height: 200px;">
                        {!! nl2br(e($email->content)) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge {{ $email->status === 'sent' ? 'bg-success' : ($email->status === 'draft' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($email->status) }}
                                </span>
                                @if($email->is_important)
                                    <i class="bi bi-star-fill text-warning ms-2"></i>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created</label>
                            <p class="form-control-plaintext">{{ $email->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('emails.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Emails
                    </a>
                    <div>
                        <a href="{{ route('emails.edit', $email) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Email
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
function deleteEmail(emailId) {
    if (confirm('Are you sure you want to delete this email? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("emails.destroy", ":id") }}'.replace(':id', emailId);
        form.submit();
    }
}
</script>
@endsection
