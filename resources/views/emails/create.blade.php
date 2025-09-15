@extends('layouts.app')

@section('title', 'Compose Email - SecretaryAI')
@section('page-title', 'Compose Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Compose New Email</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('emails.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" required>
                            <button type="button" class="btn btn-outline-info" onclick="generateSubject()" title="AI Generate Subject">
                                <i class="bi bi-robot"></i>
                            </button>
                        </div>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="from_email" class="form-label">From *</label>
                                <input type="email" class="form-control @error('from_email') is-invalid @enderror" 
                                       id="from_email" name="from_email" value="{{ old('from_email', Auth::user()->email) }}" required>
                                @error('from_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="to_email" class="form-label">To *</label>
                                <input type="email" class="form-control @error('to_email') is-invalid @enderror" 
                                       id="to_email" name="to_email" value="{{ old('to_email') }}" required>
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
                                       id="cc_emails" name="cc_emails" value="{{ old('cc_emails') }}" 
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
                                       id="bcc_emails" name="bcc_emails" value="{{ old('bcc_emails') }}" 
                                       placeholder="email1@example.com, email2@example.com">
                                @error('bcc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Message *</label>
                        <div class="btn-group mb-2" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="generateEmailContent()" title="AI Generate Content">
                                <i class="bi bi-robot me-1"></i>Generate Content
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="improveEmailContent()" title="AI Improve Content">
                                <i class="bi bi-magic me-1"></i>Improve Content
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="checkGrammar()" title="AI Grammar Check">
                                <i class="bi bi-check-circle me-1"></i>Grammar Check
                            </button>
                        </div>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_important" name="is_important" 
                                   {{ old('is_important') ? 'checked' : '' }}>
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
                                <i class="bi bi-send me-2"></i>Send Email
                            </button>
                            <button type="button" class="btn btn-outline-info me-2" onclick="sendTestEmail()">
                                <i class="bi bi-envelope-check me-2"></i>Send Test Email
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                                <i class="bi bi-save me-2"></i>Save Draft
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function saveDraft() {
    // For now, just submit the form as a draft
    // In a real implementation, you'd have a separate draft endpoint
    const form = document.querySelector('form');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'status';
    input.value = 'draft';
    form.appendChild(input);
    form.submit();
}

function sendTestEmail() {
    const toEmail = document.getElementById('to_email').value;
    const fromEmail = document.getElementById('from_email').value;
    const subject = document.getElementById('subject').value;
    const content = document.getElementById('content').value;
    
    if (!toEmail || !fromEmail || !subject || !content) {
        alert('Please fill in all required fields before sending a test email.');
        return;
    }
    
    if (!confirm('This will send a test email to ' + toEmail + '. Continue?')) {
        return;
    }
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
    button.disabled = true;
    
    // Send test email to the same recipient
    fetch('/emails/test-send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            to_email: toEmail,
            from_email: fromEmail,
            subject: '[TEST] ' + subject,
            content: '[TEST EMAIL]\n\n' + content + '\n\n---\nThis is a test email sent from SecretaryAI.'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test email sent successfully!');
        } else {
            alert('Failed to send test email: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send test email. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

// AI Functions for Email Composition
function generateSubject() {
    const toEmail = document.getElementById('to_email').value;
    const content = document.getElementById('content').value;
    
    if (!toEmail && !content) {
        const description = prompt('Please describe what this email is about to generate a relevant subject:\n\nExamples:\n- "Meeting request for next week"\n- "Follow up on invoice #12345"\n- "Thank you for the interview"');
        if (!description) return;
        
        // Use description instead of content
        generateSubjectFromDescription(description, toEmail);
        return;
    }
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch('/emails/ai/generate-subject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            to_email: toEmail,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('subject').value = data.subject;
        } else {
            alert('Failed to generate subject. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate subject. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function generateSubjectFromDescription(description, toEmail) {
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch('/emails/ai/generate-subject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            to_email: toEmail,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('subject').value = data.subject;
        } else {
            alert('Failed to generate subject. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate subject. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function generateEmailContent() {
    const subject = document.getElementById('subject').value;
    const toEmail = document.getElementById('to_email').value;
    const description = prompt('Describe what you want to write in this email:\n\nExamples:\n- "Ask for a meeting next week to discuss the project proposal"\n- "Thank them for the interview and ask about next steps"\n- "Complain about the delayed delivery and request a refund"\n- "Introduce myself and explain why I\'m reaching out"\n- "Follow up on the invoice that was sent last month"');
    
    if (!description) return;
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
    button.disabled = true;
    
    fetch('/emails/ai/generate-content', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            subject: subject,
            to_email: toEmail,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('content').value = data.content;
        } else {
            alert('Failed to generate content. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate content. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function improveEmailContent() {
    const content = document.getElementById('content').value;
    
    if (!content.trim()) {
        alert('Please enter some content first to improve.');
        return;
    }
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Improving...';
    button.disabled = true;
    
    fetch('/emails/ai/improve-content', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('content').value = data.improved_content;
        } else {
            alert('Failed to improve content. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to improve content. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function checkGrammar() {
    const content = document.getElementById('content').value;
    
    if (!content.trim()) {
        alert('Please enter some content first to check grammar.');
        return;
    }
    
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Checking...';
    button.disabled = true;
    
    fetch('/emails/ai/grammar-check', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.corrected_content) {
                document.getElementById('content').value = data.corrected_content;
                alert('Grammar check completed! Corrections have been applied.');
            } else {
                alert('Grammar check completed! No corrections needed.');
            }
        } else {
            alert('Failed to check grammar. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check grammar. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}
</script>
@endsection
