@extends('partials.layouts.master')

@section('title', 'Menu Categories | RMS Bronze')
@section('sub-title', 'Menu Categories')
@section('pagetitle', 'Menu Management')
@section('buttonTitle', 'Add Category')
@section('link', 'menu-categories/create')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Categories</p>
                    <h3 class="mb-0">{{ $categories->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Menu Items</p>
                    <h3 class="mb-0">{{ $categories->sum('menu_items_count') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Modifier Groups</p>
                    <h3 class="mb-0">{{ $modifierGroups->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Catalog Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('menu-items.index') }}" class="btn btn-light">View Menu Items</a>
                    <a href="{{ route('modifier-groups.create') }}" class="btn btn-light-primary">Add Modifier Group</a>
                    <span class="badge bg-success-subtle text-success py-2">Menu cache invalidates on every catalog change</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">Category List</h5>
                        <p class="text-muted mb-0">Soft-deleted categories can be restored.</p>
                    </div>
                    <a href="{{ route('menu-categories.create') }}" class="btn btn-sm btn-primary">Add Category</a>
                </div>
                <div class="card-body p-3">
                    <table id="menu-categories-table" class="table table-hover align-middle table-nowrap w-100">
                        <thead class="bg-light bg-opacity-30">
                            <tr>
                                <th>Category</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Sort</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td>{{ $category->menu_items_count }}</td>
                                    <td>
                                        @if ($category->trashed())
                                            <span class="badge bg-danger-subtle text-danger">Archived</span>
                                        @else
                                            <span class="badge {{ $category->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            @if ($category->trashed())
                                                <form method="POST" action="{{ route('menu-categories.restore', $category->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-light-success" type="submit">Restore</button>
                                                </form>
                                            @else
                                                <a href="{{ route('menu-categories.edit', $category) }}" class="btn btn-sm btn-light">Edit</a>
                                                <form method="POST" action="{{ route('menu-categories.destroy', $category) }}">
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
        </div>

        <div class="col-xl-5">
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">Modifier Groups</h5>
                        <p class="text-muted mb-0">Shared option sets for menu items.</p>
                    </div>
                    <a href="{{ route('modifier-groups.create') }}" class="btn btn-sm btn-primary">Add Group</a>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($modifierGroups as $group)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $group->name }}</h6>
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $group->is_required ? 'Required' : 'Optional' }} · Pick {{ $group->min_select }}-{{ $group->max_select }}
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('modifier-groups.edit', $group) }}" class="btn btn-sm btn-light">Edit</a>
                                        <a href="{{ route('modifier-options.create', $group) }}" class="btn btn-sm btn-light-primary">Option</a>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @forelse ($group->options->sortBy('sort_order') as $option)
                                        <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1">
                                            <span class="{{ $option->is_active ? '' : 'text-muted text-decoration-line-through' }}">{{ $option->name }} · {{ number_format($option->price_delta_cents / 100, 2) }}</span>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('modifier-options.edit', [$group, $option]) }}" class="btn btn-sm btn-light">Edit</a>
                                                <form method="POST" action="{{ route('modifier-options.destroy', [$group, $option]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-light-danger" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted fs-13 mb-0">No options added yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No modifier groups yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && document.getElementById('menu-categories-table')) {
                $('#menu-categories-table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: false,
                    info: true,
                    order: [[3, 'asc']],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search categories',
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
