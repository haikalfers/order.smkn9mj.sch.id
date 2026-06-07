<?php

use App\Helpers\RoleRedirect;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignTaskController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductionTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Root → redirect ke dashboard atau login ─────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect(RoleRedirect::dashboardRoute(auth()->user()))
        : redirect()->route('login');
});

// ── Auth routes (Breeze) ─────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard — controller yang menangani redirect sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/superadmin', [DashboardController::class, 'superAdmin'])->name('dashboard.superadmin');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::get('/dashboard/desain', [DashboardController::class, 'desain'])->name('dashboard.desain');
    Route::get('/dashboard/cetak', [DashboardController::class, 'cetak'])->name('dashboard.cetak');

    // ── Orders ───────────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.status');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])
            ->name('orders.print');
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
        Route::post('design-tasks/{designTask}/upload', [DesignTaskController::class, 'uploadFile'])
            ->name('design-tasks.upload');
    });

    // ── Production Tasks ─────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin,cetak')->group(function () {
        Route::resource('production-tasks', ProductionTaskController::class);
    });

    // ── Expenses ─────────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        // Nested di bawah orders (untuk create/edit/delete dari order detail)
        Route::resource('orders.expenses', ExpenseController::class)
            ->shallow();
        
        // Standalone (untuk halaman rekap semua expenses)
        Route::get('expenses', [ExpenseController::class, 'index'])
            ->name('expenses.index');
    });

    // ── User Management (Super Admin only) ────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
    });
});
