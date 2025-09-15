@extends('layouts.app')

@section('title', 'Inventory - SecretaryAI')
@section('page-title', 'Inventory Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>+ Add Item
    </a>
    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
    </button>
    <button type="button" class="btn btn-info" onclick="showForecastModal()">
        <i class="bi bi-graph-up me-2"></i>AI Forecast
    </button>
    <button type="button" class="btn btn-warning" onclick="getReorderSuggestions()">
        <i class="bi bi-arrow-repeat me-2"></i>Reorder Suggestions
    </button>
    <button type="button" class="btn btn-success" onclick="showPricingModal()">
        <i class="bi bi-currency-dollar me-2"></i>Optimize Pricing
    </button>
    <button type="button" class="btn btn-primary" onclick="getInsights()">
        <i class="bi bi-lightbulb me-2"></i>AI Insights
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
                    <input type="text" class="form-control" id="searchInput" placeholder="Search inventory...">
                </div>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="low-stock">Low Stock</button>
                    <button type="button" class="btn btn-outline-danger" data-filter="out-of-stock">Out of Stock</button>
                    <button type="button" class="btn btn-outline-info" data-filter="electronics">Electronics</button>
                    <button type="button" class="btn btn-outline-success" data-filter="office">Office Supplies</button>
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
                        <div class="h4 text-primary">{{ $inventory->count() }}</div>
                        <small class="text-muted">Total Items</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-warning">{{ $inventory->where('needs_reorder', true)->count() }}</div>
                        <small class="text-muted">Low Stock</small>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success">${{ number_format($inventory->sum('unit_price'), 2) }}</div>
                        <small class="text-muted">Total Value</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info">{{ $inventory->sum('quantity') }}</div>
                        <small class="text-muted">Total Qty</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Inventory List -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Inventory Items</h6>
            </div>
            <div class="card-body p-0">
                @if($inventory->count() > 0)
                    @foreach($inventory as $item)
                    <div class="inventory-item border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2">{{ $item->name }}</h6>
                                    @if($item->needs_reorder)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @endif
                                    @if($item->quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                    @if($item->category)
                                        <span class="badge bg-info">{{ $item->category }}</span>
                                    @endif
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-box me-1"></i>Qty: {{ $item->quantity }}
                                    <i class="bi bi-currency-dollar me-1 ms-2"></i>Price: ${{ number_format($item->unit_price, 2) }}
                                    @if($item->sku)
                                        <i class="bi bi-tag me-1 ms-2"></i>SKU: {{ $item->sku }}
                                    @endif
                                </p>
                                @if($item->supplier)
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-truck me-1"></i>Supplier: {{ $item->supplier }}
                                </p>
                                @endif
                                @if($item->description)
                                <p class="text-muted small mb-0">{{ Str::limit($item->description, 100) }}</p>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Value: ${{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('inventory.show', $item) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $item) }}" class="btn btn-outline-success btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-info btn-sm" title="AI Forecast" onclick="forecastItemDemand('{{ addslashes($item->name) }}', {{ $item->quantity }}, {{ $item->unit_price ?? 0 }})">
                                        <i class="bi bi-graph-up"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" title="Reorder Analysis" onclick="analyzeReorder('{{ addslashes($item->name) }}', {{ $item->quantity }}, {{ $item->min_quantity ?? 0 }})">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-box text-muted fs-1"></i>
                        <p class="mt-2 text-muted">No inventory items found</p>
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Your First Item
                        </a>
                    </div>
                @endif
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
            filterInventory();
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
            
            filterInventory(filter);
        });
    });
});

