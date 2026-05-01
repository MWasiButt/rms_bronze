@extends('partials.layouts.master')

@php($isEdit = $stockItem->exists)

@section('title', ($isEdit ? 'Edit Stock Item' : 'Add Stock Item') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Stock Item' : 'Add Stock Item')
@section('pagetitle', 'Inventory')
@section('buttonTitle', 'Back to Inventory')
@section('link', 'stock-items')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">{{ $isEdit ? 'Edit Stock Item' : 'Create Stock Item' }}</h5>
                    <p class="text-muted mb-0">Stock is tracked at the single Bronze outlet.</p>
                </div>
                <form method="POST" action="{{ $isEdit ? route('stock-items.update', $stockItem) : route('stock-items.store') }}">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Name</label>
                                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $stockItem->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sku">SKU</label>
                                <input id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $stockItem->sku) }}">
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="unit">Unit</label>
                                <input id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $stockItem->unit) }}" required>
                                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="current_stock">Current Stock</label>
                                <input id="current_stock" name="current_stock" type="number" step="0.001" class="form-control @error('current_stock') is-invalid @enderror" value="{{ old('current_stock', $stockItem->current_stock) }}" required>
                                @error('current_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="reorder_level">Reorder Level</label>
                                <input id="reorder_level" name="reorder_level" type="number" step="0.001" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $stockItem->reorder_level) }}" required>
                                @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $stockItem->is_active))>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('stock-items.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Item' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
