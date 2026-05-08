@extends('partials.layouts.master')

@section('title', 'Orders Queue | RMS Bronze')
@section('sub-title', 'Orders Queue')
@section('pagetitle', 'Operations')
@section('buttonTitle', 'Open POS')
@section('link', 'pos-orders')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    @php
        use App\Enums\UserRole;

        $liveOrders = $columns->flatten(1);
        $readyCount = $columns->get('READY', collect())->count() + $columns->get('SERVED', collect())->count();
        $canApproveOrders = in_array(auth()->user()->role, [UserRole::OWNER, UserRole::CASHIER], true);
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Live Orders</p>
                    <h3 class="mb-0">{{ $liveOrders->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Ready/Served</p>
                    <h3 class="mb-0">{{ $readyCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Open</p>
                    <h3 class="mb-0">{{ $columns->get('OPEN', collect())->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">Payment Method Summary</h5>
                    <p class="text-muted mb-0">Today by method for this outlet.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($paymentSummary as $summary)
                            <div class="col-md-6 col-xl-3">
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">{{ $summary->method }}</span>
                                        <span class="badge bg-success-subtle text-success">{{ $summary->payments_count }}</span>
                                    </div>
                                    <div class="fs-4 fw-semibold mt-2">{{ number_format($summary->total_cents / 100, 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted mb-0">No payments recorded today.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($columns as $status => $orders)
            @php
                $statusLabel = str_replace('_', ' ', $status);
                $badge = match ($status) {
                    'OPEN' => 'bg-warning-subtle text-warning',
                    'SENT_TO_KITCHEN' => 'bg-primary-subtle text-primary',
                    'READY' => 'bg-info-subtle text-info',
                    'SERVED' => 'bg-secondary-subtle text-secondary',
                    'VOIDED' => 'bg-danger-subtle text-danger',
                    'PAID' => 'bg-success-subtle text-success',
                    default => 'bg-light text-dark',
                };
            @endphp
            <div class="col-md-6 col-xl">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h5 class="mb-0">{{ $statusLabel }}</h5>
                        <span class="badge {{ $badge }}">{{ $orders->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @forelse ($orders as $order)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $order->order_number }}</h6>
                                            @if ($status === 'OPEN')
                                                <span class="badge bg-warning-subtle text-warning">Pending manager approval</span>
                                            @endif
                                        </div>
                                        <span class="text-muted fs-12">{{ $order->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-muted fs-13 mb-2">
                                        {{ $order->order_type->value }}
                                        @if ($order->table)
                                            - {{ $order->table->name }}
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">{{ number_format($order->total_cents / 100, 2) }}</span>
                                        <span class="badge bg-light text-dark border">{{ $order->guest_count }} guests</span>
                                    </div>

                                    @if ($order->items->isNotEmpty())
                                        <div class="d-flex flex-column gap-2 mt-3">
                                            @foreach ($order->items as $item)
                                                <div class="border rounded-2 px-2 py-2 bg-light">
                                                    <div class="d-flex justify-content-between gap-2">
                                                        <span class="fw-semibold fs-13">{{ $item->item_name }}</span>
                                                        <span class="badge bg-white text-dark border">x{{ $item->quantity }}</span>
                                                    </div>
                                                    @if ($item->modifiers->isNotEmpty())
                                                        <p class="text-muted fs-12 mb-0">
                                                            @foreach ($item->modifiers as $modifier)
                                                                {{ $modifier->modifier_option_name }}@if (! $loop->last), @endif
                                                            @endforeach
                                                        </p>
                                                    @endif
                                                    @if ($item->notes)
                                                        <p class="text-muted fs-12 mb-0">{{ $item->notes }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($order->notes)
                                        <p class="text-muted fs-13 mt-2 mb-0">{{ $order->notes }}</p>
                                    @endif

                                    @if ($status === 'OPEN' && $canApproveOrders)
                                        <form method="POST" action="{{ route('orders.status', $order) }}" class="mt-3">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="SENT_TO_KITCHEN">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                Approve & Send to Kitchen
                                            </button>
                                        </form>
                                    @elseif ($status === 'OPEN')
                                        <p class="text-muted fs-12 mt-3 mb-0">Waiting for manager approval.</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">No orders in this status.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('js')
    @include('partials.realtime-refresh', ['channels' => 'orders,kitchen,print'])
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
