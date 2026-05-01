@php
    $isMenuCatalog = request()->is('menu-categories*') || request()->is('menu-items*') || request()->is('menu-item-editor') || request()->is('modifier-groups*');
    $isPosOrders = request()->is('pos-orders') || request()->is('orders-queue');
    $isTables = request()->is('tables*');
    $isKitchen = request()->is('kitchen*');
    $isPrinting = request()->is('print-jobs*');
    $isInventory = request()->is('stock-items*') || request()->is('recipes*');
    $isReports = request()->is('reports*');
    $isSettings = request()->is('settings*');
    $role = auth()->user()?->role?->value;
    $canUseService = in_array($role, ['OWNER', 'CASHIER'], true);
    $canUseKitchen = $role === 'KITCHEN';
@endphp

<ul class="main-menu" id="all-menu-items" role="menu">
    <li class="menu-title" role="presentation">RMS Bronze</li>

    <li class="slide {{ request()->is('index') || request()->is('/') ? 'open-menu' : '' }}">
        <a href="{{ route('dashboard') }}" class="side-menu__item {{ request()->is('index') || request()->is('/') ? 'active' : '' }}" role="menuitem">
            <span class="side_menu_icon"><i class="ri-dashboard-line"></i></span>
            <span class="side-menu__label">Dashboard</span>
        </a>
    </li>

    <li class="menu-title" role="presentation">Operations</li>

    <li class="slide {{ $isPosOrders ? 'open-menu' : '' }}">
        <a href="#!" class="side-menu__item {{ $isPosOrders ? 'active' : '' }}" role="menuitem" aria-expanded="{{ $isPosOrders ? 'true' : 'false' }}">
            <span class="side_menu_icon"><i class="ri-restaurant-2-line"></i></span>
            <span class="side-menu__label">POS & Orders</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">
            @if ($canUseService)
                <li class="slide">
                    <a href="{{ route('service.pos') }}" class="side-menu__item {{ request()->is('pos-orders') ? 'active' : '' }}" role="menuitem">POS Screen</a>
                </li>
            @endif
            <li class="slide">
                <a href="{{ route('orders.queue') }}" class="side-menu__item {{ request()->is('orders-queue') ? 'active' : '' }}" role="menuitem">Orders Queue</a>
            </li>
        </ul>
    </li>

    <li class="slide {{ $isMenuCatalog ? 'open-menu' : '' }}">
        <a href="#!" class="side-menu__item {{ $isMenuCatalog ? 'active' : '' }}" role="menuitem" aria-expanded="{{ $isMenuCatalog ? 'true' : 'false' }}">
            <span class="side_menu_icon"><i class="ri-store-2-line"></i></span>
            <span class="side-menu__label">Menu & Categories</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">
            <li class="slide">
                <a href="{{ route('menu-categories.index') }}" class="side-menu__item {{ request()->is('menu-categories') ? 'active' : '' }}" role="menuitem">Categories</a>
            </li>
            <li class="slide">
                <a href="{{ route('menu-items.index') }}" class="side-menu__item {{ request()->is('menu-items') ? 'active' : '' }}" role="menuitem">Menu Items</a>
            </li>
            <li class="slide">
                <a href="{{ route('menu-item-editor') }}" class="side-menu__item {{ request()->is('menu-item-editor') ? 'active' : '' }}" role="menuitem">Item Editor</a>
            </li>
        </ul>
    </li>

    @if ($canUseService)
        <li class="slide {{ $isTables ? 'open-menu' : '' }}">
            <a href="{{ route('tables.index') }}" class="side-menu__item {{ $isTables ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-table-line"></i></span>
                <span class="side-menu__label">Tables</span>
            </a>
        </li>
    @endif

    @if ($canUseService)
        <li class="slide {{ $isPrinting ? 'open-menu' : '' }}">
            <a href="{{ route('print-jobs.index') }}" class="side-menu__item {{ $isPrinting ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-printer-line"></i></span>
                <span class="side-menu__label">Print Queue</span>
            </a>
        </li>
    @endif

    @if ($canUseKitchen)
        <li class="slide {{ $isKitchen ? 'open-menu' : '' }}">
            <a href="{{ route('kitchen.index') }}" class="side-menu__item {{ $isKitchen ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-fire-line"></i></span>
                <span class="side-menu__label">Kitchen / KDS</span>
            </a>
        </li>
    @endif

    <li class="menu-title" role="presentation">Management</li>

    @if (auth()->user()?->role?->value === 'OWNER')
        <li class="slide {{ request()->is('staff*') ? 'open-menu' : '' }}">
            <a href="{{ route('staff.index') }}" class="side-menu__item {{ request()->is('staff*') ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-team-line"></i></span>
                <span class="side-menu__label">Staff & Roles</span>
            </a>
        </li>
    @endif

    @if (auth()->user()?->role?->value === 'OWNER')
        <li class="slide {{ $isInventory ? 'open-menu' : '' }}">
            <a href="#!" class="side-menu__item {{ $isInventory ? 'active' : '' }}" role="menuitem" aria-expanded="{{ $isInventory ? 'true' : 'false' }}">
                <span class="side_menu_icon"><i class="ri-archive-drawer-line"></i></span>
                <span class="side-menu__label">Inventory</span>
                <i class="ri-arrow-down-s-line side-menu__angle"></i>
            </a>
            <ul class="slide-menu" role="menu">
                <li class="slide">
                    <a href="{{ route('stock-items.index') }}" class="side-menu__item {{ request()->is('stock-items*') ? 'active' : '' }}" role="menuitem">Stock Items</a>
                </li>
                <li class="slide">
                    <a href="{{ route('recipes.index') }}" class="side-menu__item {{ request()->is('recipes*') ? 'active' : '' }}" role="menuitem">Recipes / BOM</a>
                </li>
            </ul>
        </li>
    @endif

    @if (auth()->user()?->role?->value === 'OWNER')
        <li class="slide {{ $isReports ? 'open-menu' : '' }}">
            <a href="{{ route('reports.sales') }}" class="side-menu__item {{ $isReports ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-bar-chart-box-line"></i></span>
                <span class="side-menu__label">Reports</span>
            </a>
        </li>
    @endif

    @if (auth()->user()?->role?->value === 'OWNER')
        <li class="slide {{ $isSettings ? 'open-menu' : '' }}">
            <a href="{{ route('settings.edit') }}" class="side-menu__item {{ $isSettings ? 'active' : '' }}" role="menuitem">
                <span class="side_menu_icon"><i class="ri-settings-3-line"></i></span>
                <span class="side-menu__label">Settings</span>
            </a>
        </li>
    @endif
</ul>
