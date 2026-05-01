@extends('partials.layouts.master')

@php
    $isEdit = $staffMember->exists;
@endphp

@section('title', ($isEdit ? 'Edit Staff' : 'Add Staff') . ' | RMS Bronze')
@section('sub-title', $isEdit ? 'Edit Staff' : 'Add Staff')
@section('pagetitle', 'Staff & Roles')
@section('buttonTitle', 'Back to Staff')
@section('link', 'staff')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('staff.update', $staffMember) : route('staff.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="card mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="staff-name">Name</label>
                        <input id="staff-name" name="name" class="form-control" value="{{ old('name', $staffMember->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="staff-email">Email</label>
                        <input id="staff-email" type="email" name="email" class="form-control" value="{{ old('email', $staffMember->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="staff-password">Password {{ $isEdit ? '(optional)' : '' }}</label>
                        <input id="staff-password" type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="staff-password-confirmation">Confirm Password</label>
                        <input id="staff-password-confirmation" type="password" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="staff-role">Role</label>
                        <select id="staff-role" name="role" class="form-select">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role', $staffMember->role?->value) === $role->value)>{{ $role->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="staff-active" @checked(old('is_active', $staffMember->is_active ?? true))>
                            <label class="form-check-label" for="staff-active">Active account</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('staff.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Staff' : 'Create Staff' }}</button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
