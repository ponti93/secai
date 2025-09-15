@extends('layouts.app')

@section('title', 'View Expense - SecretaryAI')
@section('page-title', 'Expense Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Expense Details</h5>
                <div>
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteExpense({{ $expense->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <p class="form-control-plaintext">{{ $expense->description }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount</label>
                            <p class="form-control-plaintext fs-5 text-primary">${{ number_format($expense->amount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('-', ' ', $expense->category)) }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <p class="form-control-plaintext">{{ $expense->expense_date->format('F j, Y') }}</p>
                        </div>
                    </div>
                </div>

                @if($expense->merchant)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Merchant</label>
                            <p class="form-control-plaintext">{{ $expense->merchant }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <p class="form-control-plaintext">{{ ucfirst($expense->payment_method ?: 'Not specified') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($expense->tax_amount > 0 || $expense->receipt_number)
                <div class="row">
                    @if($expense->tax_amount > 0)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tax Amount</label>
                            <p class="form-control-plaintext">${{ number_format($expense->tax_amount, 2) }}</p>
                        </div>
                    </div>
                    @endif
                    @if($expense->receipt_number)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Receipt Number</label>
                            <p class="form-control-plaintext">{{ $expense->receipt_number }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                @if($expense->notes)
                <div class="mb-3">
                    <label class="form-label fw-bold">Notes</label>
                    <p class="form-control-plaintext">{{ $expense->notes }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge {{ $expense->status === 'approved' ? 'bg-success' : ($expense->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created</label>
                            <p class="form-control-plaintext">{{ $expense->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Expenses
                    </a>
                    <div>
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Expense
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
function deleteExpense(expenseId) {
    if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("expenses.destroy", ":id") }}'.replace(':id', expenseId);
        form.submit();
    }
}
</script>
@endsection
