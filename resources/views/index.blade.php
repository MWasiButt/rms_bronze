@extends('partials.layouts.master')

@section('title', 'Dashboard | RMS Bronze')
@section('sub-title', 'Operations Dashboard')
@section('pagetitle', 'RMS Bronze')
@section('buttonTitle', 'Open POS')
@section('link', 'index')

@section('css')
    <style>
        /* ─── DESIGN TOKENS ─────────────────────────────────────────── */
        :root {
            --p: var(--bs-primary);
            --p-rgb: var(--bs-primary-rgb);
            --surface: #ffffff;
            --surf-2: #f7f9fc;
            --surf-3: #eef3fb;
            --border: rgba(var(--p-rgb), .10);
            --border-2: rgba(var(--p-rgb), .06);
            --text-1: #111827;
            --text-2: #374151;
            --text-3: #6b7a96;
            --text-4: #9ca3b0;
            --rms-accent: #2563eb;
            --rms-accent-rgb: 37, 99, 235;
            --rms-warm: #64748b;
            --rms-hero: #f5f8ff;
            --shadow-xs: 0 1px 3px rgba(15, 23, 42, .06);
            --shadow-sm: 0 4px 12px rgba(15, 23, 42, .08);
            --shadow-md: 0 8px 28px rgba(15, 23, 42, .10);
            --shadow-lg: 0 18px 42px rgba(15, 23, 42, .10);
            --radius-sm: 12px;
            --radius: 18px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --speed: .22s;
        }

        /* ─── ANIMATIONS ────────────────────────────────────────────── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(.75);
            }
        }

        @keyframes spin-slow {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── PAGE SHELL ────────────────────────────────────────────── */
        .rms-dash {
            padding: 1.5rem 0 2rem;
            animation: fadeIn .4s ease both;
        }

        /* ─── HERO BANNER ───────────────────────────────────────────── */
        .hero-wrap {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba(var(--p-rgb), .045), transparent 38%),
                #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            padding: 2.15rem 2.35rem;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .10);
            animation: fadeUp .45s ease both;
        }

        .hero-wrap::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(180deg, #1d4ed8, #60a5fa);
            pointer-events: none;
        }

        .hero-wrap::after {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            top: -190px;
            right: -150px;
            background: radial-gradient(circle, rgba(var(--p-rgb), .10) 0%, rgba(var(--p-rgb), .04) 40%, transparent 68%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            padding: .44rem .95rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--p);
            margin-bottom: 1.1rem;
        }

        .hero-pill .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #60a5fa;
            animation: pulse-dot 1.6s ease infinite;
        }

        .hero-title {
            font-size: clamp(1.75rem, 2.6vw, 2.9rem);
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.08;
            color: #0f172a;
            margin-bottom: .75rem;
        }

        .hero-sub {
            color: #64748b;
            font-size: .97rem;
            max-width: 600px;
            line-height: 1.62;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-top: 1.6rem;
        }

        .hero-actions .btn {
            border-radius: 999px;
            padding: .56rem 1.15rem;
            font-size: .875rem;
            font-weight: 600;
            transition: transform var(--speed), box-shadow var(--speed), background var(--speed);
        }

        .hero-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
        }

        .btn-ghost-white {
            background: #fff;
            border: 1px solid #dbe4f0;
            color: #334155;
        }

        .btn-ghost-white:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .sync-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            background: rgba(100, 116, 139, .08);
            border: 1px solid rgba(100, 116, 139, .14);
            border-radius: 999px;
            padding: .42rem .85rem;
            color: #475569;
            font-size: .78rem;
        }

        .sync-chip i {
            font-size: .9rem;
            animation: spin-slow 3s linear infinite;
        }

        /* Hero Stats Grid */
        .hero-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .85rem;
            padding: 1rem;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, #f8fbff, #eef5ff);
            border: 1px solid #dbeafe;
        }

        .hstat {
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 14px;
            padding: 1rem 1.05rem;
            box-shadow: 0 8px 20px rgba(30, 64, 175, .06);
            transition: background var(--speed), transform var(--speed);
        }

        .hstat:hover {
            background: #fff;
            transform: translateY(-3px);
        }

        .hstat .lbl {
            display: block;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: .28rem;
        }

        .hstat .val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1d4ed8;
            line-height: 1.1;
        }

        .hstat .desc {
            font-size: .8rem;
            color: #64748b;
            margin-top: .2rem;
        }

        /* ─── SECTION STAGGER ───────────────────────────────────────── */
        .rms-row {
            animation: fadeUp .45s ease both;
        }

        .rms-row:nth-child(2) {
            animation-delay: .06s;
        }

        .rms-row:nth-child(3) {
            animation-delay: .12s;
        }

        .rms-row:nth-child(4) {
            animation-delay: .18s;
        }

        .rms-row:nth-child(5) {
            animation-delay: .24s;
        }

        /* ─── KPI CARDS ─────────────────────────────────────────────── */
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.35rem 1.4rem 1.25rem;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: transform var(--speed), box-shadow var(--speed);
            cursor: default;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(145deg, rgba(255, 255, 255, .6), transparent 55%);
            pointer-events: none;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .kpi-label {
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--text-3);
        }

        .kpi-value {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -.04em;
            color: var(--text-1);
            line-height: 1.1;
            margin: .3rem 0 .15rem;
        }

        .kpi-meta {
            font-size: .82rem;
            color: var(--text-3);
        }

        .kpi-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            border-radius: 999px;
            padding: .32rem .7rem;
            font-size: .74rem;
            font-weight: 700;
            margin-top: .9rem;
        }

        .kpi-badge.live {
            background: rgba(var(--p-rgb), .10);
            color: var(--p);
        }

        .kpi-badge i {
            font-size: .8rem;
        }

        /* ─── INSIGHT BAND ──────────────────────────────────────────── */
        .insight-band {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .insight-tile {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.2rem 1.3rem;
            box-shadow: var(--shadow-xs);
            transition: transform var(--speed), box-shadow var(--speed);
        }

        .insight-tile:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .kicker {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--p);
            margin-bottom: .35rem;
        }

        .insight-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin-bottom: .4rem;
        }

        .insight-copy {
            font-size: .84rem;
            color: var(--text-3);
            line-height: 1.55;
            margin: 0;
        }

        /* ─── STANDARD CARD ─────────────────────────────────────────── */
        .rms-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow var(--speed);
        }

        .rms-card:hover {
            box-shadow: var(--shadow-md);
        }

        .rms-card .rc-head {
            padding: 1.15rem 1.4rem .75rem;
            border-bottom: 1px solid var(--border-2);
        }

        .rms-card .rc-body {
            padding: 1.25rem 1.4rem 1.4rem;
        }

        /* ─── TABLES ────────────────────────────────────────────────── */
        .prem-table thead th {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--text-4);
            background: var(--surf-2);
            border-bottom: 0;
            padding: .85rem 1rem;
        }

        .prem-table tbody td {
            padding: .9rem 1rem;
            border-color: var(--border-2);
            vertical-align: middle;
            font-size: .875rem;
            color: var(--text-2);
            transition: background var(--speed);
        }

        .prem-table tbody tr:hover td {
            background: var(--surf-2);
        }

        .prem-table .order-id {
            font-weight: 700;
            color: var(--text-1);
            font-size: .88rem;
        }

        .prem-table .sub-text {
            font-size: .73rem;
            color: var(--text-4);
            margin-top: .12rem;
        }

        /* ─── STATUS BADGES ─────────────────────────────────────────── */
        .s-badge {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            border-radius: 999px;
            padding: .34rem .75rem;
            font-size: .74rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .s-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* ─── SURFACE ITEMS (Station Pulse, Staff) ──────────────────── */
        .surf-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .surf-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .95rem 1.05rem;
            background: var(--surf-2);
            border: 1px solid var(--border-2);
            border-radius: var(--radius);
            box-shadow: var(--shadow-xs);
            transition: transform var(--speed), box-shadow var(--speed);
        }

        .surf-item:hover {
            transform: translateX(3px);
            box-shadow: var(--shadow-sm);
        }

        .surf-item-title {
            font-size: .88rem;
            font-weight: 700;
            color: var(--text-1);
            margin-bottom: .18rem;
        }

        .surf-item-detail {
            font-size: .78rem;
            color: var(--text-3);
        }

        /* ─── PAYMENT PROGRESS ──────────────────────────────────────── */
        .pay-row {
            padding: .85rem 0;
            border-bottom: 1px solid var(--border-2);
        }

        .pay-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .pay-label {
            font-size: .86rem;
            font-weight: 700;
            color: var(--text-1);
        }

        .pay-amount {
            font-size: .86rem;
            color: var(--text-3);
        }

        .prem-progress {
            height: 9px;
            background: var(--surf-3);
            border-radius: 999px;
            overflow: hidden;
            margin-top: .55rem;
        }

        .prem-progress .bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--p), #5fa5ff);
            transition: width .8s cubic-bezier(.4, 0, .2, 1);
        }

        /* ─── TABLE CLUSTER (Front of House) ────────────────────────── */
        .table-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .8rem;
        }

        .table-tile {
            background: linear-gradient(160deg, var(--surface), var(--surf-2));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            min-height: 130px;
            transition: transform var(--speed), box-shadow var(--speed);
        }

        .table-tile:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .table-name {
            font-size: .95rem;
            font-weight: 800;
            color: var(--text-1);
        }

        .table-meta {
            font-size: .78rem;
            color: var(--text-3);
            margin-top: .18rem;
        }

        .table-detail {
            font-size: .78rem;
            color: var(--text-3);
        }

        /* ─── TEAM AVATAR ───────────────────────────────────────────── */
        .t-avatar {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .95rem;
            color: #fff;
            background: linear-gradient(135deg, var(--p), #73a9ff);
            box-shadow: 0 8px 20px rgba(var(--p-rgb), .25);
            flex-shrink: 0;
        }

        .staff-name {
            font-size: .88rem;
            font-weight: 700;
            color: var(--text-1);
        }

        .staff-meta {
            font-size: .76rem;
            color: var(--text-3);
        }

        /* ─── FEATURE FLAGS ─────────────────────────────────────────── */
        .flag-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .78rem 0;
            border-bottom: 1px solid var(--border-2);
        }

        .flag-row:first-child {
            padding-top: 0;
        }

        .flag-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .flag-lbl {
            font-size: .86rem;
            font-weight: 600;
            color: var(--text-2);
        }

        /* ─── QUICK ACTION CHIPS ────────────────────────────────────── */
        .qa-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            background: var(--surf-2);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .42rem .85rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-2);
            transition: background var(--speed), color var(--speed), transform var(--speed);
            cursor: pointer;
        }

        .qa-chip:hover {
            background: var(--p);
            color: #fff;
            border-color: var(--p);
            transform: translateY(-2px);
        }

        .qa-chip i {
            font-size: .88rem;
        }

        /* ─── SECTION HEADING ───────────────────────────────────────── */
        .rc-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--text-1);
            margin: 0;
        }

        .rc-sub {
            font-size: .8rem;
            color: var(--text-3);
            margin: .18rem 0 0;
        }

        /* ─── DATATABLE OVERRIDES ────────────────────────────────────── */
        .prem-dt-wrap .dataTables_filter input {
            border-radius: 999px !important;
            border: 1px solid var(--border) !important;
            padding: .45rem .9rem !important;
            font-size: .82rem !important;
            background: var(--surf-2) !important;
            color: var(--text-2) !important;
            outline: none !important;
            transition: border-color var(--speed), box-shadow var(--speed) !important;
        }

        .prem-dt-wrap .dataTables_filter input:focus {
            border-color: var(--p) !important;
            box-shadow: 0 0 0 3px rgba(var(--p-rgb), .12) !important;
        }

        .prem-dt-wrap .dataTables_filter label,
        .prem-dt-wrap .dataTables_length,
        .prem-dt-wrap .dataTables_info {
            font-size: .8rem;
            color: var(--text-3);
        }

        .prem-dt-wrap .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            font-size: .8rem !important;
        }

        /* ─── RESPONSIVE ────────────────────────────────────────────── */
        @media (max-width:1199.98px) {
            .insight-band {
                grid-template-columns: repeat(2, 1fr);
            }

            .insight-band> :last-child {
                grid-column: span 2;
            }
        }

        @media (max-width:991.98px) {
            .hero-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:767.98px) {
            .hero-wrap {
                padding: 1.6rem 1.4rem;
            }

            .hero-title {
                font-size: 1.7rem;
            }

            .insight-band,
            .table-grid,
            .hero-stats-grid {
                grid-template-columns: 1fr;
            }

            .insight-band> :last-child {
                grid-column: span 1;
            }

            .rms-card .rc-head,
            .rms-card .rc-body {
                padding-inline: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'Today Sales',
                'value' => '$1,284.50',
                'meta' => '+8.4% vs yesterday',
                'icon' => 'ri-money-dollar-circle-line',
                'color' => 'success',
            ],
            [
                'label' => 'Open Orders',
                'value' => '14',
                'meta' => '5 waiting for kitchen',
                'icon' => 'ri-file-list-3-line',
                'color' => 'primary',
            ],
            [
                'label' => 'Active Tables',
                'value' => '9 / 12',
                'meta' => '2 tables need cleanup',
                'icon' => 'ri-restaurant-line',
                'color' => 'warning',
            ],
            [
                'label' => 'Print Queue',
                'value' => '3',
                'meta' => '2 receipts, 1 KOT pending',
                'icon' => 'ri-printer-line',
                'color' => 'info',
            ],
        ];

        $liveOrders = [
            [
                'order' => 'ORD-1042',
                'type' => 'Dine In',
                'table' => 'T-03',
                'status' => 'SENT_TO_KITCHEN',
                'amount' => '$42.00',
                'time' => '2 min ago',
            ],
            [
                'order' => 'ORD-1041',
                'type' => 'Takeaway',
                'table' => '-',
                'status' => 'READY',
                'amount' => '$18.50',
                'time' => '5 min ago',
            ],
            [
                'order' => 'ORD-1040',
                'type' => 'Dine In',
                'table' => 'T-07',
                'status' => 'OPEN',
                'amount' => '$65.20',
                'time' => '7 min ago',
            ],
            [
                'order' => 'ORD-1039',
                'type' => 'Dine In',
                'table' => 'T-01',
                'status' => 'SERVED',
                'amount' => '$29.90',
                'time' => '11 min ago',
            ],
        ];

        $topItems = [
            ['name' => 'Zinger Burger', 'orders' => 24, 'revenue' => '$312.00', 'trend' => '+12%'],
            ['name' => 'Chicken Alfredo Pasta', 'orders' => 18, 'revenue' => '$270.00', 'trend' => '+7%'],
            ['name' => 'Mint Margarita', 'orders' => 31, 'revenue' => '$155.00', 'trend' => '+18%'],
            ['name' => 'Loaded Fries', 'orders' => 15, 'revenue' => '$97.50', 'trend' => '-3%'],
        ];

        $stations = [
            [
                'name' => 'Kitchen Station',
                'status' => 'Busy',
                'detail' => '5 tickets in progress',
                'class' => 'warning',
            ],
            ['name' => 'Cash Counter', 'status' => 'Stable', 'detail' => 'Avg checkout 1m 45s', 'class' => 'success'],
            ['name' => 'Print Agent', 'status' => 'Attention', 'detail' => 'Last sync 6 min ago', 'class' => 'danger'],
        ];

        $tables = [
            ['name' => 'T-01', 'status' => 'Occupied', 'guests' => 4, 'order' => 'ORD-1039'],
            ['name' => 'T-02', 'status' => 'Cleaning', 'guests' => 0, 'order' => '-'],
            ['name' => 'T-03', 'status' => 'Ordered', 'guests' => 2, 'order' => 'ORD-1042'],
            ['name' => 'T-04', 'status' => 'Available', 'guests' => 0, 'order' => '-'],
            ['name' => 'T-05', 'status' => 'Occupied', 'guests' => 3, 'order' => 'ORD-1038'],
            ['name' => 'T-06', 'status' => 'Reserved', 'guests' => 5, 'order' => '-'],
        ];

        $team = [
            ['name' => 'Ahsan', 'role' => 'OWNER', 'shift' => '09:00 - 18:00', 'status' => 'Online'],
            ['name' => 'Sana', 'role' => 'CASHIER', 'shift' => '10:00 - 19:00', 'status' => 'On Counter'],
            ['name' => 'Bilal', 'role' => 'KITCHEN', 'shift' => '11:00 - 20:00', 'status' => 'In Kitchen'],
        ];

        $featureFlags = [
            ['label' => 'Inventory Basic', 'enabled' => true],
            ['label' => 'KDS Basic', 'enabled' => true],
            ['label' => 'QR Ordering', 'enabled' => false],
            ['label' => 'Delivery', 'enabled' => false],
            ['label' => 'Read API', 'enabled' => false],
        ];

        $paymentBreakdown = [
            ['label' => 'Cash', 'value' => '$745.00', 'width' => '58%'],
            ['label' => 'Card', 'value' => '$539.50', 'width' => '42%'],
        ];

        $quickActions = [
            ['label' => 'Open POS', 'icon' => 'ri-cash-line'],
            ['label' => 'Add Order', 'icon' => 'ri-add-circle-line'],
            ['label' => 'Sync Printers', 'icon' => 'ri-printer-cloud-line'],
        ];

        $heroStats = [
            ['label' => 'Service Pace', 'value' => '11m 20s', 'meta' => 'Average table turn'],
            ['label' => 'Guest Satisfaction', 'value' => '4.8/5', 'meta' => 'Based on last 40 visits'],
            ['label' => 'Kitchen Load', 'value' => '74%', 'meta' => 'Peak expected in 18 min'],
            ['label' => 'Revenue Run Rate', 'value' => '$4.9k', 'meta' => 'Projected daily close'],
        ];

        if (isset($dashboard)) {
            $summaryCards = $dashboard['summaryCards'] ?? $summaryCards;
            $liveOrders = $dashboard['liveOrders'] ?? $liveOrders;
            $topItems = $dashboard['topItems'] ?? $topItems;
            $tables = $dashboard['tables'] ?? $tables;
            $team = $dashboard['team'] ?? $team;
            $featureFlags = $dashboard['featureFlags'] ?? $featureFlags;
            $paymentBreakdown = $dashboard['paymentBreakdown'] ?? $paymentBreakdown;
            $quickActions = $dashboard['quickActions'] ?? $quickActions;
            $heroStats = $dashboard['heroStats'] ?? $heroStats;
        }

        $businessName = $dashboard['businessName'] ?? 'Cafe Istanbul';
        $outletName = $dashboard['outletName'] ?? 'Main Outlet';
        $ordersTodayCount = $dashboard['ordersTodayCount'] ?? 14;

        $tableStatusClass = fn(string $status) => match ($status) {
            'Occupied', 'Ordered', 'OPEN', 'SENT TO KITCHEN', 'READY', 'SERVED' => 'bg-warning-subtle text-warning',
            'Available' => 'bg-success-subtle text-success',
            'Reserved' => 'bg-primary-subtle text-primary',
            'Cleaning' => 'bg-danger-subtle text-danger',
            default => 'bg-light text-dark',
        };

        $orderStatusClass = fn(string $status) => match ($status) {
            'READY' => 'bg-success-subtle text-success',
            'OPEN' => 'bg-primary-subtle text-primary',
            'SERVED' => 'bg-info-subtle text-info',
            'SENT_TO_KITCHEN' => 'bg-warning-subtle text-warning',
            default => 'bg-light text-dark',
        };
    @endphp

    <div class="rms-dash">

        {{-- ───── ROW 1 : HERO + KPI CARDS ───────────────────────────── --}}
        <div class="row g-3 mb-3 rms-row">
            {{-- Hero Banner --}}
            <div class="col-12">
                <div class="hero-wrap">
                    <div class="hero-content">
                        <div class="row align-items-center g-4 g-xl-5">
                            <div class="col-xl-7">
                                <div class="hero-pill">
                                    <span class="dot"></span>
                                    Bronze Plan Workspace
                                </div>
                                <h1 class="hero-title">Welcome back, {{ auth()->user()?->name ?? 'Owner' }}</h1>
                                <p class="hero-sub mb-0">{{ $businessName }}, {{ $outletName }} — live orders, staff,
                                    print queues, stock alerts, and plan limits, all in one view.</p>
                                <div class="hero-actions">
                                    <a href="{{ route('service.pos') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                                        <i class="ri-store-2-line me-1"></i>Launch POS
                                    </a>
                                    <a href="#dashboard-live-orders-table" class="btn btn-ghost-white btn-sm">
                                        <i class="ri-radar-line me-1"></i>Live Orders
                                    </a>
                                    <span class="sync-chip">
                                        <i class="ri-refresh-line"></i>Last sync 2 min ago
                                    </span>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="hero-stats-grid">
                                    @foreach ($heroStats as $stat)
                                        <div class="hstat">
                                            <span class="lbl">{{ $stat['label'] }}</span>
                                            <div class="val">{{ $stat['value'] }}</div>
                                            <div class="desc">{{ $stat['meta'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI Cards --}}
            @foreach ($summaryCards as $i => $card)
                <div class="col-sm-6 col-xl-3" style="animation-delay:{{ $i * 0.06 + 0.1 }}s">
                    <div class="kpi-card">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="kpi-label">{{ $card['label'] }}</div>
                                <div class="kpi-value">{{ $card['value'] }}</div>
                                <div class="kpi-meta">{{ $card['meta'] }}</div>
                            </div>
                            <div class="kpi-icon text-{{ $card['color'] }} bg-{{ $card['color'] }}-subtle">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <span class="kpi-badge live">
                            <i class="ri-pulse-line"></i>Live metric
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ───── ROW 2 : INSIGHT BAND ────────────────────────────────── --}}
        <div class="row g-3 mb-3 rms-row">
            <div class="col-12">
                <div class="insight-band">
                    <div class="insight-tile">
                        <div class="kicker">Premium Signal</div>
                        <div class="insight-title">Lunch rush building</div>
                        <p class="insight-copy">Orders accelerated 18% in the last 25 minutes with takeaway driving most of
                            the uplift.</p>
                    </div>
                    <div class="insight-tile">
                        <div class="kicker">Focus Area</div>
                        <div class="insight-title">Printer sync needs attention</div>
                        <p class="insight-copy">Receipt delivery is stable, but kitchen print latency is creeping up before
                            the next service spike.</p>
                    </div>
                    <div class="insight-tile">
                        <div class="kicker">Quick Actions</div>
                        <div class="insight-title">Shortcuts</div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach ($quickActions as $action)
                                <span class="qa-chip">
                                    <i class="{{ $action['icon'] }}"></i>{{ $action['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ───── ROW 3 : LIVE ORDERS + STATION PULSE + PAYMENT ──────── --}}
        <div class="row g-3 mb-3 rms-row">
            {{-- Live Order Board --}}
            <div class="col-xl-8">
                <div class="rms-card prem-dt-wrap h-100">
                    <div class="rc-head d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="kicker">Service Flow</div>
                            <div class="rc-title">Live Order Board</div>
                            <div class="rc-sub">Real-time dine-in and takeaway activity.</div>
                        </div>
                        <span
                            class="badge rounded-pill bg-dark-subtle text-dark px-3 py-2 fw-semibold">{{ $ordersTodayCount }}
                            Orders Today</span>
                    </div>
                    <div class="rc-body pt-2">
                        <table id="dashboard-live-orders-table"
                            class="prem-table table table-hover align-middle table-nowrap w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Type</th>
                                    <th>Table</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($liveOrders as $order)
                                    <tr>
                                        <td>
                                            <div class="order-id">{{ $order['order'] }}</div>
                                            <div class="sub-text">Guest-facing ref</div>
                                        </td>
                                        <td>{{ $order['type'] }}</td>
                                        <td>{{ $order['table'] }}</td>
                                        <td>
                                            <span class="s-badge {{ $orderStatusClass($order['status']) }}">
                                                {{ str_replace('_', ' ', $order['status']) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold" style="color:var(--text-1)">{{ $order['amount'] }}</td>
                                        <td style="color:var(--text-3)">{{ $order['time'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Station Pulse + Payment Split --}}
            <div class="col-xl-4 d-flex flex-column gap-3">
                {{-- Station Pulse --}}
                <div class="rms-card flex-fill">
                    <div class="rc-head">
                        <div class="kicker">System Health</div>
                        <div class="rc-title">Station Pulse</div>
                        <div class="rc-sub">Kitchen, cashier, and print at a glance.</div>
                    </div>
                    <div class="rc-body">
                        <div class="surf-list">
                            @foreach ($stations as $station)
                                <div class="surf-item">
                                    <div>
                                        <div class="surf-item-title">{{ $station['name'] }}</div>
                                        <div class="surf-item-detail">{{ $station['detail'] }}</div>
                                    </div>
                                    <span
                                        class="badge rounded-pill bg-{{ $station['class'] }}-subtle text-{{ $station['class'] }} px-3 py-2 fw-semibold">
                                        {{ $station['status'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Payment Split --}}
                <div class="rms-card flex-fill">
                    <div class="rc-head">
                        <div class="kicker">Revenue Mix</div>
                        <div class="rc-title">Payment Split</div>
                        <div class="rc-sub">Today by payment method.</div>
                    </div>
                    <div class="rc-body">
                        @foreach ($paymentBreakdown as $payment)
                            <div class="pay-row">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="pay-label">{{ $payment['label'] }}</span>
                                    <span class="pay-amount">{{ $payment['value'] }}</span>
                                </div>
                                <div class="prem-progress">
                                    <div class="bar" style="width:{{ $payment['width'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ───── ROW 4 : TABLE STATUS + TOP ITEMS + STAFF + FLAGS ──── --}}
        <div class="row g-3 rms-row">
            {{-- Table Status --}}
            <div class="col-xl-4">
                <div class="rms-card h-100">
                    <div class="rc-head">
                        <div class="kicker">Front of House</div>
                        <div class="rc-title">Table Status</div>
                        <div class="rc-sub">Occupancy and service snapshot.</div>
                    </div>
                    <div class="rc-body">
                        <div class="table-grid">
                            @foreach ($tables as $table)
                                <div class="table-tile">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <div class="table-name">{{ $table['name'] }}</div>
                                            <span
                                                class="s-badge {{ $tableStatusClass($table['status']) }} mt-1">{{ $table['status'] }}</span>
                                        </div>
                                        <i class="ri-layout-grid-line fs-4" style="color:var(--text-4)"></i>
                                    </div>
                                    <div class="table-detail">Guests: {{ $table['guests'] }}</div>
                                    <div class="table-detail">Order: {{ $table['order'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Menu Items --}}
            <div class="col-xl-4">
                <div class="rms-card prem-dt-wrap h-100">
                    <div class="rc-head d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="kicker">Menu Performance</div>
                            <div class="rc-title">Top Menu Items</div>
                            <div class="rc-sub">Best sellers by demand and revenue.</div>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 fw-semibold">Live
                            Data</span>
                    </div>
                    <div class="rc-body pt-2">
                        <table id="dashboard-top-items-table"
                            class="prem-table table table-hover align-middle table-nowrap w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topItems as $item)
                                    <tr>
                                        <td>
                                            <div class="order-id">{{ $item['name'] }}</div>
                                            <div class="sub-text">High-visibility item</div>
                                        </td>
                                        <td>{{ $item['orders'] }}</td>
                                        <td class="fw-bold" style="color:var(--text-1)">{{ $item['revenue'] }}</td>
                                        <td>
                                            <span
                                                class="badge rounded-pill {{ str_contains($item['trend'], '-') ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-3 py-2 fw-semibold">
                                                {{ $item['trend'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Staff + Feature Flags --}}
            <div class="col-xl-4 d-flex flex-column gap-3">
                {{-- Staff Coverage --}}
                <div class="rms-card flex-fill">
                    <div class="rc-head">
                        <div class="kicker">People on Shift</div>
                        <div class="rc-title">Staff Coverage</div>
                        <div class="rc-sub">Current service window team.</div>
                    </div>
                    <div class="rc-body">
                        <div class="surf-list">
                            @foreach ($team as $member)
                                <div class="surf-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="t-avatar">{{ strtoupper(substr($member['name'], 0, 1)) }}</div>
                                        <div>
                                            <div class="staff-name">{{ $member['name'] }}</div>
                                            <div class="staff-meta">{{ $member['role'] }} · {{ $member['shift'] }}</div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 fw-semibold">{{ $member['status'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Bronze Features --}}
                <div class="rms-card flex-fill">
                    <div class="rc-head">
                        <div class="kicker">Plan Controls</div>
                        <div class="rc-title">Bronze Features</div>
                        <div class="rc-sub">Active in current subscription tier.</div>
                    </div>
                    <div class="rc-body">
                        @foreach ($featureFlags as $flag)
                            <div class="flag-row">
                                <span class="flag-lbl">{{ $flag['label'] }}</span>
                                <span
                                    class="badge rounded-pill {{ $flag['enabled'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 fw-semibold">
                                    {{ $flag['enabled'] ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /rms-dash --}}
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /* ── DataTables ── */
            if (window.jQuery && document.getElementById('dashboard-live-orders-table')) {
                $('#dashboard-live-orders-table').DataTable({
                    responsive: true,
                    pageLength: 5,
                    lengthChange: false,
                    info: true,
                    order: [
                        [5, 'asc']
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search live orders…',
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    }
                });
            }

            if (window.jQuery && document.getElementById('dashboard-top-items-table')) {
                $('#dashboard-top-items-table').DataTable({
                    responsive: true,
                    pageLength: 5,
                    lengthChange: false,
                    info: true,
                    order: [
                        [1, 'desc']
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search top items…',
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    }
                });
            }

            /* ── Animate progress bars on load ── */
            document.querySelectorAll('.prem-progress .bar').forEach(bar => {
                const target = bar.style.width;
                bar.style.width = '0';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        bar.style.width = target;
                    });
                });
            });
        });
    </script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