function filterInventory(filter = 'all') {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const inventoryItems = document.querySelectorAll('.inventory-item');
    
    inventoryItems.forEach(item => {
        const name = item.querySelector('h6').textContent.toLowerCase();
        const description = item.querySelector('.text-muted').textContent.toLowerCase();
        const categoryBadge = item.querySelector('.badge.bg-info');
        const category = categoryBadge ? categoryBadge.textContent.toLowerCase().trim() : '';
        
        const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
        
        let matchesFilter = true;
        if (filter !== 'all') {
            if (filter === 'low-stock') {
                const lowStockBadge = item.querySelector('.badge.bg-warning');
                matchesFilter = lowStockBadge !== null;
            } else if (filter === 'out-of-stock') {
                const outOfStockBadge = item.querySelector('.badge.bg-danger');
                matchesFilter = outOfStockBadge !== null;
            } else {
                matchesFilter = category === filter;
            }
        }
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

// AI Inventory Features
function showForecastModal() {
    const modalHtml = `
        <div class="modal fade" id="forecastModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Demand Forecasting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="forecastForm">
                            <div class="mb-3">
                                <label for="forecastPeriod" class="form-label">Forecast Period</label>
                                <select class="form-select" id="forecastPeriod" name="period">
                                    <option value="7 days">7 Days</option>
                                    <option value="30 days" selected>30 Days</option>
                                    <option value="90 days">90 Days</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="confidenceLevel" class="form-label">Confidence Level</label>
                                <select class="form-select" id="confidenceLevel" name="confidence">
                                    <option value="low">Low (Fast, Less Accurate)</option>
                                    <option value="medium" selected>Medium (Balanced)</option>
                                    <option value="high">High (Slow, More Accurate)</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="runForecast()">
                            <i class="bi bi-graph-up me-2"></i>Generate Forecast
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('forecastModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('forecastModal'));
    modal.show();
}

function showPricingModal() {
    const modalHtml = `
        <div class="modal fade" id="pricingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Pricing Optimization</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="pricingForm">
                            <div class="mb-3">
                                <label for="marketConditions" class="form-label">Market Conditions</label>
                                <select class="form-select" id="marketConditions" name="market_conditions">
                                    <option value="stable" selected>Stable</option>
                                    <option value="growing">Growing</option>
                                    <option value="declining">Declining</option>
                                    <option value="volatile">Volatile</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="profitMargin" class="form-label">Target Profit Margin (%)</label>
                                <input type="number" class="form-control" id="profitMargin" name="profit_margin" min="0" max="100" value="20">
                            </div>
                            <div class="mb-3">
                                <label for="competitionLevel" class="form-label">Competition Level</label>
                                <select class="form-select" id="competitionLevel" name="competition">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="runPricingOptimization()">
                            <i class="bi bi-currency-dollar me-2"></i>Optimize Pricing
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('pricingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('pricingModal'));
    modal.show();
}

function runForecast() {
    const form = document.getElementById('forecastForm');
    const formData = new FormData(form);
    
    // Show loading state
    const forecastBtn = document.querySelector('[onclick="runForecast()"]');
    const originalText = forecastBtn.innerHTML;
    forecastBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    forecastBtn.disabled = true;
    
    fetch('{{ route("inventory.ai.forecast") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showForecastResults(data);
        } else {
            alert('Failed to generate forecast: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating forecast. Please try again.');
    })
    .finally(() => {
        // Reset button state
        forecastBtn.innerHTML = originalText;
        forecastBtn.disabled = false;
    });
}

function runPricingOptimization() {
    const form = document.getElementById('pricingForm');
    const formData = new FormData(form);
    
    // Show loading state
    const pricingBtn = document.querySelector('[onclick="runPricingOptimization()"]');
    const originalText = pricingBtn.innerHTML;
    pricingBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Optimizing...';
    pricingBtn.disabled = true;
    
    fetch('{{ route("inventory.ai.optimize-pricing") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPricingResults(data);
        } else {
            alert('Failed to optimize pricing: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error optimizing pricing. Please try again.');
    })
    .finally(() => {
        // Reset button state
        pricingBtn.innerHTML = originalText;
        pricingBtn.disabled = false;
    });
}

function getReorderSuggestions() {
    // Show loading state
    const reorderBtn = document.querySelector('[onclick="getReorderSuggestions()"]');
    const originalText = reorderBtn.innerHTML;
    reorderBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    reorderBtn.disabled = true;
    
    fetch('{{ route("inventory.ai.reorder-suggestions") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showReorderResults(data);
        } else {
            alert('Failed to get reorder suggestions: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting reorder suggestions. Please try again.');
    })
    .finally(() => {
        // Reset button state
        reorderBtn.innerHTML = originalText;
        reorderBtn.disabled = false;
    });
}

function getInsights() {
    // Show loading state
    const insightsBtn = document.querySelector('[onclick="getInsights()"]');
    const originalText = insightsBtn.innerHTML;
    insightsBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Analyzing...';
    insightsBtn.disabled = true;
    
    fetch('{{ route("inventory.ai.insights") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showInsightsResults(data);
        } else {
            alert('Failed to get insights: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error getting insights. Please try again.');
    })
    .finally(() => {
        // Reset button state
        insightsBtn.innerHTML = originalText;
        insightsBtn.disabled = false;
    });
}

function forecastItemDemand(itemName, quantity, unitPrice) {
    alert(`AI Forecast for ${itemName}:\nCurrent Stock: ${quantity}\nUnit Price: $${unitPrice}\n\nThis feature analyzes demand patterns and provides forecasting recommendations.`);
}

