<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PrintJob;
use App\Models\StockItem;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        $outletId = $user->outlet_id;
        $today = now()->startOfDay();

        $settings = TenantSetting::where('tenant_id', $tenantId)->first();

        $todayOrders = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->get();

        $todaySalesCents = $todayOrders
            ->where('status', OrderStatus::PAID)
            ->sum('total_cents');

        $openOrders = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereIn('status', [
                OrderStatus::OPEN,
                OrderStatus::SENT_TO_KITCHEN,
                OrderStatus::READY,
                OrderStatus::SERVED,
            ])
            ->count();

        $kitchenWaiting = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('status', OrderStatus::SENT_TO_KITCHEN)
            ->count();

        $activeTables = DiningTable::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
            ->count();

        $occupiedTables = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereNotNull('dining_table_id')
            ->whereIn('status', [OrderStatus::OPEN, OrderStatus::SENT_TO_KITCHEN, OrderStatus::READY, OrderStatus::SERVED])
            ->distinct('dining_table_id')
            ->count('dining_table_id');

        $printQueueCount = PrintJob::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('status', 'PENDING')
            ->count();

        $pendingReceipts = PrintJob::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('status', 'PENDING')
            ->where('type', 'RECEIPT')
            ->count();

        $pendingKitchen = PrintJob::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('status', 'PENDING')
            ->where('type', 'KOT')
            ->count();

        $liveOrders = Order::query()
            ->with('table')
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Order $order): array => [
                'order' => $order->order_number,
                'type' => str_replace('_', ' ', $order->order_type->value),
                'table' => $order->table?->name ?? '-',
                'status' => $order->status->value,
                'amount' => $this->money($order->total_cents),
                'time' => $order->updated_at?->diffForHumans() ?? '-',
            ])
            ->all();

        $topItems = OrderItem::query()
            ->select([
                'item_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total_cents) as revenue_cents'),
            ])
            ->whereHas('order', fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->where('outlet_id', $outletId)
                ->whereDate('created_at', today()))
            ->groupBy('item_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'name' => $item->item_name,
                'orders' => (int) $item->total_quantity,
                'revenue' => $this->money((int) $item->revenue_cents),
                'trend' => 'Live',
            ])
            ->all();

        $tables = DiningTable::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function (DiningTable $table) use ($tenantId, $outletId): array {
                $order = Order::query()
                    ->where('tenant_id', $tenantId)
                    ->where('outlet_id', $outletId)
                    ->where('dining_table_id', $table->id)
                    ->whereIn('status', [OrderStatus::OPEN, OrderStatus::SENT_TO_KITCHEN, OrderStatus::READY, OrderStatus::SERVED])
                    ->latest()
                    ->first();

                return [
                    'name' => $table->name,
                    'status' => $order ? str_replace('_', ' ', $order->status->value) : 'Available',
                    'guests' => $order?->guest_count ?? 0,
                    'order' => $order?->order_number ?? '-',
                ];
            })
            ->all();

        $team = User::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByRaw("CASE role WHEN 'OWNER' THEN 1 WHEN 'CASHIER' THEN 2 WHEN 'KITCHEN' THEN 3 ELSE 4 END")
            ->limit(5)
            ->get()
            ->map(fn (User $member): array => [
                'name' => $member->name,
                'role' => $member->role->value,
                'shift' => $member->last_active_at?->diffForHumans() ?? 'Not active yet',
                'status' => $member->id === $user->id ? 'Online' : 'Active',
            ])
            ->all();

        $paymentBreakdown = Payment::query()
            ->select(['method', DB::raw('SUM(amount_cents) as amount_cents')])
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->groupBy('method')
            ->get();

        $paymentTotal = max(1, (int) $paymentBreakdown->sum('amount_cents'));

        $paymentBreakdown = $paymentBreakdown
            ->map(fn (Payment $payment): array => [
                'label' => $payment->method->value,
                'value' => $this->money((int) $payment->amount_cents),
                'width' => round((((int) $payment->amount_cents) / $paymentTotal) * 100).'%',
            ])
            ->all();

        $lowStockCount = StockItem::query()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->count();

        $dashboard = [
            'summaryCards' => [
                ['label' => 'Today Sales', 'value' => $this->money($todaySalesCents), 'meta' => $todayOrders->count().' orders today', 'icon' => 'ri-money-dollar-circle-line', 'color' => 'success'],
                ['label' => 'Open Orders', 'value' => (string) $openOrders, 'meta' => $kitchenWaiting.' waiting for kitchen', 'icon' => 'ri-file-list-3-line', 'color' => 'primary'],
                ['label' => 'Active Tables', 'value' => $occupiedTables.' / '.$activeTables, 'meta' => $lowStockCount.' low stock alerts', 'icon' => 'ri-restaurant-line', 'color' => 'warning'],
                ['label' => 'Print Queue', 'value' => (string) $printQueueCount, 'meta' => $pendingReceipts.' receipts, '.$pendingKitchen.' kitchen tickets pending', 'icon' => 'ri-printer-line', 'color' => 'info'],
            ],
            'liveOrders' => $liveOrders,
            'topItems' => $topItems,
            'tables' => $tables,
            'team' => $team,
            'featureFlags' => [
                ['label' => 'Inventory Basic', 'enabled' => (bool) $settings?->inventory_basic],
                ['label' => 'KDS Basic', 'enabled' => (bool) $settings?->kds_basic],
                ['label' => 'QR Ordering', 'enabled' => (bool) $settings?->qr_ordering],
                ['label' => 'Delivery', 'enabled' => (bool) $settings?->delivery],
                ['label' => 'Read API', 'enabled' => (bool) $settings?->api_read],
            ],
            'paymentBreakdown' => $paymentBreakdown ?: [
                ['label' => 'Cash', 'value' => $this->money(0), 'width' => '0%'],
                ['label' => 'Card', 'value' => $this->money(0), 'width' => '0%'],
            ],
            'businessName' => $user->tenant?->name ?? 'Business',
            'outletName' => $user->outlet?->name ?? 'Main Outlet',
            'ordersTodayCount' => $todayOrders->count(),
            'quickActions' => [
                ['label' => 'Open POS', 'icon' => 'ri-cash-line'],
                ['label' => 'Add Order', 'icon' => 'ri-add-circle-line'],
                ['label' => 'Sync Printers', 'icon' => 'ri-printer-cloud-line'],
            ],
            'heroStats' => [
                ['label' => 'Menu Items', 'value' => (string) MenuItem::where('tenant_id', $tenantId)->where('is_active', true)->count(), 'meta' => 'Active sellable items'],
                ['label' => 'Staff Accounts', 'value' => count($team).' / 3', 'meta' => 'Bronze active user limit'],
                ['label' => 'Open Since', 'value' => $today->format('g:i A'), 'meta' => 'Local business day'],
                ['label' => 'Outlet', 'value' => $user->outlet?->code ?? 'MAIN', 'meta' => $user->outlet?->name ?? 'Main Outlet'],
            ],
        ];

        return view('index', compact('dashboard'));
    }

    public function index($page)
    {
        $allowedPages = [
            'apps-calendar',
            'apps-chat',
            'apps-course-details',
            'apps-course-overview',
            'apps-course-student-add',
            'apps-course-student-details',
            'apps-course-student-list',
            'apps-course-teacher-add',
            'apps-course-teacher-details',
            'apps-course-teacher-list',
            'apps-create-invoices',
            'apps-email-list',
            'apps-email-view',
            'apps-file-manager',
            'apps-invoices-details',
            'apps-invoices-list',
            'apps-invoices-print',
            'apps-product-cart',
            'apps-product-category-list',
            'apps-product-checkout',
            'apps-product-create',
            'apps-product-details',
            'apps-product-order-details',
            'apps-product-order-list',
            'apps-products',
            'apps-products-list',
            'apps-projects-create',
            'apps-projects-list',
            'apps-projects-overview',
            'apps-social-activity',
            'apps-social-events',
            'apps-social-feeds',
            'apps-social-friends',
            'apps-social-watch-video',
            'apps-tasks-kanban',
            'apps-todo',
            'auth-401',
            'auth-404',
            'auth-500',
            'auth-coming-soon',
            'auth-create-password',
            'auth-lockscreen',
            'auth-offline',
            'auth-reset-password',
            'auth-signin',
            'auth-signup',
            'auth-two-step-verify',
            'chart-apex-line',
            'chart-js-chart',
            'charts-apex-area',
            'charts-apex-bar',
            'charts-apex-boxplot',
            'charts-apex-bubble',
            'charts-apex-candlestick',
            'charts-apex-column',
            'charts-apex-funnel',
            'charts-apex-heatmap',
            'charts-apex-mixed',
            'charts-apex-pie',
            'charts-apex-polar',
            'charts-apex-radar',
            'charts-apex-radialbar',
            'charts-apex-range-area',
            'charts-apex-scatter',
            'charts-apex-slope',
            'charts-apex-timeline',
            'charts-apex-treemap',
            'charts-echarts',
            'dashboard-online-course',
            'dashboard-project-management',
            'dashboard-social-media',
            'demo-layout-compact',
            'demo-layout-horizontal',
            'demo-layout-semibox',
            'demo-layout-small-icon',
            'demo-layout-two-column',
            'google-maps',
            'icons-bootstrap-icons',
            'icons-remix',
            'index',
            'leaflet-maps',
            'menu-categories',
            'menu-items',
            'menu-item-editor',
            'orders-queue',
            'pages-blog-create',
            'pages-blog-details',
            'pages-blog-list',
            'pages-faqs',
            'pages-gallery',
            'pages-pricing',
            'pages-privacy-policy',
            'pages-profile-connections',
            'pages-profile-documents',
            'pages-profile-edit-billing-plans',
            'pages-profile-edit-connections',
            'pages-profile-edit-notifications',
            'pages-profile-edit-overview',
            'pages-profile-edit-security',
            'pages-profile-overview',
            'pages-profile-project',
            'pages-search-result',
            'pages-starter',
            'pages-terms-conditions',
            'pages-timeline',
            'pos-orders',
            'ui-accordions',
            'ui-advance-swiper',
            'ui-alert',
            'ui-avatars',
            'ui-badges',
            'ui-block',
            'ui-breadcrumbs',
            'ui-button-group',
            'ui-buttons',
            'ui-card',
            'ui-carousel',
            'ui-cookie',
            'ui-countup',
            'ui-date-picker',
            'ui-draggable-cards',
            'ui-dropdowns',
            'ui-floating-labels',
            'ui-form-advanced',
            'ui-form-checkboxs-radios',
            'ui-form-editor',
            'ui-form-elements',
            'ui-form-file-uploads',
            'ui-form-input-group',
            'ui-form-input-masks',
            'ui-form-layout',
            'ui-form-range',
            'ui-form-select',
            'ui-form-validation',
            'ui-form-wizards',
            'ui-images-figures',
            'ui-links',
            'ui-list',
            'ui-media-player',
            'ui-modal',
            'ui-offcanvas',
            'ui-pagination',
            'ui-placeholders',
            'ui-popover',
            'ui-progress',
            'ui-ratings',
            'ui-scrollspy',
            'ui-separator',
            'ui-sortable-js',
            'ui-spinner',
            'ui-sweetalert2',
            'ui-tabs',
            'ui-toast',
            'ui-tooltips',
            'ui-tour',
            'ui-treeview',
            'ui-typography',
            'ui-utilities',
            'under-maintenance',
            'vector-maps',
        ];

        if (in_array($page, $allowedPages, true) && view()->exists($page)) {
            $this->authorizePage($page);

            return view($page);
        }

        abort(404);
    }

    private function money(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }

    private function authorizePage(string $page): void
    {
        $role = auth()->user()?->role;

        $pageRoles = [
            'menu-categories' => [UserRole::OWNER],
            'menu-items' => [UserRole::OWNER],
            'menu-item-editor' => [UserRole::OWNER],
            'pos-orders' => [UserRole::OWNER, UserRole::CASHIER],
            'orders-queue' => [UserRole::OWNER, UserRole::CASHIER, UserRole::KITCHEN],
            'apps-product-checkout' => [UserRole::OWNER, UserRole::CASHIER],
            'apps-product-order-list' => [UserRole::OWNER, UserRole::CASHIER],
            'apps-product-order-details' => [UserRole::OWNER, UserRole::CASHIER, UserRole::KITCHEN],
        ];

        $allowedRoles = $pageRoles[$page] ?? [UserRole::OWNER];

        abort_if(! $role || ! in_array($role, $allowedRoles, true), 403, 'You do not have access to this screen.');
    }
}
