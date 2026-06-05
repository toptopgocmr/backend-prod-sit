<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order, CustomOrder, Client, Product, Expense};
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index()
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'kpis'              => $this->kpisData($today, $thisMonth),
            'low_stock_count'   => Product::active()->whereRaw('stock_quantity <= alert_threshold')->count(),
            'pending_custom'    => CustomOrder::whereNotIn('status',['livre','annule'])->count(),
            'today_orders'      => Order::whereDate('created_at', $today)->count(),
        ]);
    }

    public function kpis()
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        return response()->json($this->kpisData($today, $thisMonth));
    }

    public function revenueChart()
    {
        $data = $this->financeService->getDailyRevenue(30);
        return response()->json($data);
    }

    private function kpisData(Carbon $today, Carbon $thisMonth): array
    {
        $revenueMonth = Order::whereDate('created_at','>=',$thisMonth)->where('status','!=','cancelled')->sum('amount_paid')
                      + CustomOrder::whereDate('created_at','>=',$thisMonth)->where('status','!=','annule')->sum('amount_paid');
        $expensesMonth = Expense::whereDate('expense_date','>=',$thisMonth)->sum('amount');
        return [
            'revenue_today'    => Order::whereDate('created_at',$today)->sum('amount_paid') + CustomOrder::whereDate('created_at',$today)->sum('amount_paid'),
            'revenue_month'    => $revenueMonth,
            'expenses_month'   => $expensesMonth,
            'profit_month'     => $revenueMonth - $expensesMonth,
            'clients_total'    => Client::count(),
            'clients_new'      => Client::whereDate('created_at','>=',$thisMonth)->count(),
            'orders_today'     => Order::whereDate('created_at',$today)->count(),
            'custom_pending'   => CustomOrder::whereNotIn('status',['livre','annule'])->count(),
        ];
    }
}
