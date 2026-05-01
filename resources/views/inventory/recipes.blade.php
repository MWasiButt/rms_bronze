@extends('partials.layouts.master')

@section('title', 'Recipes / BOM | RMS Bronze')
@section('sub-title', 'Recipes / BOM')
@section('pagetitle', 'Inventory')
@section('buttonTitle', 'Stock Items')
@section('link', 'stock-items')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="card mb-0">
        <div class="card-header">
            <h5 class="mb-1">Menu Item Recipes</h5>
            <p class="text-muted mb-0">Link menu items to stock items for automatic sale deduction.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse ($menuItems as $item)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $item->name }}</h6>
                                    <p class="text-muted fs-13 mb-0">{{ $item->sku }}</p>
                                </div>
                                <span class="badge {{ $item->recipe ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $item->recipe ? 'Linked' : 'No BOM' }}</span>
                            </div>
                            <div class="d-flex flex-column gap-2 mb-3">
                                @forelse ($item->recipe?->items ?? [] as $recipeItem)
                                    <div class="d-flex justify-content-between border rounded-2 px-2 py-1">
                                        <span>{{ $recipeItem->stockItem?->name }}</span>
                                        <span>{{ number_format((float) $recipeItem->quantity, 3) }}</span>
                                    </div>
                                @empty
                                    <p class="text-muted fs-13 mb-0">No stock items linked.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('recipes.edit', $item) }}" class="btn btn-sm btn-light-primary w-100">Edit Recipe</a>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted mb-0">No menu items found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
