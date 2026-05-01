@extends('partials.layouts.master')

@php
    $isEdit = $item->exists;
    $selectedGroups = old('modifier_groups', $item->modifierGroups?->pluck('id')->all() ?? []);
@endphp

@section('title', ($isEdit ? 'Edit Menu Item' : 'Add Menu Item') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Menu Item' : 'Add Menu Item')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Back to Items')
@section('link', 'menu-items')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('menu-items.update', $item) : route('menu-items.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-1">Item Details</h5>
                        <p class="text-muted mb-0">Store money as cents, with per-item tax and tax mode.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="item-name">Item Name</label>
                                <input id="item-name" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="item-sku">SKU</label>
                                <input id="item-sku" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="item-category">Category</label>
                                <select id="item-category" name="product_category_id" class="form-select" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('product_category_id', $item->product_category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="item-active" @checked(old('is_active', $item->is_active ?? true))>
                                    <label class="form-check-label" for="item-active">Active item</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="item-price">Sale Price</label>
                                <input id="item-price" type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $item->exists ? number_format($item->price_cents / 100, 2, '.', '') : '0.00') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="item-cost">Cost Price</label>
                                <input id="item-cost" type="number" min="0" step="0.01" name="cost" class="form-control" value="{{ old('cost', $item->exists ? number_format($item->cost_cents / 100, 2, '.', '') : '0.00') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="item-tax-rate">Tax Rate %</label>
                                <input id="item-tax-rate" type="number" min="0" max="100" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', number_format(($item->tax_rate_bps ?? 0) / 100, 2, '.', '')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="item-tax-mode">Tax Mode</label>
                                <select id="item-tax-mode" name="tax_mode" class="form-select" required>
                                    @foreach ($taxModes as $mode)
                                        <option value="{{ $mode->value }}" @selected(old('tax_mode', $item->tax_mode?->value ?? 'EXCLUSIVE') === $mode->value)>{{ $mode->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="item-description">Description</label>
                                <textarea id="item-description" name="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header">
                        <h5 class="mb-1">Modifier Assignment</h5>
                        <p class="text-muted mb-0">Attach shared modifier groups to this item.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse ($modifierGroups as $group)
                                <div class="col-md-6">
                                    <label class="form-check border rounded-3 p-3 d-flex align-items-start gap-2 h-100">
                                        <input class="form-check-input mt-1" type="checkbox" name="modifier_groups[]" value="{{ $group->id }}" @checked(in_array($group->id, array_map('intval', $selectedGroups), true))>
                                        <span>
                                            <span class="fw-medium d-block">{{ $group->name }}</span>
                                            <span class="text-muted fs-13">{{ $group->options->pluck('name')->join(', ') ?: 'No options' }}</span>
                                        </span>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Create modifier groups before assigning options to items.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-1">Publishing Panel</h5>
                        <p class="text-muted mb-0">Owner-only catalog save.</p>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Item' : 'Create Item' }}</button>
                        <a href="{{ route('menu-items.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header">
                        <h5 class="mb-1">Cache</h5>
                        <p class="text-muted mb-0">Menu cache is refreshed after save.</p>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-success-subtle text-success">Enabled</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
