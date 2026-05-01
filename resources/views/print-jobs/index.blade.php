@extends('partials.layouts.master')

@section('title', 'Print Queue | RMS Bronze')
@section('sub-title', 'Print Queue')
@section('pagetitle', 'Printing')
@section('buttonTitle', 'POS')
@section('link', 'pos-orders')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        @foreach (['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'] as $status)
            <div class="col-md-6 col-xl-3">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">{{ $status }}</p>
                        <h3 class="mb-0">{{ $summary->get($status, 0) }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-0">
        <div class="card-header">
            <h5 class="mb-1">Latest Jobs</h5>
            <p class="text-muted mb-0">Showing latest 100 print jobs for this outlet.</p>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap w-100 mb-0">
                    <thead class="bg-light bg-opacity-30">
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Copies</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $job)
                            @php
                                $badge = match ($job->status->value) {
                                    'PENDING' => 'bg-warning-subtle text-warning',
                                    'PROCESSING' => 'bg-primary-subtle text-primary',
                                    'COMPLETED' => 'bg-success-subtle text-success',
                                    'FAILED' => 'bg-danger-subtle text-danger',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <tr>
                                <td>#{{ $job->id }}</td>
                                <td>{{ $job->type->value }}</td>
                                <td>{{ $job->order?->order_number ?: '-' }}</td>
                                <td><span class="badge {{ $badge }}">{{ $job->status->value }}</span></td>
                                <td>{{ $job->copies }}</td>
                                <td>{{ $job->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    @if ($job->status->value === 'FAILED')
                                        <form method="POST" action="{{ route('print-jobs.retry', $job) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-light-primary" type="submit">Retry</button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted">No print jobs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('partials.realtime-refresh', ['channels' => 'print'])
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
