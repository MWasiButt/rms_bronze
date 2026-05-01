@extends('partials.layouts.master')

@section('title', 'Staff & Roles | RMS Bronze')
@section('sub-title', 'Staff & Roles')
@section('pagetitle', 'Management')
@section('buttonTitle', 'Add Staff')
@section('link', 'staff/create')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="card mb-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Staff Accounts</h5>
                <p class="text-muted mb-0">Manage Bronze RMS roles: Owner, Cashier, and Kitchen.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2">{{ $staff->where('is_active', true)->count() }} / {{ $maxActiveUsers }} active users</span>
        </div>
        <div class="card-body p-3">
            <table id="staff-table" class="table table-hover align-middle table-nowrap w-100">
                <thead class="bg-light bg-opacity-30">
                    <tr>
                        <th>Staff</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $member->name }}</div>
                                <div class="fs-12 text-muted">{{ $member->email }}</div>
                            </td>
                            <td><span class="badge bg-dark-subtle text-dark">{{ $member->role->value }}</span></td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $member->last_active_at?->diffForHumans() ?? 'Never' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('staff.edit', $member) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form method="POST" action="{{ route('staff.password-reset', $member) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light-primary">Reset Link</button>
                                    </form>
                                    @if (! $member->is(auth()->user()))
                                        <form method="POST" action="{{ route('staff.status', $member) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $member->is_active ? 'btn-light-danger' : 'btn-light-success' }}">
                                                {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && document.getElementById('staff-table')) {
                $('#staff-table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: false,
                    order: [[0, 'asc']],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search staff',
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    }
                });
            }
        });
    </script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
