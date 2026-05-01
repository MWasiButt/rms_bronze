@extends('partials.layouts.master')

@section('title', 'Kitchen Display | RMS Bronze')
@section('sub-title', 'Kitchen Display')
@section('pagetitle', 'Kitchen')
@section('buttonTitle', 'Orders Queue')
@section('link', 'orders-queue')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    @php
        $allTickets = $columns->flatten(1);
        $pendingCount = $columns->get('PENDING', collect())->count();
        $readyCount = $columns->get('READY', collect())->count();
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Live KOTs</p>
                    <h3 class="mb-0">{{ $allTickets->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Pending</p>
                    <h3 class="mb-0">{{ $pendingCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Ready</p>
                    <h3 class="mb-0">{{ $readyCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($columns as $status => $tickets)
            @php
                $statusLabel = match ($status) {
                    'PENDING' => 'Pending',
                    'PREPARING' => 'In Progress',
                    'READY' => 'Ready',
                    'SERVED' => 'Served',
                    default => $status,
                };
                $badge = match ($status) {
                    'PENDING' => 'bg-warning-subtle text-warning',
                    'PREPARING' => 'bg-primary-subtle text-primary',
                    'READY' => 'bg-info-subtle text-info',
                    'SERVED' => 'bg-success-subtle text-success',
                    default => 'bg-light text-dark',
                };
            @endphp
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h5 class="mb-0">{{ $statusLabel }}</h5>
                        <span class="badge {{ $badge }}">{{ $tickets->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @forelse ($tickets as $ticket)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $ticket->order?->order_number }}</h6>
                                            <p class="text-muted fs-13 mb-0">
                                                {{ $ticket->order?->order_type?->value }}
                                                @if ($ticket->order?->table)
                                                    - {{ $ticket->order->table->name }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="text-muted fs-12">{{ $ticket->created_at?->diffForHumans() }}</span>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mb-3">
                                        @foreach ($ticket->order?->items ?? [] as $item)
                                            <div class="border rounded-2 px-2 py-2">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <span class="fw-semibold">{{ $item->item_name }}</span>
                                                    <span class="badge bg-light text-dark border">x{{ $item->quantity }}</span>
                                                </div>
                                                @if ($item->modifiers->isNotEmpty())
                                                    <p class="text-muted fs-13 mb-0">
                                                        @foreach ($item->modifiers as $modifier)
                                                            {{ $modifier->modifier_option_name }}@if (! $loop->last), @endif
                                                        @endforeach
                                                    </p>
                                                @endif
                                                @if ($item->notes)
                                                    <p class="text-muted fs-13 mb-0">{{ $item->notes }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($ticket->notes)
                                        <p class="text-muted fs-13 mb-3">{{ $ticket->notes }}</p>
                                    @endif

                                    <div class="d-grid gap-2">
                                        @foreach (['PENDING' => 'Pending', 'PREPARING' => 'In Progress', 'READY' => 'Ready', 'SERVED' => 'Served'] as $target => $label)
                                            @if ($target !== $ticket->status->value)
                                                <form method="POST" action="{{ route('kitchen.tickets.update', $ticket) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $target }}">
                                                    <button type="submit" class="btn btn-sm {{ $target === 'READY' ? 'btn-light-primary' : ($target === 'SERVED' ? 'btn-light-success' : 'btn-light') }} w-100">{{ $label }}</button>
                                                </form>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No tickets here.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('js')
    @include('partials.realtime-refresh', ['channels' => 'kitchen'])
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
