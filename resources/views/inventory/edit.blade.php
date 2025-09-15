@extends('layouts.app')

@section('title', 'Edit Inventory Item - SecretaryAI')
@section('page-title', 'Edit Inventory Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Inventory Item</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $item->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Enter item description...">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" name="sku" value="{{ old('sku', $item->sku) }}" 
                                       placeholder="Stock Keeping Unit">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="Electronics" {{ old('category', $item->category) == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                    <option value="Office Supplies" {{ old('category', $item->category) == 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                                    <option value="Furniture" {{ old('category', $item->category) == 'Furniture' ? 'selected' : '' }}>Furniture</option>
                                    <option value="Software" {{ old('category', $item->category) == 'Software' ? 'selected' : '' }}>Software</option>
                                    <option value="Other" {{ old('category', $item->category) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" value="{{ old('quantity', $item->quantity) }}" 
                                       min="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">Unit Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                           id="unit_price" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}" 
                                           step="0.01" min="0" required>
                                </div>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_quantity" class="form-label">Minimum Quantity</label>
                                <input type="number" class="form-control @error('min_quantity') is-invalid @enderror" 
                                       id="min_quantity" name="min_quantity" value="{{ old('min_quantity', $item->min_quantity) }}" 
                                       min="0">
                                <div class="form-text">Alert when quantity falls below this level</div>
                                @error('min_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control @error('supplier') is-invalid @enderror" 
                                       id="supplier" name="supplier" value="{{ old('supplier', $item->supplier) }}" 
                                       placeholder="Supplier name">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="supplier_contact" class="form-label">Supplier Contact</label>
                        <input type="text" class="form-control @error('supplier_contact') is-invalid @enderror" 
                               id="supplier_contact" name="supplier_contact" value="{{ old('supplier_contact', $item->supplier_contact) }}" 
                               placeholder="Email, phone, or contact person">
                        @error('supplier_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-2"></i>Update Item
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteItem({{ $item->id }})">
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
function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("inventory.destroy", ":id") }}'.replace(':id', itemId);
        form.submit();
    }
}

// Auto-calculate total value
document.getElementById('quantity').addEventListener('input', calculateTotal);
document.getElementById('unit_price').addEventListener('input', calculateTotal);

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * unitPrice;
    
    // You could display this somewhere if needed
    console.log('Total value:', total);
}
</script>
@endsection
