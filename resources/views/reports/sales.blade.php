@extends('partials.layouts.master')

@section('title', 'Sales Reports | RMS Bronze')
@section('sub-title', 'Sales Reports')
@section('pagetitle', 'Reporting')
@section('buttonTitle', 'Today')
@section('link', 'reports/sales')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Report Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.sales') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label" for="start_date">Start</label>
                            <input id="start_date" name="start_date" type="date" class="form-control" value="{{ request('start_date', $range->startDate()) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="end_date">End</label>
                            <input id="end_date" name="end_date" type="date" class="form-control" value="{{ request('end_date', $range->endDate()) }}">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Paid Orders</p>
                    <h3 class="mb-0">{{ $summary['order_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Net Sales</p>
                    <h3 class="mb-0">{{ number_format($summary['total_cents'] / 100, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Tax</p>
                    <h3 class="mb-0">{{ number_format($summary['tax_cents'] / 100, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Avg Ticket</p>
                    <h3 class="mb-0">{{ number_format($summary['average_ticket_cents'] / 100, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Z-Read / End of Day</h5>
                    <p class="text-muted mb-0">{{ $range->startDate() }} to {{ $range->endDate() }}</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between"><span class="text-muted">Gross Subtotal</span><span>{{ number_format($summary['subtotal_cents'] / 100, 2) }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Tax</span><span>{{ number_format($summary['tax_cents'] / 100, 2) }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Discounts</span><span>{{ number_format($summary['discount_cents'] / 100, 2) }}</span></div>
                        <div class="d-flex justify-content-between fw-semibold fs-5"><span>Net Sales</span><span>{{ number_format($summary['total_cents'] / 100, 2) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="mb-1">Payment Method Summary</h5>
                    <p class="text-muted mb-0">Cash and card totals for selected period.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($paymentSummary as $payment)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $payment['method'] }}</h6>
                                        <p class="text-muted fs-13 mb-0">{{ $payment['count'] }} payments</p>
                                    </div>
                                    <span class="fw-semibold">{{ number_format($payment['total_cents'] / 100, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments in this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Daily Sales</h5>
                    <p class="text-muted mb-0">Paid order totals by date.</p>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap w-100 mb-0">
                            <thead class="bg-light bg-opacity-30">
                                <tr>
                                    <th>Date</th>
                                    <th>Orders</th>
                                    <th>Subtotal</th>
                                    <th>Tax</th>
                                    <th>Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailySales as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td>{{ $day['orders'] }}</td>
                                        <td>{{ number_format($day['subtotal_cents'] / 100, 2) }}</td>
                                        <td>{{ number_format($day['tax_cents'] / 100, 2) }}</td>
                                        <td>{{ number_format($day['discount_cents'] / 100, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($day['total_cents'] / 100, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted">No paid orders in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 mb-0">
                        <div class="card-header">
                            <h5 class="mb-1">Category Breakdown</h5>
                            <p class="text-muted mb-0">Sales by category.</p>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                @forelse ($categoryBreakdown as $category)
                                    <div class="border rounded-3 p-3">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold">{{ $category['category'] }}</span>
                                            <span>{{ number_format($category['total_cents'] / 100, 2) }}</span>
                                        </div>
                                        <p class="text-muted fs-13 mb-0">Qty {{ number_format($category['quantity'], 2) }}</p>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No category data.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 mb-0">
                        <div class="card-header">
                            <h5 class="mb-1">Item Breakdown</h5>
                            <p class="text-muted mb-0">Top items by sales.</p>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                @forelse ($itemBreakdown as $item)
                                    <div class="border rounded-3 p-3">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold">{{ $item['item_name'] }}</span>
                                            <span>{{ number_format($item['total_cents'] / 100, 2) }}</span>
                                        </div>
                                        <p class="text-muted fs-13 mb-0">{{ $item['sku'] ?: 'No SKU' }} - Qty {{ number_format($item['quantity'], 2) }}</p>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No item data.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
