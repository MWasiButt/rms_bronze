@extends('partials.layouts.master')

@php($isEdit = $option->exists)

@section('title', ($isEdit ? 'Edit Modifier Option' : 'Add Modifier Option') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Modifier Option' : 'Add Modifier Option')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Back to Categories')
@section('link', 'menu-categories')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('modifier-options.update', [$group, $option]) : route('modifier-options.store', $group) }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="card mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $group->name }} Option</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="option-name">Name</label>
                        <input id="option-name" name="name" class="form-control" value="{{ old('name', $option->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="option-price">Price Delta</label>
                        <input id="option-price" type="number" step="0.01" name="price_delta" class="form-control" value="{{ old('price_delta', $option->exists ? number_format($option->price_delta_cents / 100, 2, '.', '') : '0.00') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="option-sort">Sort</label>
                        <input id="option-sort" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $option->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="option-active" @checked(old('is_active', $option->is_active ?? true))>
                            <label class="form-check-label" for="option-active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('menu-categories.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Option' : 'Create Option' }}</button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
