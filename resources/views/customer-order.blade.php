@php
    $tableNumber = request('table', $table ?? '12');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RMS Bronze Cafe - Order</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/Favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --rms-primary: #2563EB;
            --rms-primary-hover: #1D4ED8;
            --rms-primary-soft: #EFF6FF;
            --rms-navy: #0F172A;
            --rms-dark: #111827;
            --rms-muted: #6B7280;
            --rms-bg: #F8FAFC;
            --rms-card: #FFFFFF;
            --rms-border: #E5E7EB;
            --rms-shadow: rgba(15, 23, 42, 0.08);
            --status-received: #2563EB;
            --status-preparing: #F59E0B;
            --status-ready: #16A34A;
            --status-served: #166534;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--rms-bg);
            color: var(--rms-dark);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .rms-primary { color: var(--rms-primary) !important; }
        .bg-primary { background-color: var(--rms-primary) !important; }
        .bg-primary-soft { background-color: var(--rms-primary-soft) !important; }
        .rms-navy { color: var(--rms-navy) !important; }
        .rms-border { border-color: var(--rms-border) !important; }
        .cursor-pointer { cursor: pointer; }
        .z-index-toast { z-index: 1070; }
        .transition-all { transition: all 0.2s ease-in-out; }

        .btn-primary {
            background-color: var(--rms-primary);
            border-color: var(--rms-primary);
        }
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: var(--rms-primary-hover) !important;
            border-color: var(--rms-primary-hover) !important;
        }
        .btn-outline-primary {
            color: var(--rms-primary);
            border-color: var(--rms-primary);
        }
        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active,
        .btn-outline-primary.active {
            background-color: var(--rms-primary-soft) !important;
            color: var(--rms-primary) !important;
            border-color: var(--rms-primary) !important;
        }
        .text-primary { color: var(--rms-primary) !important; }
        .border-primary { border-color: var(--rms-primary) !important; }

        .main-container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .rms-header {
            background-color: var(--rms-card);
            border-bottom: 1px solid var(--rms-border);
            box-shadow: 0 4px 20px -10px var(--rms-shadow);
            padding: 0.75rem 0;
        }
        .rms-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--rms-primary), var(--rms-primary-hover));
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .rms-small {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .hero-section {
            background-color: var(--rms-card);
            border: 1px solid var(--rms-border);
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, var(--rms-primary-soft) 0%, rgba(255,255,255,0) 100%);
            opacity: 0.8;
        }

        .order-type-card {
            transition: all 0.2s ease;
            cursor: pointer;
            background-color: var(--rms-card);
        }
        .order-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px var(--rms-shadow) !important;
        }
        .order-type-card.active {
            background-color: var(--rms-primary-soft);
            border: 2px solid var(--rms-primary) !important;
        }
        .order-type-card.active i,
        .order-type-card.active h4 {
            color: var(--rms-primary) !important;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .category-chip {
            white-space: nowrap;
            background-color: var(--rms-card);
            border: 1px solid var(--rms-border);
            color: var(--rms-navy);
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .category-chip.active {
            background-color: var(--rms-primary);
            border-color: var(--rms-primary);
            color: white;
        }
        .category-wrapper {
            margin-left: -0.75rem;
            margin-right: -0.75rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            background: linear-gradient(to bottom, var(--rms-bg) 60%, rgba(248, 250, 252, 0) 100%) !important;
        }

        .menu-card {
            border-radius: 20px;
            border: 1px solid var(--rms-border);
            background: var(--rms-card);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .menu-card.unavailable {
            opacity: 0.6;
            filter: grayscale(100%);
        }
        .menu-card.unavailable .btn {
            pointer-events: none;
            background-color: var(--rms-muted) !important;
            border-color: var(--rms-muted) !important;
            color: white !important;
        }
        @media (min-width: 992px) {
            .menu-card:not(.unavailable):hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
            }
        }
        .menu-card-img-wrapper {
            position: relative;
            padding-top: 65%;
            overflow: hidden;
            background-color: var(--rms-bg);
        }
        .menu-card-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .menu-card:not(.unavailable):hover .menu-card-img {
            transform: scale(1.08);
        }
        .menu-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1;
            font-size: 0.7rem;
            padding: 6px 10px;
            border-radius: 8px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(4px);
            font-weight: 700;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .prep-time {
            font-size: 0.7rem;
            color: var(--rms-muted);
            font-weight: 500;
        }
        .customizable-badge {
            font-size: 0.65rem;
            background-color: var(--rms-primary-soft);
            color: var(--rms-primary);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .custom-modal .modal-content { border-radius: 24px; }
        @media (max-width: 576px) {
            .modal.fade .modal-dialog.modal-fullscreen-sm-down {
                transition: transform 0.3s ease-out;
                transform: translateY(100%);
            }
            .modal.show .modal-dialog.modal-fullscreen-sm-down {
                transform: translateY(0);
            }
            .modal-fullscreen-sm-down {
                align-items: flex-end;
                margin: 0;
                min-height: 100%;
            }
            .modal-fullscreen-sm-down .modal-content {
                border-radius: 28px 28px 0 0 !important;
                border: none;
            }
        }
        .radio-card input:checked + span,
        .checkbox-card input:checked ~ span {
            font-weight: 700;
            color: var(--rms-primary) !important;
        }
        .radio-card:has(input:checked),
        .checkbox-card:has(input:checked) {
            border-color: var(--rms-primary) !important;
            background-color: var(--rms-primary-soft);
        }
        .spice-btn { color: var(--rms-muted); }
        input[name="spiceLvl"]:checked + .spice-btn {
            background-color: var(--rms-primary) !important;
            color: white !important;
            font-weight: 700;
        }

        .form-control:focus {
            border-color: var(--rms-primary);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        }
        .input-group-text,
        .form-control {
            border-color: var(--rms-border);
        }
        .sticky-cart-bar {
            padding-bottom: env(safe-area-inset-bottom);
        }
        .slide-up {
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .timeline-dot {
            top: 3px;
            box-shadow: 0 0 0 4px var(--rms-card);
        }
        body.has-cart {
            padding-bottom: 100px !important;
        }
        .payment-method-option:has(input:checked) {
            border-color: var(--rms-primary) !important;
            background-color: var(--rms-primary-soft);
        }
        .payment-method-option:has(input:checked) span.flex-grow-1 {
            color: var(--rms-primary) !important;
        }
        .cart-qty-btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar sticky-top rms-header z-3">
        <div class="container-fluid align-items-center main-container">
            <div class="d-flex align-items-center">
                <div class="rms-logo me-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-cup-hot-fill text-white"></i>
                </div>
                <div>
                    <h1 class="h6 mb-0 fw-bold rms-navy">RMS Bronze Cafe</h1>
                    <small class="text-muted rms-small"><i class="bi bi-geo-alt-fill text-primary"></i> Table {{ $tableNumber }} detected</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-icon btn-light rounded-circle rms-border text-muted d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Help" onclick="scrollToHelp()">
                    <i class="bi bi-question-circle"></i>
                </button>
                <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2 position-relative shadow-sm px-3" style="height: 38px;" onclick="scrollToCart()">
                    <i class="bi bi-cart3"></i>
                    <span class="badge bg-white text-primary rounded-pill" id="headerCartCount">0</span>
                </button>
            </div>
        </div>
    </nav>

    <main class="container-fluid main-container pb-5 mb-5 fade-in">
        <section class="hero-section mt-3 p-3 rounded-4 shadow-sm text-center position-relative overflow-hidden">
            <div class="hero-bg"></div>
            <div class="position-relative z-1">
                <span class="badge bg-white text-primary mb-2 shadow-sm px-3 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;"><i class="bi bi-check-circle-fill me-1"></i> No app required</span>
                <h2 class="fw-bold mb-1 rms-navy fs-5">Order Food from Your Table</h2>
                <p class="text-muted mb-3 small px-2" style="font-size: 0.8rem;">Scan, choose, customize, and place your restaurant order directly from your phone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-primary btn-sm px-3 py-2 shadow-sm rounded-pill fw-medium" onclick="scrollToMenu()">Start Order</button>
                    <button class="btn bg-white text-primary btn-sm border-primary px-3 py-2 shadow-sm rounded-pill fw-medium" onclick="scrollToMenu()">View Menu</button>
                </div>
            </div>
        </section>

        <section class="mt-3 fade-in delay-1">
            <h3 class="h6 fw-bold rms-navy mb-2">How would you like to order?</h3>
            <div class="row g-2">
                <div class="col-6">
                    <div class="card h-100 order-type-card active border-primary shadow-sm rounded-4" onclick="selectOrderType(this, 'dine-in')">
                        <div class="card-body text-center p-2 py-3">
                            <i class="bi bi-shop fs-3 text-primary mb-1 d-block"></i>
                            <h4 class="h6 fw-bold mb-1">Dine In</h4>
                            <p class="text-muted mb-2" style="font-size: 0.7rem;">Order to your table and track food.</p>
                            <button class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-medium" style="font-size: 0.75rem;">Order for Table</button>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card h-100 order-type-card rms-border shadow-sm rounded-4" onclick="selectOrderType(this, 'takeaway')">
                        <div class="card-body text-center p-2 py-3">
                            <i class="bi bi-bag fs-3 text-muted mb-1 d-block"></i>
                            <h4 class="h6 fw-bold text-dark mb-1">Takeaway</h4>
                            <p class="text-muted mb-2" style="font-size: 0.7rem;">Place your order and collect it.</p>
                            <button class="btn btn-sm btn-outline-secondary w-100 rounded-pill fw-medium" style="font-size: 0.75rem;">Order for Takeaway</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-3 fade-in delay-2" id="tableConfirmationSection">
            <div class="card shadow-sm rms-border border-0 rounded-4">
                <div class="card-body p-3">
                    <span class="badge bg-primary-soft text-primary mb-2 px-2 py-1 rounded-pill">Your Table</span>
                    <h3 class="h6 fw-bold rms-navy mb-1">Table {{ $tableNumber }} detected</h3>
                    <p class="text-muted small mb-2" style="font-size: 0.8rem;">Your order will be sent directly to the restaurant with this table number.</p>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group input-group-sm shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-hash"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0 bg-light fs-6" id="tableNumber" value="{{ $tableNumber }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <button class="btn btn-primary btn-sm w-100 rounded-3 py-2 fw-medium shadow-sm" onclick="confirmTable()">Continue</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-4 pt-2" id="menuSection">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <h3 class="h5 fw-bold rms-navy mb-1">Browse Menu</h3>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Select your favorite items and review.</p>
                </div>
            </div>

            <div class="position-relative mb-3 shadow-sm rounded-pill overflow-hidden border rms-border bg-white">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted ms-3"></i>
                <input type="text" class="form-control border-0 ps-5 bg-transparent fs-6 py-2" id="menuSearch" placeholder="Search dishes, drinks, or deals" onkeyup="filterMenu()">
            </div>

            <div class="category-wrapper sticky-top bg-light pb-2 pt-2 z-2" style="top: 55px;">
                <div class="d-flex gap-2 overflow-auto hide-scrollbar pb-1 px-1" id="categoryContainer">
                    <button class="btn category-chip active rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'all')">All</button>
                    <button class="btn category-chip rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'popular')">Popular</button>
                    <button class="btn category-chip rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'burgers')">Burgers</button>
                    <button class="btn category-chip rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'pizza')">Pizza</button>
                    <button class="btn category-chip rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'drinks')">Drinks</button>
                    <button class="btn category-chip rounded-pill px-3 py-1 shadow-sm" onclick="filterCategory(this, 'desserts')">Desserts</button>
                </div>
            </div>

            <div class="row g-2 mt-1" id="menuItemsContainer"></div>

            <div id="noResultsMsg" class="text-center p-5 d-none">
                <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold rms-navy">No items found.</h5>
                <p class="text-muted small">Try another search or category.</p>
            </div>
        </section>

        <section class="mt-4 pt-2" id="cartSection">
            <h3 class="h5 fw-bold rms-navy mb-2">Review Your Order</h3>

            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                <div class="card-body p-0">
                    <div id="emptyCartMsg" class="text-center p-4">
                        <div class="bg-primary-soft rounded-circle d-inline-flex p-3 mb-2">
                            <i class="bi bi-cart-x fs-2 text-primary"></i>
                        </div>
                        <h6 class="fw-bold rms-navy mb-1">Your cart is empty</h6>
                        <p class="text-muted small mb-3" style="font-size: 0.8rem;">Add items from the menu to start your order.</p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-medium" onclick="scrollToMenu()">Browse Menu</button>
                    </div>

                    <div id="cartContent" class="d-none">
                        <div class="p-3 border-bottom" id="cartItemsList"></div>

                        <div class="p-3 border-bottom bg-light">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-pencil-square me-1"></i> Order Note (Optional)</label>
                            <input type="text" class="form-control border-0 shadow-sm rounded-3 py-2 text-sm" id="orderNote" placeholder="Any special instructions for the kitchen?" style="font-size: 0.85rem;">
                        </div>

                        <div class="p-3 border-bottom">
                            <h6 class="fw-bold mb-2 fs-6">Payment Method</h6>
                            <div class="d-flex flex-column gap-2">
                                <label class="payment-method-option rounded-3 border p-2 d-flex align-items-center cursor-pointer active shadow-sm bg-white">
                                    <input type="radio" name="paymentMethod" class="form-check-input mt-0 me-2" checked>
                                    <i class="bi bi-cash-coin fs-6 me-2 text-primary"></i>
                                    <span class="flex-grow-1 small fw-bold">Pay at Counter</span>
                                </label>
                                <label class="payment-method-option rounded-3 border p-2 d-flex align-items-center cursor-pointer shadow-sm bg-white">
                                    <input type="radio" name="paymentMethod" class="form-check-input mt-0 me-2">
                                    <i class="bi bi-cash fs-6 me-2 text-primary"></i>
                                    <span class="flex-grow-1 small fw-bold">Cash</span>
                                </label>
                                <label class="payment-method-option rounded-3 border p-2 d-flex align-items-center cursor-pointer shadow-sm bg-white">
                                    <input type="radio" name="paymentMethod" class="form-check-input mt-0 me-2">
                                    <i class="bi bi-credit-card fs-6 me-2 text-primary"></i>
                                    <span class="flex-grow-1 small fw-bold">Card</span>
                                </label>
                                <label class="payment-method-option rounded-3 border p-2 d-flex align-items-center cursor-pointer shadow-sm bg-white">
                                    <input type="radio" name="paymentMethod" class="form-check-input mt-0 me-2">
                                    <i class="bi bi-phone fs-6 me-2 text-primary"></i>
                                    <span class="flex-grow-1 small fw-bold">Online Later</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-3 bg-white">
                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Subtotal</span>
                                <span id="cartSubtotal" class="fw-medium text-dark">Rs. 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Service Fee (5%)</span>
                                <span id="cartTax" class="fw-medium text-dark">Rs. 0</span>
                            </div>
                            <div class="border-top pt-2 d-flex justify-content-between mb-3 fw-bold fs-5 rms-navy">
                                <span>Total</span>
                                <span id="cartTotal">Rs. 0</span>
                            </div>

                            <button class="btn btn-primary w-100 py-2 rounded-3 fw-bold fs-6 shadow-sm d-flex justify-content-center align-items-center gap-2 transition-all" onclick="placeOrder()" id="placeOrderBtn">
                                Place Order <i class="bi bi-arrow-right-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="orderSuccessMsg" class="card shadow-sm border-0 rounded-4 border-start border-success border-4 d-none">
                <div class="card-body p-3 text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-2"></i>
                    </div>
                    <h6 class="fw-bold rms-navy mb-1">Order Placed Successfully!</h6>
                    <p class="text-muted small mb-0 fw-medium" id="orderSuccessNumberPlaceholder">Order #RMS-8832</p>
                </div>
            </div>
        </section>

        <section class="mt-4 pt-2 d-none fade-in" id="orderTrackingSection">
            <h3 class="h5 fw-bold rms-navy mb-3">Track Your Order</h3>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-3">
                    <div class="timeline position-relative ps-2">
                        <div class="timeline-line position-absolute h-100 start-0 ms-3" style="width: 2px; background: var(--rms-border); top: 10px; z-index: 0;"></div>
                        <div class="timeline-item position-relative ps-5 pb-4">
                            <div class="timeline-dot position-absolute start-0 translate-middle-x rounded-circle shadow-sm" style="width: 18px; height: 18px; background: var(--status-received); border: 3px solid white; z-index: 1;"></div>
                            <h6 class="fw-bold mb-1" style="color: var(--status-received);">Received</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Your order has been received.</p>
                            <small class="text-muted fw-medium" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>Just now</small>
                        </div>
                        <div class="timeline-item position-relative ps-5 pb-4 opacity-50">
                            <div class="timeline-dot position-absolute start-0 translate-middle-x rounded-circle bg-white border border-2" style="width: 18px; height: 18px; border-color: var(--rms-border)!important; z-index: 1;"></div>
                            <h6 class="fw-bold mb-1 text-dark">Preparing</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">The kitchen is preparing your food.</p>
                        </div>
                        <div class="timeline-item position-relative ps-5 pb-4 opacity-50">
                            <div class="timeline-dot position-absolute start-0 translate-middle-x rounded-circle bg-white border border-2" style="width: 18px; height: 18px; border-color: var(--rms-border)!important; z-index: 1;"></div>
                            <h6 class="fw-bold mb-1 text-dark">Ready</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Your order is ready to be served.</p>
                        </div>
                        <div class="timeline-item position-relative ps-5 opacity-50">
                            <div class="timeline-dot position-absolute start-0 translate-middle-x rounded-circle bg-white border border-2" style="width: 18px; height: 18px; border-color: var(--rms-border)!important; z-index: 1;"></div>
                            <h6 class="fw-bold mb-1 text-dark">Served</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Enjoy your meal!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-4 pt-2" id="helpSection">
            <h3 class="h5 fw-bold rms-navy mb-2">Need Help?</h3>
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center h-100 bg-white" onclick="showToast('Waiter called to Table ' + getTableLabel())">
                        <i class="bi bi-person-raised-hand fs-4 mb-1"></i>
                        <span class="small fw-bold" style="font-size: 0.8rem;">Call Waiter</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center h-100 bg-white" onclick="showToast('Bill requested for Table ' + getTableLabel())">
                        <i class="bi bi-receipt fs-4 mb-1"></i>
                        <span class="small fw-bold" style="font-size: 0.8rem;">Request Bill</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center h-100 bg-white" onclick="showToast('Water requested for Table ' + getTableLabel())">
                        <i class="bi bi-droplet fs-4 mb-1"></i>
                        <span class="small fw-bold" style="font-size: 0.8rem;">Ask for Water</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center h-100 bg-white" onclick="showToast('Assistance requested')">
                        <i class="bi bi-info-circle fs-4 mb-1"></i>
                        <span class="small fw-bold" style="font-size: 0.8rem;">Assistance</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="mt-4 pt-2 pb-3">
            <div class="card bg-primary-soft border-0 rounded-4 shadow-sm">
                <div class="card-body p-3 text-center">
                    <p class="text-primary small mb-3 fw-medium px-2" style="font-size: 0.75rem;">No app required. Your order is sent directly to the POS.</p>
                    <div class="row g-2 text-center">
                        <div class="col-3">
                            <div class="bg-white rounded-circle d-inline-flex p-2 shadow-sm mb-1 text-primary"><i class="bi bi-phone"></i></div>
                            <div class="small fw-medium text-dark" style="font-size: 0.65rem;">No App</div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded-circle d-inline-flex p-2 shadow-sm mb-1 text-primary"><i class="bi bi-diagram-3"></i></div>
                            <div class="small fw-medium text-dark" style="font-size: 0.65rem;">Direct</div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded-circle d-inline-flex p-2 shadow-sm mb-1 text-primary"><i class="bi bi-search"></i></div>
                            <div class="small fw-medium text-dark" style="font-size: 0.65rem;">Review</div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded-circle d-inline-flex p-2 shadow-sm mb-1 text-primary"><i class="bi bi-shield-check"></i></div>
                            <div class="small fw-medium text-dark" style="font-size: 0.65rem;">Secure</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white border-top py-3 pb-4 text-center text-md-start mt-auto z-1 position-relative">
        <div class="container-fluid main-container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px;">
                        <i class="bi bi-cup-hot-fill" style="font-size: 0.7rem;"></i>
                    </div>
                    <div class="text-start">
                        <h6 class="fw-bold rms-navy mb-0" style="font-size: 0.9rem;">RMS Bronze Cafe</h6>
                    </div>
                </div>
                <div class="d-flex gap-3 small fw-medium" style="font-size: 0.75rem;">
                    <a href="#" class="text-muted text-decoration-none">Help</a>
                    <a href="#" class="text-muted text-decoration-none">Privacy</a>
                    <a href="#" class="text-muted text-decoration-none">Terms</a>
                </div>
            </div>
            <div class="text-center mt-3 border-top pt-2">
                <p class="text-muted mb-1" style="font-size: 0.7rem;">No app required. Your order is sent directly to the restaurant.</p>
                <p class="text-muted mb-1" style="font-size: 0.7rem;">For order issues, please contact restaurant staff.</p>
                <p class="text-muted mb-0 fw-bold" style="font-size: 0.7rem;">&copy; 2026 RMS Bronze.</p>
            </div>
        </div>
    </footer>

    <div class="position-fixed bottom-0 start-0 w-100 z-3 sticky-cart-bar slide-up d-none" id="stickyCartBar">
        <div class="container-fluid main-container p-0">
            <div class="bg-primary text-white shadow-lg p-2 px-3 d-flex justify-content-between align-items-center cursor-pointer m-2 rounded-pill" onclick="scrollToCart()">
                <div class="d-flex align-items-center gap-2 ps-1">
                    <div class="position-relative bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                        <i class="bi bi-cart3"></i>
                    </div>
                    <div class="fw-bold ms-1" style="font-size: 0.95rem;" id="stickyCartText">0 items - Rs. 0</div>
                </div>
                <button class="btn btn-primary text-white border-0 btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-sm" style="background-color: var(--rms-primary-hover);">
                    View Cart
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down custom-modal">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="position-relative">
                    <img src="" id="modalImg" class="w-100 object-fit-cover rounded-top-4" style="height: 200px;" alt="Food Image">
                    <button type="button" class="btn btn-light rounded-circle position-absolute top-0 end-0 m-2 shadow-sm btn-close-custom d-flex align-items-center justify-content-center border-0" data-bs-dismiss="modal" style="width: 32px; height: 32px; padding: 0;">
                        <i class="bi bi-x-lg fs-6 text-dark"></i>
                    </button>
                </div>
                <div class="modal-body p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h4 class="fw-bold rms-navy mb-0 pe-2 fs-5" id="modalTitle">Item Name</h4>
                        <span class="fw-bold fs-6 text-primary text-nowrap" id="modalPrice">Rs. 0</span>
                    </div>
                    <p class="text-muted small mb-3" id="modalDesc" style="line-height: 1.4; font-size: 0.8rem;">Description goes here.</p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <h6 class="fw-bold rms-navy mb-0" style="font-size: 0.9rem;">Size</h6>
                            <span class="badge bg-light text-muted fw-medium border">Required</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer radio-card transition-all">
                                <input type="radio" name="itemSize" class="form-check-input mt-0 me-3 shadow-sm" value="0" checked onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Regular</span>
                            </label>
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer radio-card transition-all">
                                <input type="radio" name="itemSize" class="form-check-input mt-0 me-3 shadow-sm" value="150" onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Large</span>
                                <span class="text-muted small fw-medium">+ Rs. 150</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <h6 class="fw-bold rms-navy mb-0" style="font-size: 0.9rem;">Add-ons</h6>
                            <span class="badge bg-light text-muted fw-medium border">Optional</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer checkbox-card transition-all">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm addon-checkbox" value="50" onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Extra Cheese</span>
                                <span class="text-muted small fw-medium">+ Rs. 50</span>
                            </label>
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer checkbox-card transition-all">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm addon-checkbox" value="30" onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Extra Sauce</span>
                                <span class="text-muted small fw-medium">+ Rs. 30</span>
                            </label>
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer checkbox-card transition-all">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm addon-checkbox" value="120" onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Fries</span>
                                <span class="text-muted small fw-medium">+ Rs. 120</span>
                            </label>
                            <label class="border rounded-3 p-2 d-flex align-items-center cursor-pointer checkbox-card transition-all">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm addon-checkbox" value="100" onchange="updateModalPrice()">
                                <span class="flex-grow-1 small fw-medium text-dark">Drink</span>
                                <span class="text-muted small fw-medium">+ Rs. 100</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold rms-navy mb-2" style="font-size: 0.9rem;">Spice Level</h6>
                        <div class="d-flex gap-1 bg-light p-1 rounded-pill border">
                            <input type="radio" class="btn-check" name="spiceLvl" id="spiceMild" autocomplete="off" checked>
                            <label class="btn btn-outline-secondary border-0 rounded-pill flex-grow-1 btn-sm fw-medium py-1 spice-btn" for="spiceMild">Mild</label>
                            <input type="radio" class="btn-check" name="spiceLvl" id="spiceMed" autocomplete="off">
                            <label class="btn btn-outline-secondary border-0 rounded-pill flex-grow-1 btn-sm fw-medium py-1 spice-btn" for="spiceMed">Medium</label>
                            <input type="radio" class="btn-check" name="spiceLvl" id="spiceHot" autocomplete="off">
                            <label class="btn btn-outline-secondary border-0 rounded-pill flex-grow-1 btn-sm fw-medium py-1 spice-btn" for="spiceHot">Hot</label>
                        </div>
                    </div>

                    <div class="mb-1">
                        <h6 class="fw-bold rms-navy mb-2" style="font-size: 0.9rem;">Special Instructions</h6>
                        <textarea class="form-control bg-light border-0 shadow-sm rounded-3 py-2 px-3 text-sm" id="itemNote" rows="2" placeholder="Add cooking instructions or remove ingredients..." style="font-size: 0.85rem;"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top shadow-lg p-2 rounded-bottom-4 sticky-bottom bg-white m-0 row g-2">
                    <div class="col-4">
                        <div class="d-flex align-items-center border rounded-pill shadow-sm bg-light overflow-hidden h-100">
                            <button class="btn btn-light border-0 py-1 px-2 text-primary bg-transparent flex-fill" onclick="updateQty(-1)"><i class="bi bi-dash-lg"></i></button>
                            <span class="fw-bold px-1 text-center flex-fill rms-navy fs-6" id="modalQty">1</span>
                            <button class="btn btn-light border-0 py-1 px-2 text-primary bg-transparent flex-fill" onclick="updateQty(1)"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                    <div class="col-8">
                        <button class="btn btn-primary w-100 h-100 py-2 rounded-pill shadow-sm fw-bold d-flex justify-content-between align-items-center px-3 transition-all" onclick="addToCart()">
                            <span>Add to Cart</span>
                            <span id="modalFinalPrice" class="bg-white text-primary px-2 py-1 rounded-pill small" style="font-size: 0.75rem;">Rs. 0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3 mt-4 z-index-toast">
        <div id="liveToast" class="toast align-items-center text-white bg-dark border-0 rounded-pill shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex px-2 py-1">
                <div class="toast-body d-flex align-items-center gap-2 fw-medium small" id="toastMessage">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i> Notification
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const initialTableNumber = @json((string) $tableNumber);
        const qrOrderStoreUrl = @json(route('customer.orders.store'));
        const backendMenuData = @json($menuItems ?? []);
        const fallbackMenuData = [
            { id: 1, name: "Crispy Chicken Burger", desc: "Crispy fried chicken breast, lettuce, mayo, brioche bun.", price: 450, category: "burgers", tag: "Popular", prepTime: "10-15 min", customizable: true, available: true, img: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80" },
            { id: 2, name: "Margherita Pizza", desc: "Classic cheese pizza with rich tomato sauce and fresh basil.", price: 850, category: "pizza", tag: "Veg", prepTime: "15-20 min", customizable: true, available: true, img: "https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=500&q=80" },
            { id: 3, name: "Loaded Fries", desc: "Crispy fries topped with melted cheese, jalapenos, and special sauce.", price: 350, category: "popular", tag: "Spicy", prepTime: "5-10 min", customizable: false, available: true, img: "https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=500&q=80" },
            { id: 4, name: "Grilled Chicken", desc: "Healthy grilled chicken breast served with steamed veggies.", price: 650, category: "popular", tag: "Recommended", prepTime: "15-20 min", customizable: true, available: true, img: "https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?auto=format&fit=crop&w=500&q=80" },
            { id: 5, name: "Pasta Alfredo", desc: "Creamy fettuccine pasta with grilled chicken strips and parmesan.", price: 700, category: "popular", tag: "New", prepTime: "12-18 min", customizable: true, available: true, img: "https://images.unsplash.com/photo-1645112411341-6c4fd023714a?auto=format&fit=crop&w=500&q=80" },
            { id: 6, name: "Cold Coffee", desc: "Rich espresso blended with milk and vanilla ice cream.", price: 250, category: "drinks", tag: "Popular", prepTime: "5 min", customizable: true, available: true, img: "https://images.unsplash.com/photo-1461023058943-0708ce151c6e?auto=format&fit=crop&w=500&q=80" },
            { id: 7, name: "Chocolate Brownie", desc: "Warm gooey chocolate brownie served with vanilla syrup.", price: 200, category: "desserts", tag: "Veg", prepTime: "5 min", customizable: false, available: false, img: "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=500&q=80" },
            { id: 8, name: "Fresh Lemonade", desc: "Chilled classic lemonade with mint leaves and ice.", price: 150, category: "drinks", tag: "", prepTime: "5 min", customizable: false, available: true, img: "https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=500&q=80" }
        ];
        const menuData = backendMenuData.length > 0 ? backendMenuData : fallbackMenuData;

        let cart = [];
        let currentItem = null;
        let currentModalQty = 1;
        let itemModal;
        let selectedOrderType = 'dine-in';

        document.addEventListener("DOMContentLoaded", () => {
            renderMenu(menuData);
        });

        function getTableLabel() {
            return document.getElementById("tableNumber").value || initialTableNumber;
        }

        function money(amount) {
            return Number(amount).toLocaleString();
        }

        function renderMenu(data) {
            const container = document.getElementById("menuItemsContainer");
            const noResults = document.getElementById("noResultsMsg");
            container.innerHTML = "";

            if (data.length === 0) {
                noResults.classList.remove("d-none");
                return;
            }

            noResults.classList.add("d-none");

            data.forEach(item => {
                const col = document.createElement("div");
                col.className = "col-12 col-md-6 col-lg-4 mb-2";

                const badgeHtml = item.tag ? `<div class="menu-badge text-primary"><i class="bi bi-star-fill text-warning me-1"></i> ${item.tag}</div>` : "";
                const custBadge = item.customizable ? `<span class="customizable-badge ms-2">Customizable</span>` : "";
                const unavailableClass = item.available ? "" : "unavailable";
                const btnText = item.available ? `<i class="bi bi-plus-lg me-1"></i> Add` : `Currently Unavailable`;

                col.innerHTML = `
                    <div class="menu-card shadow-sm border-0 ${unavailableClass}">
                        <div class="menu-card-img-wrapper">
                            ${badgeHtml}
                            <img src="${item.img}" class="menu-card-img" alt="${item.name}" loading="lazy">
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="fw-bold fs-6 rms-navy mb-0 pe-2 d-flex align-items-center flex-wrap gap-1">${item.name} ${custBadge}</h5>
                                <span class="fw-bold text-primary">Rs. ${money(item.price)}</span>
                            </div>
                            <div class="prep-time mb-2"><i class="bi bi-clock me-1"></i>${item.prepTime}</div>
                            <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.8rem; line-height: 1.4;">${item.desc}</p>
                            <button class="btn btn-primary btn-sm w-100 rounded-pill fw-bold shadow-sm py-2" onclick="${item.available ? `openItemModal(${item.id})` : ''}">${btnText}</button>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        }

        function filterCategory(btn, cat) {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById("menuSearch").value = "";
            renderMenu(cat === 'all' ? menuData : menuData.filter(item => item.category === cat || (cat === 'popular' && item.tag === 'Popular')));
        }

        function filterMenu() {
            const query = document.getElementById("menuSearch").value.toLowerCase();
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            document.querySelector('.category-chip').classList.add('active');

            renderMenu(menuData.filter(item =>
                item.name.toLowerCase().includes(query) ||
                item.desc.toLowerCase().includes(query)
            ));
        }

        function openItemModal(id) {
            if (!itemModal) {
                itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
            }

            currentItem = menuData.find(item => item.id === id);
            if (!currentItem) return;

            currentModalQty = 1;
            document.getElementById("modalImg").src = currentItem.img;
            document.getElementById("modalTitle").innerText = currentItem.name;
            document.getElementById("modalDesc").innerText = currentItem.desc;
            document.getElementById("modalPrice").innerText = "Rs. " + money(currentItem.price);
            document.getElementById("modalQty").innerText = currentModalQty;
            document.querySelector('input[name="itemSize"][value="0"]').checked = true;
            document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
            document.getElementById("spiceMild").checked = true;
            document.getElementById("itemNote").value = "";

            updateModalPrice();
            itemModal.show();
        }

        function updateQty(change) {
            const newQty = currentModalQty + change;
            if (newQty >= 1 && newQty <= 20) {
                currentModalQty = newQty;
                document.getElementById("modalQty").innerText = currentModalQty;
                updateModalPrice();
            }
        }

        function updateModalPrice() {
            let basePrice = currentItem.price;
            basePrice += parseInt(document.querySelector('input[name="itemSize"]:checked').value);

            document.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                basePrice += parseInt(cb.value);
            });

            document.getElementById("modalFinalPrice").innerText = "Rs. " + money(basePrice * currentModalQty);
            return basePrice;
        }

        function addToCart() {
            const unitPrice = updateModalPrice();
            const total = unitPrice * currentModalQty;
            const sizeText = document.querySelector('input[name="itemSize"]:checked').nextElementSibling.innerText;
            const addons = [];

            document.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                addons.push(cb.nextElementSibling.innerText);
            });

            const spice = document.querySelector('input[name="spiceLvl"]:checked').nextElementSibling.innerText;
            const note = document.getElementById("itemNote").value.trim();

            cart.push({
                id: Date.now(),
                itemId: currentItem.id,
                name: currentItem.name,
                qty: currentModalQty,
                unitPrice,
                total,
                details: `${sizeText} | Spice: ${spice}` + (addons.length > 0 ? ` | Add-ons: ${addons.join(', ')}` : "") + (note ? ` | Note: ${note}` : "")
            });

            itemModal.hide();
            updateCartUI();
            showToast(`Added ${currentModalQty}x ${currentItem.name} to cart`);
        }

        function removeFromCart(cartId) {
            cart = cart.filter(item => item.id !== cartId);
            updateCartUI();
        }

        function updateCartItemQty(cartId, change) {
            const item = cart.find(i => i.id === cartId);
            if (!item) return;

            const newQty = item.qty + change;
            if (newQty < 1) {
                removeFromCart(cartId);
            } else {
                item.qty = newQty;
                item.total = item.unitPrice * item.qty;
                updateCartUI();
            }
        }

        function updateCartUI() {
            const cartCount = cart.reduce((sum, item) => sum + item.qty, 0);
            const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            const tax = Math.round(subtotal * 0.05);
            const grandTotal = subtotal + tax;

            document.getElementById("headerCartCount").innerText = cartCount;

            const stickyBar = document.getElementById("stickyCartBar");
            if (cartCount > 0) {
                stickyBar.classList.remove("d-none");
                document.body.classList.add("has-cart");
                document.getElementById("stickyCartText").innerText = `${cartCount} item${cartCount > 1 ? 's' : ''} - Rs. ${money(grandTotal)}`;
            } else {
                stickyBar.classList.add("d-none");
                document.body.classList.remove("has-cart");
            }

            const emptyMsg = document.getElementById("emptyCartMsg");
            const cartContent = document.getElementById("cartContent");
            const cartList = document.getElementById("cartItemsList");

            if (cartCount === 0) {
                emptyMsg.classList.remove("d-none");
                cartContent.classList.add("d-none");
                return;
            }

            emptyMsg.classList.add("d-none");
            cartContent.classList.remove("d-none");
            cartList.innerHTML = "";

            cart.forEach(item => {
                cartList.innerHTML += `
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex flex-column me-3">
                            <div class="fw-bold rms-navy mb-1">${item.name}</div>
                            <div class="small text-muted mb-2" style="font-size: 0.75rem;">${item.details}</div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="border rounded-pill d-flex align-items-center overflow-hidden">
                                    <button class="btn btn-light bg-transparent border-0 cart-qty-btn text-primary" onclick="updateCartItemQty(${item.id}, -1)"><i class="bi bi-dash"></i></button>
                                    <span class="fw-bold small px-2">${item.qty}</span>
                                    <button class="btn btn-light bg-transparent border-0 cart-qty-btn text-primary" onclick="updateCartItemQty(${item.id}, 1)"><i class="bi bi-plus"></i></button>
                                </div>
                                <button class="btn btn-link text-danger p-0 small text-decoration-none fw-medium ms-2" style="font-size: 0.75rem;" onclick="removeFromCart(${item.id})">Remove</button>
                            </div>
                        </div>
                        <div class="fw-bold text-dark">Rs. ${money(item.total)}</div>
                    </div>
                `;
            });

            document.getElementById("cartSubtotal").innerText = `Rs. ${money(subtotal)}`;
            document.getElementById("cartTax").innerText = `Rs. ${money(tax)}`;
            document.getElementById("cartTotal").innerText = `Rs. ${money(grandTotal)}`;
        }

        function selectOrderType(element, type) {
            selectedOrderType = type;

            document.querySelectorAll('.order-type-card').forEach(el => {
                el.classList.remove('active', 'border-primary');
                el.classList.add('rms-border');
                el.querySelector('button').classList.replace('btn-outline-primary', 'btn-outline-secondary');
                el.querySelector('i').classList.replace('text-primary', 'text-muted');
                el.querySelector('h4').classList.replace('text-primary', 'text-dark');
            });

            element.classList.add('active');
            element.classList.remove('rms-border');
            element.classList.add('border-primary');
            element.querySelector('button').classList.replace('btn-outline-secondary', 'btn-outline-primary');
            element.querySelector('i').classList.replace('text-muted', 'text-primary');
            element.querySelector('h4').classList.replace('text-dark', 'text-primary');

            const tableSection = document.getElementById("tableConfirmationSection");
            if (type === 'takeaway') {
                tableSection.classList.add('d-none');
                document.getElementById("tableNumber").value = "Takeaway";
                document.getElementById("tableNumber").disabled = true;
            } else {
                tableSection.classList.remove('d-none');
                document.getElementById("tableNumber").value = initialTableNumber;
                document.getElementById("tableNumber").disabled = false;
            }
        }

        function confirmTable() {
            const btn = document.querySelector("#tableConfirmationSection button");
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Confirming...';

            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-check2"></i> Confirmed';
                btn.classList.replace('btn-primary', 'btn-success');
                showToast("Table confirmed. You can browse the menu.");
                setTimeout(() => {
                    scrollToMenu();
                    btn.innerHTML = originalText;
                    btn.classList.replace('btn-success', 'btn-primary');
                }, 1000);
            }, 800);
        }

        function scrollToMenu() {
            scrollWithHeader("menuSection");
        }

        function scrollToCart() {
            scrollWithHeader("cartSection");
        }

        function scrollToHelp() {
            scrollWithHeader("helpSection");
        }

        function scrollWithHeader(id) {
            const headerOffset = 65;
            const elementPosition = document.getElementById(id).getBoundingClientRect().top;
            window.scrollTo({ top: elementPosition + window.pageYOffset - headerOffset, behavior: "smooth" });
        }

        async function placeOrder() {
            if (cart.length === 0) {
                showToast("Your cart is empty.");
                return;
            }

            const btn = document.getElementById("placeOrderBtn");
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
            btn.disabled = true;

            try {
                const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')
                    ?.closest('label')
                    ?.querySelector('span.flex-grow-1')
                    ?.innerText || 'Pay at Counter';

                const response = await fetch(qrOrderStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        order_type: selectedOrderType,
                        table: getTableLabel(),
                        payment_method: paymentMethod,
                        note: document.getElementById('orderNote').value,
                        items: cart
                    })
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Order could not be placed.');
                }

                const orderNumber = data.order?.number || `RMS-${Math.floor(1000 + Math.random() * 9000)}`;
                cart = [];
                updateCartUI();

                document.getElementById("emptyCartMsg").classList.add("d-none");
                document.getElementById("cartContent").classList.add("d-none");
                document.getElementById("orderSuccessMsg").classList.remove("d-none");
                document.getElementById("orderTrackingSection").classList.remove("d-none");
                document.getElementById("orderSuccessNumberPlaceholder").innerText = `Order #${orderNumber}`;
                document.getElementById("stickyCartBar").classList.add("d-none");
                document.body.classList.remove("has-cart");

                btn.innerHTML = originalText;
                btn.disabled = false;

                setTimeout(() => scrollWithHeader("orderTrackingSection"), 100);
                showToast("Order placed successfully!");
            } catch (error) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast(error.message || "Order could not be placed.");
            }
        }

        function showToast(message) {
            const toastEl = document.getElementById('liveToast');
            document.getElementById('toastMessage').innerHTML = `<i class="bi bi-check-circle-fill text-success fs-5"></i> ${message}`;
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
        }
    </script>
</body>
</html>
