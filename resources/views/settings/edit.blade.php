@extends('partials.layouts.master')

@section('title', 'Settings | RMS Bronze')
@section('sub-title', 'Settings')
@section('pagetitle', 'Management')
@section('buttonTitle', 'Dashboard')
@section('link', '/')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-1">Tenant Settings</h5>
                        <p class="text-muted mb-0">Business defaults used by receipts and calculations.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="currency">Currency</label>
                                <input id="currency" name="currency" maxlength="3" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', $tenantSetting->currency) }}" required>
                                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="timezone">Timezone</label>
                                <select id="timezone" name="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $tenantSetting->timezone) === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="default_tax_rate">Default Tax %</label>
                                <input id="default_tax_rate" name="default_tax_rate" type="number" min="0" max="100" step="0.01" class="form-control @error('default_tax_rate') is-invalid @enderror" value="{{ old('default_tax_rate', number_format($tenantSetting->default_tax_rate_bps / 100, 2, '.', '')) }}" required>
                                @error('default_tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="receipt_header">Receipt Header</label>
                                <input id="receipt_header" name="receipt_header" class="form-control @error('receipt_header') is-invalid @enderror" value="{{ old('receipt_header', $tenantSetting->receipt_header) }}">
                                @error('receipt_header')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="receipt_footer">Receipt Footer</label>
                                <textarea id="receipt_footer" name="receipt_footer" rows="3" class="form-control @error('receipt_footer') is-invalid @enderror">{{ old('receipt_footer', $tenantSetting->receipt_footer) }}</textarea>
                                @error('receipt_footer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header">
                        <h5 class="mb-1">Bronze Feature Flags</h5>
                        <p class="text-muted mb-0">Locked plan capabilities.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ([
                                'qr_ordering' => false,
                                'delivery' => false,
                                'inventory_basic' => true,
                                'kds_basic' => true,
                                'api_read' => false,
                            ] as $flag => $enabled)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">{{ $flag }}</span>
                                        <span class="badge {{ $enabled ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $enabled ? 'true' : 'false' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-1">Outlet Settings</h5>
                        <p class="text-muted mb-0">Single outlet details for this tenant.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="outlet_name">Name</label>
                                <input id="outlet_name" name="outlet_name" class="form-control @error('outlet_name') is-invalid @enderror" value="{{ old('outlet_name', $outlet->name) }}" required>
                                @error('outlet_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="outlet_code">Code</label>
                                <input id="outlet_code" name="outlet_code" class="form-control @error('outlet_code') is-invalid @enderror" value="{{ old('outlet_code', $outlet->code) }}">
                                @error('outlet_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="outlet_phone">Phone</label>
                                <input id="outlet_phone" name="outlet_phone" class="form-control @error('outlet_phone') is-invalid @enderror" value="{{ old('outlet_phone', $outlet->phone) }}">
                                @error('outlet_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="outlet_email">Email</label>
                                <input id="outlet_email" name="outlet_email" type="email" class="form-control @error('outlet_email') is-invalid @enderror" value="{{ old('outlet_email', $outlet->email) }}">
                                @error('outlet_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="outlet_address">Address</label>
                                <textarea id="outlet_address" name="outlet_address" rows="3" class="form-control @error('outlet_address') is-invalid @enderror">{{ old('outlet_address', $outlet->address) }}</textarea>
                                @error('outlet_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header">
                        <h5 class="mb-1">Service Settings</h5>
                        <p class="text-muted mb-0">Basic outlet service behavior.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="service_charge_rate">Service Charge %</label>
                                <input id="service_charge_rate" name="service_charge_rate" type="number" min="0" max="100" step="0.01" class="form-control @error('service_charge_rate') is-invalid @enderror" value="{{ old('service_charge_rate', number_format($outletSetting->service_charge_bps / 100, 2, '.', '')) }}" required>
                                @error('service_charge_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="service_charge_enabled" name="service_charge_enabled" value="1" @checked(old('service_charge_enabled', $outletSetting->service_charge_enabled))>
                                    <label class="form-check-label" for="service_charge_enabled">Enable service charge</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="service_notes">Service Notes</label>
                                <textarea id="service_notes" name="service_notes" rows="3" class="form-control @error('service_notes') is-invalid @enderror">{{ old('service_notes', $outletSetting->notes) }}</textarea>
                                @error('service_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
