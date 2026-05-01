@extends('partials.layouts.master')

@section('title', 'Inventory | RMS Bronze')
@section('sub-title', 'Stock Items')
@section('pagetitle', 'Inventory')
@section('buttonTitle', 'Add Stock Item')
@section('link', 'stock-items/create')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Items</p>
                    <h3 class="mb-0">{{ $stockItems->whereNull('deleted_at')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Low Stock</p>
                    <h3 class="mb-0">{{ $lowStockItems->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Movements</p>
                    <h3 class="mb-0">{{ $movements->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inventory Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('stock-items.create') }}" class="btn btn-primary">Add Stock Item</a>
                    <a href="{{ route('recipes.index') }}" class="btn btn-light-primary">Recipes / BOM</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">Stock Items</h5>
                        <p class="text-muted mb-0">Soft-deleted items can be restored.</p>
                    </div>
                    <a href="{{ route('stock-items.create') }}" class="btn btn-sm btn-primary">Add Item</a>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($stockItems as $item)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <p class="text-muted fs-13 mb-2">{{ $item->sku ?: 'No SKU' }} - {{ $item->unit }}</p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge {{ (float) $item->current_stock <= (float) $item->reorder_level ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                Stock {{ number_format((float) $item->current_stock, 3) }}
                                            </span>
                                            <span class="badge bg-light text-dark border">Reorder {{ number_format((float) $item->reorder_level, 3) }}</span>
                                            @if ($item->trashed())
                                                <span class="badge bg-secondary-subtle text-secondary">Archived</span>
                                            @elseif (! $item->is_active)
                                                <span class="badge bg-warning-subtle text-warning">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2" style="min-width: 280px;">
                                        @if ($item->trashed())
                                            <form method="POST" action="{{ route('stock-items.restore', $item->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-light-success w-100">Restore</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('stock-items.movements.store', $item) }}" class="row g-2">
                                                @csrf
                                                <div class="col-4">
                                                    <select name="type" class="form-select form-select-sm">
                                                        <option value="IN">IN</option>
                                                        <option value="OUT">OUT</option>
                                                        <option value="ADJUST">ADJUST</option>
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <input type="number" step="0.001" name="quantity" class="form-control form-control-sm" placeholder="Qty" required>
                                                </div>
                                                <div class="col-4">
                                                    <button type="submit" class="btn btn-sm btn-light-primary w-100">Post</button>
                                                </div>
                                                <div class="col-12">
                                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason / notes">
                                                </div>
                                            </form>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('stock-items.edit', $item) }}" class="btn btn-sm btn-light flex-fill">Edit</a>
                                                <form method="POST" action="{{ route('stock-items.destroy', $item) }}" class="flex-fill">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger w-100">Archive</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No stock items yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">Movement Ledger</h5>
                    <p class="text-muted mb-0">Latest 50 stock movements</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($movements as $movement)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h6 class="mb-1">{{ $movement->stockItem?->name }}</h6>
                                        <p class="text-muted fs-13 mb-0">{{ $movement->notes ?: $movement->occurred_at?->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">{{ $movement->type->value }}</span>
                                        <div class="fw-semibold mt-1">{{ number_format((float) $movement->quantity, 3) }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No stock movements yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
