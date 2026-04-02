@extends('partials.layouts.master-auth')

@section('title', 'Sign Up | RMS Bronze')

@section('css')
    @include('partials.head-css', ['auth' => 'layout-auth'])
@endsection

@section('content')

    <div class="account-pages">
        <img src="{{ asset('assets/images/auth/auth_bg.jpeg') }}" alt="auth_bg" class="auth-bg light">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="auth_bg_dark" class="auth-bg dark">
        <div class="container">
            <div class="justify-content-center row gy-0">

                <div class="col-lg-6 auth-banners">
                    <div class="bg-login card card-body m-0 h-100 border-0">
                        <img src="{{ asset('assets/images/auth/bg-img-2.png') }}" class="img-fluid auth-banner"
                            alt="auth-banner">
                        <div class="auth-contain">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                        class="active" aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                        aria-label="Slide 3"></button>
                                </div>
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">Launch Your Outlet</h3>
                                            <p class="mt-3">Create your Bronze workspace, first outlet, and owner account in one step.</p>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">Built for Daily Service</h3>
                                            <p class="mt-3">Set up your business foundation now so menu, POS, kitchen, and reporting modules plug in cleanly.</p>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">Tenant-Aware From Day One</h3>
                                            <p class="mt-3">Each signup creates an isolated business space with its own outlet, settings, and owner user.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-normal">Create your <span class="fw-bold text-primary">RMS Bronze</span> workspace</h4>
                            <p class="text-muted mb-0">We will create your business, first outlet, and owner account together.</p>
                        </div>
                        <form class="form-custom mt-10" method="POST" action="{{ route('register.store') }}">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="business-name">Business Name<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control @error('business_name') is-invalid @enderror"
                                        id="business-name" name="business_name" value="{{ old('business_name') }}"
                                        placeholder="Bronze Cafe" required>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="outlet-name">Outlet Name<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control @error('outlet_name') is-invalid @enderror"
                                        id="outlet-name" name="outlet_name" value="{{ old('outlet_name', 'Main Outlet') }}"
                                        placeholder="Main Outlet" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="owner-name">Owner Name<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="owner-name" name="name" value="{{ old('name') }}"
                                        placeholder="Enter owner name" required>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="login-email">Email<span class="text-danger ms-1">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="login-email" name="email" value="{{ old('email') }}"
                                        placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}"
                                        placeholder="Optional phone number">
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label" for="address">Address</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        id="address" name="address" value="{{ old('address') }}"
                                        placeholder="Optional outlet address">
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="LoginPassword">Password<span class="text-danger ms-1">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="LoginPassword"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="Enter your password" data-visible="false" required>
                                    <a class="input-group-text bg-transparent toggle-password" href="javascript:;"
                                        data-target="password">
                                        <i class="ri-eye-off-line text-muted toggle-icon"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="LoginPasswordConfirmation">Confirm Password<span class="text-danger ms-1">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="LoginPasswordConfirmation" class="form-control"
                                        name="password_confirmation" placeholder="Confirm your password"
                                        data-visible="false" required>
                                    <a class="input-group-text bg-transparent toggle-password" href="javascript:;"
                                        data-target="password_confirmation">
                                        <i class="ri-eye-off-line text-muted toggle-icon"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <p class="mb-0 fs-xs text-muted fst-italic">By registering you agree to the RMS Bronze
                                    <a href="#!" class="text-primary text-decoration-underline fst-normal fw-medium">Terms of Use</a>
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-2 w-100">
                                <span class="indicator-label">Create Business Account</span>
                            </button>

                            <p class="mb-0 mt-5 text-muted text-center">
                                Already have an account ?
                                <a href="{{ route('login') }}" class="text-primary fw-medium text-decoraton-underline ms-1">
                                    Sign In
                                </a>
                            </p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
