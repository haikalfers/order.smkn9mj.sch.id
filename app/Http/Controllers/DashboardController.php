<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use App\Models\DesignTask;
use App\Models\ProductionTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Router dashboard sesuai role
     */
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'desain'      => $this->desain(),
            'cetak'       => $this->cetak(),
            default       => $this->admin(), // super_admin & admin
        };
    }

    /**
     * Dashboard Super Admin & Admin
     */
    public function admin()
    {
        // Stats
        $activeOrders = Order::whereNotIn('status', ['done', 'delivered', 'cancelled'])->count();
        $doneThisMonth = Order::where('status', 'done')
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();
        $expensesThisMonth = Expense::whereMonth('expense_date', now()->month)
                                    ->whereYear('expense_date', now()->year)
                                    ->sum('amount');
        $overdueOrders = Order::where('deadline', '<', now())
                                ->whereNotIn('status', ['done', 'delivered', 'cancelled'])
                                ->count();

        $stats = [
            'active_orders'        => $activeOrders,
            'done_this_month'      => $doneThisMonth,
            'expenses_this_month'  => $expensesThisMonth,
            'overdue_orders'       => $overdueOrders,
        ];

        // Status breakdown
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->pluck('count', 'status')
                                ->toArray();

        // Chart data (12 bulan terakhir)
        $chartLabels = [];
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartLabels[] = $date->format('M Y');
            $count = Order::whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year)
                            ->count();
            $chartData[] = $count;
        }

        // Overdue orders
        $overdueOrders = Order::where('deadline', '<', now())
                                ->whereNotIn('status', ['done', 'delivered', 'cancelled'])
                                ->with('customer')
                                ->orderBy('deadline')
                                ->take(5)
                                ->get();

        // Recent orders
        $recentOrders = Order::with('customer')
                                ->orderByDesc('created_at')
                                ->take(10)
                                ->get();

        return view('dashboard.admin', compact(
            'stats', 'statusCounts', 'chartLabels', 'chartData', 'overdueOrders', 'recentOrders'
        ));
    }

    /**
     * Dashboard Bagian Desain
     */
    public function desain()
    {
        $myActiveTasks = DesignTask::where('assigned_to', Auth::id())
                                    ->where('status', '!=', 'done')
                                    ->count();

        $activeTasks = DesignTask::where('assigned_to', Auth::id())
                                    ->where('status', '!=', 'done')
                                    ->with('order.customer')
                                    ->orderByDesc('created_at')
                                    ->take(5)
                                    ->get();

        $unassignedTasks = DesignTask::whereNull('assigned_to')
                                    ->with('order.customer')
                                    ->orderBy('created_at')
                                    ->take(5)
                                    ->get();

        return view('dashboard.desain', compact(
            'myActiveTasks', 'activeTasks', 'unassignedTasks'
        ));
    }

    /**
     * Dashboard Bagian Cetak
     */
    public function cetak()
    {
        $activeTasks = ProductionTask::where('status', '!=', 'done')
                                        ->with('order.customer', 'assignedUser')
                                        ->orderByDesc('created_at')
                                        ->take(10)
                                        ->get();

        $readyToPrint = Order::where('status', 'design_done')
                                ->with('customer')
                                ->orderByDesc('created_at')
                                ->take(5)
                                ->get();

        return view('dashboard.cetak', compact('activeTasks', 'readyToPrint'));
    }

    /**
     * Legacy routes (opsional, untuk backward compatibility)
     */
    public function superAdmin()
    {
        return $this->admin();
    }
}
