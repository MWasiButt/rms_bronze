@extends('partials.layouts.master')

@php($isEdit = $category->exists)

@section('title', ($isEdit ? 'Edit Category' : 'Add Category') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Category' : 'Add Category')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Back to Categories')
@section('link', 'menu-categories')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('menu-categories.update', $category) : route('menu-categories.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="card mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Category Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category-name">Name</label>
                        <input id="category-name" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="category-sort">Sort Order</label>
                        <input id="category-sort" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="category-active" @checked(old('is_active', $category->is_active ?? true))>
                            <label class="form-check-label" for="category-active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('menu-categories.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Category' : 'Create Category' }}</button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
