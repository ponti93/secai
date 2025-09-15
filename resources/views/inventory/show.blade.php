@extends('layouts.app')

@section('title', 'View Inventory Item - SecretaryAI')
@section('page-title', 'Inventory Item Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Item Details</h5>
                <div>
                    <a href="{{ route('inventory.edit', $item) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem({{ $item->id }})">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Item Name</label>
                            <p class="form-control-plaintext fs-5">{{ $item->name }}</p>
                        </div>
                    </div>
                </div>

                @if($item->description)
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <p class="form-control-plaintext">{{ $item->description }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">SKU</label>
                            <p class="form-control-plaintext">{{ $item->sku ?: 'Not specified' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">
                                @if($item->category)
                                    <span class="badge bg-info">{{ $item->category }}</span>
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Quantity</label>
                            <p class="form-control-plaintext fs-4">
                                <span class="{{ $item->quantity == 0 ? 'text-danger' : ($item->needs_reorder ? 'text-warning' : 'text-success') }}">
                                    {{ $item->quantity }}
                                </span>
                                @if($item->needs_reorder)
                                    <i class="bi bi-exclamation-triangle text-warning ms-1"></i>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unit Price</label>
                            <p class="form-control-plaintext fs-4">${{ number_format($item->unit_price, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Value</label>
                            <p class="form-control-plaintext fs-4">${{ number_format($item->quantity * $item->unit_price, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Minimum Quantity</label>
                            <p class="form-control-plaintext">{{ $item->min_quantity }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                @if($item->quantity == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->needs_reorder)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if($item->supplier)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Supplier</label>
                            <p class="form-control-plaintext">{{ $item->supplier }}</p>
                        </div>
                    </div>
                    @if($item->supplier_contact)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Supplier Contact</label>
                            <p class="form-control-plaintext">{{ $item->supplier_contact }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created</label>
                            <p class="form-control-plaintext">{{ $item->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p class="form-control-plaintext">{{ $item->updated_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                    </a>
                    <div>
                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Item
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
function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("inventory.destroy", ":id") }}'.replace(':id', itemId);
        form.submit();
    }
}
</script>
@endsection
