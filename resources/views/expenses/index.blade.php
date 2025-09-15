@extends('layouts.app')

@section('title', 'Expenses - SecretaryAI')
@section('page-title', 'Expense Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('expenses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ Add Expense
    </a>
    <button type="button" class="btn btn-success" onclick="showReceiptUpload()">
        <i class="bi bi-camera me-2"></i>Upload Receipt
    </button>
    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
    <button type="button" class="btn btn-info" onclick="loadAIInsights()">
        <i class="bi bi-robot me-2"></i>AI Insights
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
                    <input type="text" class="form-control" id="searchInput" placeholder="Search expenses...">
                </div>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="pending">Pending</button>
                    <button type="button" class="btn btn-outline-success" data-filter="approved">Approved</button>
                    <button type="button" class="btn btn-outline-danger" data-filter="rejected">Rejected</button>
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
                        <div class="h4 text-primary">{{ $expenses->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-warning">{{ $expenses->where('status', 'pending')->count() }}</div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success">${{ number_format($expenses->sum('amount'), 2) }}</div>
                        <small class="text-muted">Total Amount</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info">{{ $expenses->pluck('category')->unique()->count() }}</div>
                        <small class="text-muted">Categories</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Expenses List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Expenses</h6>
            </div>
            <div class="card-body p-0">
                @if($expenses->count() > 0)
                    @foreach($expenses as $expense)
                    <div class="expense-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $expense->description }}</h6>
                                    <span class="badge {{ $expense->status === 'approved' ? 'bg-success' : ($expense->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-tag me-1"></i>{{ ucfirst(str_replace('-', ' ', $expense->category)) }}
                                    <i class="bi bi-calendar me-1 ms-2"></i>{{ $expense->expense_date->format('d/m/Y') }}
                                    @if($expense->merchant)
                                    <i class="bi bi-building me-1 ms-2"></i>{{ $expense->merchant }}
                                    @endif
                                </p>
                                <p class="text-muted small mb-0">{{ $expense->notes ?: 'No notes' }}</p>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">${{ number_format($expense->amount, 2) }}</div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-success btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-info btn-sm" title="AI Categorize" onclick="categorizeExpense({{ $expense->id }}, '{{ addslashes($expense->description) }}', {{ $expense->amount }})">
                                        <i class="bi bi-robot"></i>
                                    </button>
                                    <a href="{{ route('expenses.fraud-check', $expense) }}" class="btn btn-outline-warning btn-sm" title="Fraud Check" target="_blank">
                                        <i class="bi bi-shield-exclamation"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-receipt text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No expenses found</p>
                        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Your First Expense
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Receipt Upload Modal -->
<div class="modal fade" id="receiptUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-camera fs-1 text-muted mb-3"></i>
                                <h6>Upload Receipt Photo</h6>
                                <input type="file" class="form-control" id="receiptFile" accept="image/*" onchange="previewReceipt(this)">
                                <small class="text-muted">Supported formats: JPG, PNG, PDF</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="receiptPreview" class="text-center" style="display: none;">
                            <img id="previewImage" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="aiProcessReceipt" checked>
                        <label class="form-check-label" for="aiProcessReceipt">
                            Use AI to extract data from receipt (Gemini API)
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processReceipt()">Process Receipt</button>
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
            filterExpenses();
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
            
            filterExpenses(filter);
        });
    });
});

function filterExpenses(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const expenseItems = document.querySelectorAll('.expense-item');
    
    expenseItems.forEach(item => {
        const description = item.querySelector('h6').textContent.toLowerCase();
        const category = item.querySelector('.text-muted').textContent.toLowerCase();
        const statusBadge = item.querySelector('.badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase().trim() : '';
        
        const matchesSearch = description.includes(searchTerm) || category.includes(searchTerm);
        
        let matchesFilter = true;
        if (filter !== 'all') {
            matchesFilter = status === filter;
        }
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

// Receipt Upload Functions (simplified)
function showReceiptUpload() {
    new bootstrap.Modal(document.getElementById('receiptUploadModal')).show();
}

function previewReceipt(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('receiptPreview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function processReceipt() {
    const fileInput = document.getElementById('receiptFile');
    const aiProcessCheckbox = document.getElementById('aiProcessReceipt');
    
    if (!fileInput.files[0]) {
        alert('Please select a receipt image first.');
        return;
    }
    
    if (aiProcessCheckbox.checked) {
        // Show loading state
        const processBtn = document.querySelector('[onclick="processReceipt()"]');
        const originalText = processBtn.innerHTML;
        processBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
        processBtn.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('receipt', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Process receipt with AI
        fetch('{{ route("expenses.process-receipt") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fill form with extracted data
                fillFormWithExtractedData(data.data);
                alert('Receipt processed successfully! Form has been filled with extracted data.');
            } else {
                alert('Failed to process receipt: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing receipt. Please try again.');
        })
        .finally(() => {
            // Reset button state
            processBtn.innerHTML = originalText;
            processBtn.disabled = false;
        });
    } else {
        alert('Please enable AI processing to extract data from receipt.');
    }
}

function fillFormWithExtractedData(data) {
    // Create a new expense with the extracted data
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("expenses.store") }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);
    
    // Add form fields
    const fields = {
        'description': data.description || 'Receipt purchase',
        'amount': data.amount || 0,
        'tax_amount': data.tax_amount || 0,
        'category': data.category || 'other',
        'expense_date': data.date || new Date().toISOString().split('T')[0],
        'merchant': data.merchant || '',
        'payment_method': data.payment_method || 'card',
        'receipt_number': data.receipt_number || '',
        'notes': `AI Extracted - Confidence: ${(data.confidence * 100).toFixed(1)}%`
    };
    
    Object.keys(fields).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    });
    
    // Submit form
    document.body.appendChild(form);
    form.submit();
}

// Add AI categorization button to expense rows
function categorizeExpense(expenseId, description, amount) {
    fetch('{{ route("expenses.categorize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            description: description,
            amount: amount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`AI suggests category: ${data.category} (Confidence: ${(data.confidence * 100).toFixed(1)}%)`);
        } else {
            alert('Failed to categorize expense. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error categorizing expense. Please try again.');
    });
}

// Load AI insights
function loadAIInsights() {
    fetch('{{ route("expenses.ai.insights") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAIInsights(data.insights);
        } else {
            alert('Failed to load AI insights. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading AI insights. Please try again.');
    });
}

function displayAIInsights(insights) {
    const insightsHtml = `
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">AI Insights</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="h5 text-primary">${insights.total_expenses}</div>
                        <small class="text-muted">Total Expenses</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h5 text-success">$${insights.total_amount.toFixed(2)}</div>
                        <small class="text-muted">Total Amount</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="h6">Top Category: ${insights.top_category}</div>
                    <small class="text-muted">Most frequent expense category</small>
                </div>
                <div class="mb-3">
                    <div class="h6">Average Amount: $${insights.average_amount.toFixed(2)}</div>
                    <small class="text-muted">Per expense</small>
                </div>
                ${insights.recommendations.length > 0 ? `
                    <div class="mt-3">
                        <h6>Recommendations:</h6>
                        <ul class="list-unstyled">
                            ${insights.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${rec}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    // Replace or add insights to the page
    const existingInsights = document.getElementById('ai-insights');
    if (existingInsights) {
        existingInsights.innerHTML = insightsHtml;
    } else {
        const sidebar = document.querySelector('.col-lg-3');
        if (sidebar) {
            sidebar.insertAdjacentHTML('beforeend', insightsHtml);
        }
    }
}
</script>
@endsection