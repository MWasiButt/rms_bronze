@extends('partials.layouts.master')

@section('title', 'POS Orders | RMS Bronze')
@section('sub-title', 'POS / Order Taking')
@section('pagetitle', 'Operations')
@section('buttonTitle', 'View Queue')
@section('link', 'orders-queue')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @php
        $closed = $selectedOrder && in_array($selectedOrder->status->value, ['VOIDED', 'PAID'], true);
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tables</p>
                    <h3 class="mb-0">{{ $tables->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Available</p>
                    <h3 class="mb-0">{{ $tables->where('status', 'Available')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Active Orders</p>
                    <h3 class="mb-0">{{ $openOrders->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Start Order</h5>
                    <p class="text-muted mb-0">Create a dine-in ticket with a table or start a takeaway ticket.</p>
                </div>
                <form method="POST" action="{{ route('orders.start') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label" for="order_type">Order Type</label>
                                <select id="order_type" name="order_type" class="form-select">
                                    <option value="DINE_IN" @selected(old('order_type') === 'DINE_IN')>Dine In</option>
                                    <option value="TAKEAWAY" @selected(old('order_type') === 'TAKEAWAY')>Takeaway</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="dining_table_id">Table</label>
                                <select id="dining_table_id" name="dining_table_id" class="form-select @error('dining_table_id') is-invalid @enderror">
                                    <option value="">Counter / Takeaway</option>
                                    @foreach ($tables as $entry)
                                        <option value="{{ $entry['table']->id }}" @selected((string) old('dining_table_id') === (string) $entry['table']->id) @disabled($entry['status'] !== 'Available')>
                                            {{ $entry['table']->name }}{{ $entry['table']->code ? ' (' . $entry['table']->code . ')' : '' }} - {{ $entry['status'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('dining_table_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="guest_count">Guests</label>
                                <input id="guest_count" name="guest_count" type="number" min="1" max="100" class="form-control @error('guest_count') is-invalid @enderror" value="{{ old('guest_count', 1) }}">
                                @error('guest_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Start Order</button>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Service Notes</label>
                                <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Optional kitchen or service note">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">Menu</h5>
                        <p class="text-muted mb-0">Choose an active order, then add items with modifiers.</p>
                    </div>
                    <span class="badge bg-success-subtle text-success">{{ $items->count() }} Items</span>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="btn btn-primary btn-sm">All Items</span>
                        @foreach ($categories as $category)
                            <span class="btn btn-light btn-sm">{{ $category->name }}</span>
                        @endforeach
                    </div>

                    <div class="row g-3">
                        @forelse ($items as $item)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge bg-primary-subtle text-primary">{{ $item->category?->name ?: 'Uncategorized' }}</span>
                                        <span class="text-muted fs-13">{{ $item->sku ?: 'No SKU' }}</span>
                                    </div>
                                    <h6 class="mb-2">{{ $item->name }}</h6>
                                    <p class="text-muted fs-13 mb-3">{{ $item->description ?: 'Ready for order entry.' }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold">{{ number_format($item->price_cents / 100, 2) }}</span>
                                        <span class="badge {{ $item->tax_mode->value === 'INCLUSIVE' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }}">{{ $item->tax_mode->value }}</span>
                                    </div>

                                    @if ($selectedOrder && ! $closed)
                                        <form method="POST" action="{{ route('orders.items.store', $selectedOrder) }}" class="d-grid gap-2">
                                            @csrf
                                            <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                            <input type="number" min="0.01" step="0.01" name="quantity" class="form-control form-control-sm" value="1">
                                            @foreach ($item->modifierGroups->sortBy('sort_order') as $group)
                                                <div class="border rounded-2 p-2">
                                                    <div class="fw-semibold fs-13 mb-2">{{ $group->name }}</div>
                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach ($group->options->where('is_active', true)->sortBy('sort_order') as $option)
                                                            <label class="form-check fs-13 mb-0">
                                                                <input class="form-check-input" type="checkbox" name="modifier_options[]" value="{{ $option->id }}">
                                                                <span class="form-check-label">{{ $option->name }} @if ($option->price_delta_cents !== 0) +{{ number_format($option->price_delta_cents / 100, 2) }} @endif</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                            <button type="submit" class="btn btn-sm btn-light-primary">Add to Order</button>
                                        </form>
                                    @elseif (! $selectedOrder)
                                        <span class="badge bg-warning-subtle text-warning py-2 w-100">Start or select an order first</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary py-2 w-100">Order closed</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted mb-0">No menu items available yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">Current Order</h5>
                        <p class="text-muted mb-0">{{ $selectedOrder?->order_number ?: 'No order selected' }}</p>
                    </div>
                    @if ($selectedOrder)
                        <span class="badge bg-light text-dark border">{{ $selectedOrder->status->value }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($selectedOrder)
                        <div class="d-flex flex-column gap-3">
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Type</span>
                                    <span>{{ $selectedOrder->order_type->value }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Table</span>
                                    <span>{{ $selectedOrder->table?->name ?: 'Counter' }}</span>
                                </div>
                            </div>

                            @forelse ($selectedOrder->items as $line)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $line->item_name }}</h6>
                                            <p class="text-muted fs-13 mb-0">
                                                {{ number_format($line->unit_price_cents / 100, 2) }}
                                                @foreach ($line->modifiers as $modifier)
                                                    - {{ $modifier->modifier_option_name }} @if ($modifier->price_delta_cents !== 0) +{{ number_format($modifier->price_delta_cents / 100, 2) }} @endif
                                                @endforeach
                                            </p>
                                        </div>
                                        <span class="fw-semibold">{{ number_format($line->line_total_cents / 100, 2) }}</span>
                                    </div>

                                    @if (! $closed)
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="{{ route('orders.items.update', [$selectedOrder, $line]) }}" class="d-flex gap-2 flex-grow-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" min="0.01" step="0.01" name="quantity" class="form-control form-control-sm" value="{{ $line->quantity }}">
                                                <button type="submit" class="btn btn-sm btn-light">Qty</button>
                                            </form>
                                            <form method="POST" action="{{ route('orders.items.destroy', [$selectedOrder, $line]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light-danger">Remove</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">No items added yet.</p>
                            @endforelse
                        </div>
                    @else
                        <p class="text-muted mb-0">Start a new order or select an active one below.</p>
                    @endif
                </div>
                @if ($selectedOrder)
                    <div class="card-footer">
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>{{ number_format($selectedOrder->subtotal_cents / 100, 2) }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted">Tax</span><span>{{ number_format($selectedOrder->tax_cents / 100, 2) }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted">Discount</span><span>{{ number_format($selectedOrder->discount_cents / 100, 2) }}</span></div>
                            <div class="d-flex justify-content-between fw-semibold fs-5"><span>Total</span><span>{{ number_format($selectedOrder->total_cents / 100, 2) }}</span></div>
                        </div>

                        @if (! $closed)
                            <form method="POST" action="{{ route('orders.discount', $selectedOrder) }}" class="row g-2 mb-3">
                                @csrf
                                @method('PATCH')
                                <div class="col-5">
                                    <select name="discount_type" class="form-select form-select-sm">
                                        <option value="flat">Flat</option>
                                        <option value="percent">Percent</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" min="0" step="0.01" name="discount_value" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-3">
                                    <button type="submit" class="btn btn-sm btn-light-primary w-100">Apply</button>
                                </div>
                            </form>

                            <div class="d-grid gap-2">
                                <form method="POST" action="{{ route('orders.status', $selectedOrder) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="SENT_TO_KITCHEN">
                                    <button type="submit" class="btn btn-primary w-100">Send to Kitchen</button>
                                </form>
                                <div class="d-flex gap-2">
                                    @foreach (['READY' => 'Ready', 'SERVED' => 'Served'] as $status => $label)
                                        <form method="POST" action="{{ route('orders.status', $selectedOrder) }}" class="flex-fill">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <button type="submit" class="btn btn-light w-100">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                                <form method="POST" action="{{ route('orders.payments.store', $selectedOrder) }}">
                                    @csrf
                                    <div class="d-grid gap-2">
                                        <div class="input-group">
                                            <select name="method" class="form-select">
                                                <option value="CASH">Cash</option>
                                                <option value="CARD">Card</option>
                                            </select>
                                            <input type="number" min="0" step="0.01" name="amount" class="form-control" value="{{ number_format(($selectedOrder->total_cents - $selectedOrder->payments->sum('amount_cents')) / 100, 2, '.', '') }}">
                                        </div>
                                        <input type="text" name="reference" class="form-control" placeholder="Reference optional">
                                        <button type="submit" class="btn btn-success w-100">Take Payment</button>
                                    </div>
                                </form>
                                @if ($selectedOrder->payments->isNotEmpty())
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('receipts.show', $selectedOrder) }}" class="btn btn-light-primary flex-fill">Thermal Receipt</a>
                                        <a href="{{ route('receipts.pdf', $selectedOrder) }}" class="btn btn-light flex-fill">PDF Receipt</a>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('orders.status', $selectedOrder) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="VOIDED">
                                    <button type="submit" class="btn btn-light-danger w-100">Void Order</button>
                                </form>
                            </div>
                        @else
                            <div class="d-grid gap-2">
                                @if ($selectedOrder->status->value === 'PAID')
                                    <a href="{{ route('receipts.show', $selectedOrder) }}" class="btn btn-light-primary">Thermal Receipt</a>
                                    <a href="{{ route('receipts.pdf', $selectedOrder) }}" class="btn btn-light">PDF Receipt</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Payments</h5>
                    <p class="text-muted mb-0">Exact payment is required before an order can close.</p>
                </div>
                <div class="card-body">
                    @if ($selectedOrder)
                        <div class="d-flex flex-column gap-2">
                            @forelse ($selectedOrder->payments as $payment)
                                <div class="d-flex justify-content-between border rounded-2 px-2 py-2">
                                    <span>{{ $payment->method->value }}</span>
                                    <span class="fw-semibold">{{ number_format($payment->amount_cents / 100, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No payments yet.</p>
                            @endforelse
                        </div>
                    @else
                        <p class="text-muted mb-0">Select an order to view payments.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Active Orders</h5>
                    <p class="text-muted mb-0">Select a ticket to edit it.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($openOrders as $order)
                            <a href="{{ route('service.pos', ['order' => $order->id]) }}" class="border rounded-3 p-3 text-reset">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h6 class="mb-0">{{ $order->order_number }}</h6>
                                    <span class="badge bg-light text-dark border">{{ $order->status->value }}</span>
                                </div>
                                <p class="text-muted fs-13 mb-0">
                                    {{ $order->order_type->value }}
                                    @if ($order->table)
                                        - {{ $order->table->name }}
                                    @endif
                                </p>
                            </a>
                        @empty
                            <p class="text-muted mb-0">No active orders yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">Table Status</h5>
                    <p class="text-muted mb-0">Live service state by table.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($tables as $entry)
                            @php
                                $badge = match ($entry['status']) {
                                    'Available' => 'bg-success-subtle text-success',
                                    'Occupied' => 'bg-warning-subtle text-warning',
                                    'Ordered' => 'bg-primary-subtle text-primary',
                                    'Ready' => 'bg-info-subtle text-info',
                                    'Served' => 'bg-secondary-subtle text-secondary',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h6 class="mb-1">{{ $entry['table']->name }}</h6>
                                        <p class="text-muted fs-13 mb-0">
                                            {{ $entry['table']->seats ?: 0 }} seats
                                            @if ($entry['order'])
                                                - {{ $entry['order']->order_number }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="badge {{ $badge }}">{{ $entry['status'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No active tables. Add tables first.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('partials.realtime-refresh', ['channels' => 'orders,kitchen,print'])
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
