@extends('partials.layouts.master')

@php($isEdit = $table->exists)

@section('title', ($isEdit ? 'Edit Table' : 'Add Table') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Table' : 'Add Table')
@section('pagetitle', 'Operations')
@section('buttonTitle', 'Back to Tables')
@section('link', 'tables')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">{{ $isEdit ? 'Edit Table' : 'Create Table' }}</h5>
                    <p class="text-muted mb-0">Tables are scoped to this tenant outlet and used by dine-in orders.</p>
                </div>
                <form method="POST" action="{{ $isEdit ? route('tables.update', $table) : route('tables.store') }}">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Table Name</label>
                                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $table->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="code">Code</label>
                                <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $table->code) }}" placeholder="T-01">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="seats">Seats</label>
                                <input id="seats" name="seats" type="number" min="1" max="100" class="form-control @error('seats') is-invalid @enderror" value="{{ old('seats', $table->seats) }}">
                                @error('seats')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $table->is_active))>
                                    <label class="form-check-label" for="is_active">Active for dine-in assignment</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('tables.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Table' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
