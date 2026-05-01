@extends('partials.layouts.master')

@php($isEdit = $group->exists)

@section('title', ($isEdit ? 'Edit Modifier Group' : 'Add Modifier Group') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Modifier Group' : 'Add Modifier Group')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Back to Categories')
@section('link', 'menu-categories')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('modifier-groups.update', $group) : route('modifier-groups.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="card mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Modifier Group</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="group-name">Name</label>
                        <input id="group-name" name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="group-min">Min Select</label>
                        <input id="group-min" type="number" min="0" name="min_select" class="form-control" value="{{ old('min_select', $group->min_select ?? 0) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="group-max">Max Select</label>
                        <input id="group-max" type="number" min="1" name="max_select" class="form-control" value="{{ old('max_select', $group->max_select ?? 1) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="group-sort">Sort</label>
                        <input id="group-sort" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $group->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_required" value="0">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="group-required" @checked(old('is_required', $group->is_required ?? false))>
                            <label class="form-check-label" for="group-required">Required modifier group</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('menu-categories.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Group' : 'Create Group' }}</button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
