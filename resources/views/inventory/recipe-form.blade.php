@extends('partials.layouts.master')

@section('title', 'Edit Recipe | RMS Bronze')
@section('sub-title', 'Edit Recipe')
@section('pagetitle', 'Inventory')
@section('buttonTitle', 'Recipes')
@section('link', 'recipes')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">{{ $menuItem->name }} Recipe</h5>
                    <p class="text-muted mb-0">Set the quantity of each stock item consumed by this menu item.</p>
                </div>
                <form method="POST" action="{{ route('recipes.update', $menuItem) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="yield_quantity">Yield Quantity</label>
                                <input id="yield_quantity" name="yield_quantity" type="number" min="0.001" step="0.001" class="form-control" value="{{ old('yield_quantity', $menuItem->recipe?->yield_quantity ?: 1) }}" required>
                            </div>
                        </div>

                        @php($existing = $menuItem->recipe?->items?->keyBy('stock_item_id') ?? collect())

                        <div class="row g-3">
                            @foreach ($stockItems as $stockItem)
                                @php($recipeItem = $existing->get($stockItem->id))
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3">
                                        <input type="hidden" name="stock_item_ids[]" value="{{ $stockItem->id }}">
                                        <label class="form-label">{{ $stockItem->name }} <span class="text-muted">({{ $stockItem->unit }})</span></label>
                                        <input name="quantities[]" type="number" min="0" step="0.001" class="form-control" value="{{ old('quantities.'.$loop->index, $recipeItem?->quantity ?: 0) }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('recipes.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Recipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
