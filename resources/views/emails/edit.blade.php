@extends('layouts.app')

@section('title', 'Edit Email - SecretaryAI')
@section('page-title', 'Edit Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Email</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('emails.update', $email) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject', $email->subject) }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="from_email" class="form-label">From *</label>
                                <input type="email" class="form-control @error('from_email') is-invalid @enderror" 
                                       id="from_email" name="from_email" value="{{ old('from_email', $email->from_email) }}" required>
                                @error('from_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="to_email" class="form-label">To *</label>
                                <input type="email" class="form-control @error('to_email') is-invalid @enderror" 
                                       id="to_email" name="to_email" value="{{ old('to_email', $email->to_email) }}" required>
                                @error('to_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cc_emails" class="form-label">CC</label>
                                <input type="text" class="form-control @error('cc_emails') is-invalid @enderror" 
                                       id="cc_emails" name="cc_emails" value="{{ old('cc_emails', $email->cc_emails) }}" 
                                       placeholder="email1@example.com, email2@example.com">
                                @error('cc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bcc_emails" class="form-label">BCC</label>
                                <input type="text" class="form-control @error('bcc_emails') is-invalid @enderror" 
                                       id="bcc_emails" name="bcc_emails" value="{{ old('bcc_emails', $email->bcc_emails) }}" 
                                       placeholder="email1@example.com, email2@example.com">
                                @error('bcc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Message *</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="10" required>{{ old('content', $email->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_important" name="is_important" 
                                   {{ old('is_important', $email->is_important) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_important">
                                Mark as Important
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('emails.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Emails
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-2"></i>Update Email
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteEmail({{ $email->id }})">
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
function deleteEmail(emailId) {
    if (confirm('Are you sure you want to delete this email? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("emails.destroy", ":id") }}'.replace(':id', emailId);
        form.submit();
    }
}
</script>
@endsection
