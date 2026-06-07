<?php

use App\Helpers\RoleRedirect;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignTaskController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductionTaskController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── Root → redirect ke dashboard atau login ─────────────────────────────────
Route::get('/', function () {
    return Auth::check()
        ? redirect(RoleRedirect::dashboardRoute(Auth::user()))
        : redirect()->route('login');
});

// ── Public Tracking Routes ───────────────────────────────────────────────────
// Halaman tracking untuk customer (terpisah dari login page)
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/tracking/{orderNumber}', [TrackingController::class, 'show'])->name('tracking.show');

// API endpoints untuk tracking order
Route::post('/api/tracking/order-status', [TrackingController::class, 'getOrderStatus']);
Route::get('/api/tracking/active-orders', [TrackingController::class, 'getActiveOrders']);
Route::get('/api/tracking/search', [TrackingController::class, 'search']);

// ── Auth routes (Breeze) ─────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard — controller yang menangani redirect sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:super_admin,admin')->name('dashboard.admin');
    Route::get('/dashboard/superadmin', [DashboardController::class, 'superAdmin'])->middleware('role:super_admin')->name('dashboard.superadmin');
    Route::get('/dashboard/desain', [DashboardController::class, 'desain'])->middleware('role:desain')->name('dashboard.desain');
    Route::get('/dashboard/cetak', [DashboardController::class, 'cetak'])->middleware('role:cetak')->name('dashboard.cetak');

    // ── Orders ───────────────────────────────────────────────────────────────
    // Management (Create, Edit, Update, Delete) - Admin only
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('orders', OrderController::class, ['except' => ['show']]);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])
            ->name('orders.print');
    });

    // View only - Admin, Desain, and Cetak
    Route::middleware('role:super_admin,admin,desain,cetak')->group(function () {
        Route::get('orders/{order}', [OrderController::class, 'show'])
            ->name('orders.show');
    });

    // ── Customers ────────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // ── Design Tasks ─────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin,desain')->group(function () {
        Route::resource('design-tasks', DesignTaskController::class);
        Route::patch('design-tasks/{designTask}/assign', [DesignTaskController::class, 'assign'])
            ->name('design-tasks.assign');
        Route::patch('design-tasks/{designTask}/admin-assign', [DesignTaskController::class, 'adminAssign'])
            ->name('design-tasks.admin-assign');
        Route::post('design-tasks/{designTask}/upload', [DesignTaskController::class, 'uploadFile'])
            ->name('design-tasks.upload');
    });

    // ── Production Tasks ─────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin,cetak')->group(function () {
        Route::resource('production-tasks', ProductionTaskController::class);
    });

    // ── Expenses ─────────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        // Nested di bawah orders (fully nested, no shallow)
        Route::resource('orders.expenses', ExpenseController::class);
        
        // Standalone untuk halaman rekap semua expenses
        Route::get('expenses', [ExpenseController::class, 'index'])
            ->name('expenses.index');

        // Export expenses
        Route::get('expenses/export', [ExpenseController::class, 'export'])
            ->name('expenses.export');

        // Nested payments di bawah orders
        Route::resource('orders.payments', PaymentController::class)
            ->only(['store', 'destroy']);
    });

    // ── User Management (Super Admin only) ────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
    });
});
