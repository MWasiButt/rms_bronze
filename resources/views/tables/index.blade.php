@extends('partials.layouts.master')

@section('title', 'Tables | RMS Bronze')
@section('sub-title', 'Table Management')
@section('pagetitle', 'Operations')
@section('buttonTitle', 'Add Table')
@section('link', 'tables/create')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tables</p>
                    <h3 class="mb-0">{{ $tables->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Active</p>
                    <h3 class="mb-0">{{ $tables->where('is_active', true)->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Seats</p>
                    <h3 class="mb-0">{{ $tables->sum('seats') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('tables.create') }}" class="btn btn-primary">Add Table</a>
                    <a href="{{ route('service.pos') }}" class="btn btn-light">Open POS Screen</a>
                    <a href="{{ route('orders.queue') }}" class="btn btn-light-primary">View Orders Queue</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Outlet Tables</h5>
                <p class="text-muted mb-0">Inactive tables stay hidden from dine-in assignment.</p>
            </div>
            <a href="{{ route('tables.create') }}" class="btn btn-sm btn-primary">Add Table</a>
        </div>
        <div class="card-body p-3">
            <table id="tables-table" class="table table-hover align-middle table-nowrap w-100">
                <thead class="bg-light bg-opacity-30">
                    <tr>
                        <th>Table</th>
                        <th>Code</th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tables as $table)
                        <tr>
                            <td class="fw-semibold">{{ $table->name }}</td>
                            <td>{{ $table->code ?: '-' }}</td>
                            <td>{{ $table->seats ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $table->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $table->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form method="POST" action="{{ route('tables.destroy', $table) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                                    </form>
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
            if (window.jQuery && document.getElementById('tables-table')) {
                $('#tables-table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: false,
                    info: true,
                    language: {
                        search: '',
                        searchPlaceholder: 'Search tables',
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
