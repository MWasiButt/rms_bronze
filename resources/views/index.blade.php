@extends('partials.layouts.master')

@section('title', 'E-Commerce | Herozi - The Worlds Best Selling Bootstrap Admin & Dashboard Template by SRBThemes')
@section('sub-title', 'E-Commerce ')
@section('pagetitle', 'Dashboard')
@section('buttonTitle', 'Add Product')
@section('link', 'apps-product-create')


@section('content')
    @livewire('index')
@endsection

@section('js')
    <!-- Countup init -->
    <script type="module" src="{{ asset('assets/js/pages/countup.init.js') }}"></script>

    <!-- ApexChat js -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Ecommerce dashboard init -->
    <script src="{{ asset('assets/js/charts/apexcharts-config.init.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
