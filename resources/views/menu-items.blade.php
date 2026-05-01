@extends('partials.layouts.master')

@section('title', 'Menu Items | RMS Bronze')
@section('sub-title', 'Menu Items')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Add Menu Item')
@section('link', 'menu-items/create')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Visible Items</p>
                    <h3 class="mb-0">{{ $items->where('deleted_at', null)->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Active</p>
                    <h3 class="mb-0">{{ $items->where('is_active', true)->where('deleted_at', null)->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Archived</p>
                    <h3 class="mb-0">{{ $items->filter->trashed()->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Actions</h5>
                </div>
                <div class="card-body d-grid gap-2 align-content-center">
                    <a href="{{ route('menu-categories.index') }}" class="btn btn-light">Categories & Modifiers</a>
                    <a href="{{ route('menu-items.create') }}" class="btn btn-primary">Add Menu Item</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Menu Item List</h5>
                <p class="text-muted mb-0">Soft-deleted items can be restored.</p>
            </div>
        </div>
        <div class="card-body p-3">
            <table id="menu-items-table" class="table table-hover align-middle table-nowrap w-100">
                <thead class="bg-light bg-opacity-30">
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Tax</th>
                        <th>Modifiers</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->name }}</div>
                                <div class="fs-12 text-muted">{{ $item->sku ?: 'No SKU' }}</div>
                            </td>
                            <td>{{ $item->category?->name ?? '-' }}</td>
                            <td>{{ number_format($item->price_cents / 100, 2) }}</td>
                            <td>{{ number_format($item->cost_cents / 100, 2) }}</td>
                            <td>{{ number_format($item->tax_rate_bps / 100, 2) }}% {{ $item->tax_mode->value }}</td>
                            <td>{{ $item->modifierGroups->pluck('name')->join(', ') ?: '-' }}</td>
                            <td>
                                @if ($item->trashed())
                                    <span class="badge bg-danger-subtle text-danger">Archived</span>
                                @else
                                    <span class="badge {{ $item->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    @if ($item->trashed())
                                        <form method="POST" action="{{ route('menu-items.restore', $item->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-light-success" type="submit">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('menu-items.edit', $item) }}" class="btn btn-sm btn-light">Edit</a>
                                        <form method="POST" action="{{ route('menu-items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light-danger" type="submit">Archive</button>
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
            if (window.jQuery && document.getElementById('menu-items-table')) {
                $('#menu-items-table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: false,
                    info: true,
                    order: [[0, 'asc']],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search menu items',
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
