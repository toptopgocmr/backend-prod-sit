<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, CustomOrder, Client, Product, Expense, Equipment, StockMovement, MaintenanceLog};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // ─── KPIs du jour ────────────────────────────────────────
        $kpis = [
            'revenue_today'   => $this->revenueForPeriod($today, $today),
            'revenue_month'   => $this->revenueForPeriod($thisMonth, Carbon::now()),
            'revenue_last_m'  => $this->revenueForPeriod($lastMonth, Carbon::now()->subMonth()->endOfMonth()),
            'orders_today'    => Order::whereDate('created_at', $today)->count(),
            'custom_pending'  => CustomOrder::whereNotIn('status', ['livre','annule'])->count(),
            'clients_total'   => Client::count(),
            'clients_new'     => Client::whereDate('created_at', '>=', $thisMonth)->count(),
            'expenses_month'  => Expense::whereDate('expense_date', '>=', $thisMonth)->sum('amount'),
        ];

        $kpis['profit_month']  = $kpis['revenue_month'] - $kpis['expenses_month'];
        $kpis['revenue_growth'] = $kpis['revenue_last_m'] > 0
            ? round((($kpis['revenue_month'] - $kpis['revenue_last_m']) / $kpis['revenue_last_m']) * 100, 1)
            : 0;

        // ─── Stock faible ─────────────────────────────────────────
        $lowStockProducts = Product::active()
            ->whereRaw('stock_quantity <= alert_threshold')
            ->orWhereRaw('available_meters <= alert_threshold')
            ->with('category')
            ->limit(8)
            ->get();

        // ─── Commandes sur mesure en cours ────────────────────────
        $activeCustomOrders = CustomOrder::with([
                'client'    => fn($q) => $q->withTrashed(),
                'couturier' => fn($q) => $q->withTrashed(),
            ])
            ->whereNotIn('status', ['livre','annule'])
            ->orderBy('delivery_date')
            ->limit(10)
            ->get();

        // ─── Dernières ventes ─────────────────────────────────────
        $recentOrders = Order::with(['client' => fn($q) => $q->withTrashed()])
            ->latest()
            ->limit(8)
            ->get();

        // ─── CA par mois (12 derniers mois) ───────────────────────
        $revenueChart = $this->getMonthlyRevenue(12);

        // ─── Top produits ─────────────────────────────────────────
        $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.name', DB::raw('SUM(order_items.total) as revenue'), DB::raw('SUM(order_items.quantity) as qty'))
            ->whereDate('order_items.created_at', '>=', $thisMonth)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ─── Maintenance ──────────────────────────────────────────
        $equipmentAlerts = Equipment::where('status', '!=', 'operationnel')
            ->orWhereDate('next_maintenance_date', '<=', Carbon::now()->addDays(7))
            ->limit(5)
            ->get();

        $maintenancePending = MaintenanceLog::where('status', '!=', 'resolu')
            ->with('equipment')
            ->latest()
            ->limit(5)
            ->get();

        // ─── Performance couturiers ───────────────────────────────
        $couturiersPerf = DB::table('custom_orders')
            ->join('users', 'users.id', '=', 'custom_orders.assigned_to')
            ->select(
                'users.name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'livre' THEN 1 ELSE 0 END) as completed"),
                DB::raw('AVG(DATEDIFF(completed_at, started_at)) as avg_days')
            )
            ->whereDate('custom_orders.created_at', '>=', $thisMonth)
            ->groupBy('users.id', 'users.name')
            ->get();

        return view('dashboard.index', compact(
            'kpis','lowStockProducts','activeCustomOrders','recentOrders',
            'revenueChart','topProducts','equipmentAlerts','maintenancePending','couturiersPerf'
        ));
    }

    private function revenueForPeriod(Carbon $start, Carbon $end): float {
        $orders = Order::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->where('status', '!=', 'cancelled')
            ->sum('amount_paid');
        $custom = CustomOrder::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->where('status', '!=', 'annule')
            ->sum('amount_paid');
        return $orders + $custom;
    }

    private function getMonthlyRevenue(int $months): array {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $data[] = [
                'label'   => $month->translatedFormat('M Y'),
                'revenue' => $this->revenueForPeriod($month->copy()->startOfMonth(), $month->copy()->endOfMonth()),
                'expenses'=> Expense::whereYear('expense_date', $month->year)
                    ->whereMonth('expense_date', $month->month)
                    ->sum('amount'),
            ];
        }
        return $data;
    }
}
