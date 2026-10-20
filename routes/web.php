<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ModifierGroupController;
use App\Http\Controllers\ModifierOptionController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrintJobController;
use App\Http\Controllers\QrOrderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockItemController;
use Illuminate\Support\Facades\Route;

Route::redirect('/register', '/login');
Route::redirect('/auth-signup', '/login');
Route::get('/qr-order', [QrOrderController::class, 'show'])->name('customer.order');
Route::get('/qr/table/{table}/menu', [QrOrderController::class, 'show'])->name('customer.table.order');
Route::post('/qr/orders', [QrOrderController::class, 'store'])->name('customer.orders.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth-signin', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
    Route::get('/auth-reset-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->middleware('throttle:login')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::middleware(['tenant.context', 'plan.limits'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::middleware(['role:OWNER,CASHIER', 'ability:orders.manage'])->group(function () {
            Route::resource('tables', DiningTableController::class)->except(['show'])->middleware(['ability:tables.manage', 'throttle:writes']);
            Route::get('pos-orders', [ServiceController::class, 'pos'])->name('service.pos');
            Route::post('orders/start', [ServiceController::class, 'startOrder'])->middleware('throttle:writes')->name('orders.start');
            Route::post('orders/{order}/items', [ServiceController::class, 'addItem'])->middleware('throttle:writes')->name('orders.items.store');
            Route::patch('orders/{order}/items/{item}', [ServiceController::class, 'updateItem'])->middleware('throttle:writes')->name('orders.items.update');
            Route::delete('orders/{order}/items/{item}', [ServiceController::class, 'removeItem'])->middleware('throttle:writes')->name('orders.items.destroy');
            Route::patch('orders/{order}/discount', [ServiceController::class, 'discount'])->middleware('throttle:writes')->name('orders.discount');
            Route::patch('orders/{order}/status', [ServiceController::class, 'status'])->middleware('throttle:writes')->name('orders.status');
            Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->middleware('throttle:writes')->name('orders.payments.store');
            Route::get('orders/{order}/receipt', [ReceiptController::class, 'show'])->name('receipts.show');
            Route::get('orders/{order}/receipt.pdf', [ReceiptController::class, 'pdf'])->name('receipts.pdf');
            Route::get('print-jobs', [PrintJobController::class, 'index'])->name('print-jobs.index');
            Route::patch('print-jobs/{printJob}/retry', [PrintJobController::class, 'retry'])->middleware('throttle:writes')->name('print-jobs.retry');
        });

        Route::middleware(['role:OWNER,CASHIER,KITCHEN', 'ability:orders.view'])->group(function () {
            Route::get('orders-queue', [ServiceController::class, 'queue'])->name('orders.queue');
        });

        Route::middleware(['role:KITCHEN', 'ability:kitchen.manage'])->group(function () {
            Route::get('kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
            Route::patch('kitchen/tickets/{ticket}', [KitchenController::class, 'update'])->middleware('throttle:writes')->name('kitchen.tickets.update');
        });

        Route::middleware('role:OWNER')->group(function () {
            Route::resource('staff', StaffController::class)->except(['show', 'destroy'])->middleware(['ability:staff.manage', 'throttle:writes']);
            Route::patch('staff/{staff}/status', [StaffController::class, 'status'])->middleware(['ability:staff.manage', 'throttle:writes'])->name('staff.status');
            Route::post('staff/{staff}/password-reset', [StaffController::class, 'sendPasswordReset'])->middleware(['ability:staff.manage', 'throttle:writes'])->name('staff.password-reset');

            Route::resource('menu-categories', MenuCategoryController::class)->parameters([
                'menu-categories' => 'menuCategory',
            ])->except(['show'])->middleware(['ability:catalog.manage', 'throttle:writes']);
            Route::patch('menu-categories/{category}/restore', [MenuCategoryController::class, 'restore'])->middleware(['ability:catalog.manage', 'throttle:writes'])->name('menu-categories.restore');

            Route::resource('menu-items', MenuItemController::class)->parameters([
                'menu-items' => 'menuItem',
            ])->except(['show'])->middleware(['ability:catalog.manage', 'throttle:writes']);
            Route::get('menu-item-editor', [MenuItemController::class, 'create'])->middleware('ability:catalog.manage')->name('menu-item-editor');
            Route::patch('menu-items/{item}/restore', [MenuItemController::class, 'restore'])->middleware(['ability:catalog.manage', 'throttle:writes'])->name('menu-items.restore');

            Route::resource('modifier-groups', ModifierGroupController::class)->parameters([
                'modifier-groups' => 'modifierGroup',
            ])->except(['index', 'show'])->middleware(['ability:catalog.manage', 'throttle:writes']);
            Route::get('modifier-groups/{modifierGroup}/options/create', [ModifierOptionController::class, 'create'])->middleware('ability:catalog.manage')->name('modifier-options.create');
            Route::post('modifier-groups/{modifierGroup}/options', [ModifierOptionController::class, 'store'])->middleware(['ability:catalog.manage', 'throttle:writes'])->name('modifier-options.store');
            Route::get('modifier-groups/{modifierGroup}/options/{modifierOption}/edit', [ModifierOptionController::class, 'edit'])->middleware('ability:catalog.manage')->name('modifier-options.edit');
            Route::put('modifier-groups/{modifierGroup}/options/{modifierOption}', [ModifierOptionController::class, 'update'])->middleware(['ability:catalog.manage', 'throttle:writes'])->name('modifier-options.update');
            Route::delete('modifier-groups/{modifierGroup}/options/{modifierOption}', [ModifierOptionController::class, 'destroy'])->middleware(['ability:catalog.manage', 'throttle:writes'])->name('modifier-options.destroy');

            Route::resource('stock-items', StockItemController::class)->parameters([
                'stock-items' => 'stockItem',
            ])->except(['show'])->middleware(['ability:inventory.manage', 'throttle:writes']);
            Route::patch('stock-items/{stockItem}/restore', [StockItemController::class, 'restore'])->middleware(['ability:inventory.manage', 'throttle:writes'])->name('stock-items.restore');
            Route::post('stock-items/{stockItem}/movements', [StockItemController::class, 'movement'])->middleware(['ability:inventory.adjust', 'throttle:writes'])->name('stock-items.movements.store');
            Route::get('recipes', [RecipeController::class, 'index'])->middleware('ability:recipes.manage')->name('recipes.index');
            Route::get('recipes/{menuItem}/edit', [RecipeController::class, 'edit'])->middleware('ability:recipes.manage')->name('recipes.edit');
            Route::put('recipes/{menuItem}', [RecipeController::class, 'update'])->middleware(['ability:recipes.manage', 'throttle:writes'])->name('recipes.update');
            Route::get('reports/sales', [ReportController::class, 'sales'])->middleware('ability:reports.sales.view')->name('reports.sales');
            Route::get('settings', [SettingsController::class, 'edit'])->middleware('ability:settings.manage')->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->middleware(['ability:settings.manage', 'throttle:writes'])->name('settings.update');
        });

        Route::get('{page}', [DashboardController::class, 'index'])
            ->where('page', '[A-Za-z0-9\-]+');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
