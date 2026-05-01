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
        $liveOrders = $columns->flatten(1);
        $readyCount = $columns->get('READY', collect())->count() + $columns->get('SERVED', collect())->count();
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
                                        <h6 class="mb-0">{{ $order->order_number }}</h6>
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
                                    @if ($order->notes)
                                        <p class="text-muted fs-13 mt-2 mb-0">{{ $order->notes }}</p>
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