function analyzeReorder(itemName, quantity, minQuantity) {
    const status = quantity <= minQuantity ? 'URGENT - Below minimum threshold' : 
                   quantity <= minQuantity * 1.5 ? 'WARNING - Getting low' : 'OK - Stock levels adequate';
    
    alert(`Reorder Analysis for ${itemName}:\nCurrent Stock: ${quantity}\nMinimum Required: ${minQuantity}\nStatus: ${status}\n\nAI suggests monitoring this item closely.`);
}

function showForecastResults(data) {
    const modalHtml = `
        <div class="modal fade" id="forecastResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Demand Forecast Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Overall Analysis:</h6>
                            <p>${data.overall_analysis}</p>
                        </div>
                        <div class="mb-3">
                            <h6>Forecasts:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Predicted Demand</th>
                                            <th>Confidence</th>
                                            <th>Risk Level</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.forecasts.map(forecast => `
                                            <tr>
                                                <td>${forecast.item_name}</td>
                                                <td>${forecast.predicted_demand}</td>
                                                <td>
                                                    <span class="badge bg-${forecast.confidence_score > 80 ? 'success' : forecast.confidence_score > 60 ? 'warning' : 'danger'}">
                                                        ${forecast.confidence_score}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${forecast.risk_level === 'high' ? 'danger' : forecast.risk_level === 'medium' ? 'warning' : 'success'}">
                                                        ${forecast.risk_level}
                                                    </span>
                                                </td>
                                                <td>${forecast.recommended_action}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        ${data.recommendations.length > 0 ? `
                            <div class="mb-3">
                                <h6>Recommendations:</h6>
                                <ul class="list-unstyled">
                                    ${data.recommendations.map(rec => `<li class="mb-1"><i class="bi bi-lightbulb me-2"></i>${rec}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('forecastResultsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('forecastResultsModal'));
    modal.show();
}

function showReorderResults(data) {
    const modalHtml = `
        <div class="modal fade" id="reorderResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reorder Suggestions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Summary:</h6>
                            <p>${data.summary}</p>
                        </div>
                        <div class="mb-3">
                            <h6>Reorder Suggestions:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Current Stock</th>
                                            <th>Suggested Qty</th>
                                            <th>Urgency</th>
                                            <th>Priority</th>
                                            <th>Timing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.suggestions.map(suggestion => `
                                            <tr>
                                                <td>${suggestion.item_name}</td>
                                                <td>${suggestion.current_stock}</td>
                                                <td>${suggestion.suggested_quantity}</td>
                                                <td>
                                                    <span class="badge bg-${suggestion.urgency === 'high' ? 'danger' : suggestion.urgency === 'medium' ? 'warning' : 'success'}">
                                                        ${suggestion.urgency}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">${suggestion.priority_score}/10</span>
                                                </td>
                                                <td>${suggestion.timing}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('reorderResultsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('reorderResultsModal'));
    modal.show();
}

function showPricingResults(data) {
    const modalHtml = `
        <div class="modal fade" id="pricingResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pricing Optimization Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Market Analysis:</h6>
                            <p>${data.market_analysis}</p>
                        </div>
                        <div class="mb-3">
                            <h6>Pricing Optimizations:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Current Price</th>
                                            <th>Suggested Price</th>
                                            <th>Change</th>
                                            <th>Confidence</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.optimizations.map(opt => `
                                            <tr>
                                                <td>${opt.item_name}</td>
                                                <td>$${opt.current_price}</td>
                                                <td>$${opt.suggested_price}</td>
                                                <td>
                                                    <span class="badge bg-${opt.price_change > 0 ? 'success' : 'danger'}">
                                                        ${opt.price_change > 0 ? '+' : ''}${opt.price_change}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${opt.confidence > 80 ? 'success' : opt.confidence > 60 ? 'warning' : 'danger'}">
                                                        ${opt.confidence}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${opt.implementation_priority === 'high' ? 'danger' : opt.implementation_priority === 'medium' ? 'warning' : 'success'}">
                                                        ${opt.implementation_priority}
                                                    </span>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('pricingResultsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('pricingResultsModal'));
    modal.show();
}

function showInsightsResults(data) {
    const modalHtml = `
        <div class="modal fade" id="insightsResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Inventory Insights</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Key Metrics</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre>${JSON.stringify(data.insights.key_metrics || {}, null, 2)}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Trends</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre>${JSON.stringify(data.insights.trends || {}, null, 2)}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Recommendations</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre>${JSON.stringify(data.insights.recommendations || {}, null, 2)}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Risk Alerts</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre>${JSON.stringify(data.insights.risk_alerts || {}, null, 2)}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('insightsResultsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('insightsResultsModal'));
    modal.show();
}
</script>
@endsection