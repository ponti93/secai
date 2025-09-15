@extends('layouts.app')

@section('title', 'Compose Email - SecretaryAI')
@section('page-title', 'Compose Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">New Email</h6>
            </div>
            <div class="card-body">
                <form id="composeForm">
                    <div class="mb-3">
                        <label for="to" class="form-label">To</label>
                        <input type="email" class="form-control" id="to" placeholder="recipient@example.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cc" class="form-label">CC</label>
                        <input type="email" class="form-control" id="cc" placeholder="cc@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="bcc" class="form-label">BCC</label>
                        <input type="email" class="form-control" id="bcc" placeholder="bcc@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" placeholder="Email subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Message</label>
                        <textarea class="form-control" id="content" rows="10" placeholder="Type your message here..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="aiAssist">
                            <label class="form-check-label" for="aiAssist">
                                Use AI assistance for writing
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="saveDraft()">
                                <i class="bi bi-file-earmark me-2"></i>Save Draft
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="generateWithAI()">
                                <i class="bi bi-magic me-2"></i>Generate with AI
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Send
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save draft every 30 seconds
    setInterval(saveDraft, 30000);
});

function saveDraft() {
    const formData = {
        to: document.getElementById('to').value,
        cc: document.getElementById('cc').value,
        bcc: document.getElementById('bcc').value,
        subject: document.getElementById('subject').value,
        content: document.getElementById('content').value
    };
    
    // Save to localStorage as backup
    localStorage.setItem('emailDraft', JSON.stringify(formData));
    
    // Show success message
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                Draft saved successfully
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    new bootstrap.Toast(toast).show();
    
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 3000);
}

function generateWithAI() {
    const subject = document.getElementById('subject').value;
    const content = document.getElementById('content').value;
    
    if (!subject && !content) {
        alert('Please enter a subject or some content to generate with AI');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-magic me-2"></i>Generating...';
    button.disabled = true;
    
    fetch('/api/ai/generate-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            subject: subject,
            content: content,
            context: 'email composition'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.data.subject) {
                document.getElementById('subject').value = data.data.subject;
            }
            if (data.data.content) {
                document.getElementById('content').value = data.data.content;
            }
        } else {
            alert('Error generating email: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Load draft from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const draft = localStorage.getItem('emailDraft');
    if (draft) {
        const formData = JSON.parse(draft);
        document.getElementById('to').value = formData.to || '';
        document.getElementById('cc').value = formData.cc || '';
        document.getElementById('bcc').value = formData.bcc || '';
        document.getElementById('subject').value = formData.subject || '';
        document.getElementById('content').value = formData.content || '';
    }
});

// Form submission
document.getElementById('composeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        to: document.getElementById('to').value,
        cc: document.getElementById('cc').value,
        bcc: document.getElementById('bcc').value,
        subject: document.getElementById('subject').value,
        content: document.getElementById('content').value
    };
    
    // Send email
    fetch('/api/emails', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear form
            document.getElementById('composeForm').reset();
            localStorage.removeItem('emailDraft');
            
            // Show success message
            alert('Email sent successfully!');
            window.location.href = '/emails';
        } else {
            alert('Error sending email: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>
@endsection
