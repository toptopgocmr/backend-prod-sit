<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, CustomOrder, Expense, SalaryPayment, Product, StockMovement, MaintenanceLog, PurchaseOrder};
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $year   = (int) $request->get('year',  now()->year);
        $month  = (int) $request->get('month', now()->month);

        [$start, $end, $prevStart, $prevEnd] = $this->resolveDates($period, $year, $month);

        // ── Ventes ──
        $salesData = $this->getSalesData($start, $end, $prevStart, $prevEnd);

        // ── Stock ──
        $stockData = $this->getStockData($start, $end);

        // ── Dépenses ──
        $expenseData = $this->getExpenseData($start, $end);

        // ── Maintenance ──
        $maintenanceData = $this->getMaintenanceData($start, $end);

        // ── Finance annuelle (pour graphique) ──
        $annualData = $period === 'year'
            ? $this->financeService->getAnnualReport($year)
            : null;

        return view('reports.index', compact(
            'period','year','month',
            'salesData','stockData','expenseData','maintenanceData','annualData',
            'start','end'
        ));
    }

    public function export(Request $request)
    {
        $type   = $request->get('type', 'full');
        $period = $request->get('period', 'month');
        $year   = (int) $request->get('year',  now()->year);
        $month  = (int) $request->get('month', now()->month);
        $format = $request->get('format', 'pdf');

        [$start, $end] = $this->resolveDates($period, $year, $month);

        $data = [
            'period_label' => $this->periodLabel($period, $year, $month),
            'generated_at' => now()->format('d/m/Y H:i'),
            'type'         => $type,
        ];

        if (in_array($type, ['full','sales'])) {
            $data['sales'] = $this->getSalesData($start, $end, $start, $end);
        }
        if (in_array($type, ['full','stock'])) {
            $data['stock'] = $this->getStockData($start, $end);
        }
        if (in_array($type, ['full','expenses'])) {
            $data['expenses'] = $this->getExpenseData($start, $end);
        }
        if (in_array($type, ['full','maintenance'])) {
            $data['maintenance'] = $this->getMaintenanceData($start, $end);
        }
        if (in_array($type, ['full','finance'])) {
            $data['finance'] = $this->financeService->getMonthlyReport($year, $month);
        }

        $pdf = Pdf::loadView('reports.export-pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'rapport-' . $type . '-' . $period . '-' . $year . ($period === 'month' ? '-' . str_pad($month, 2, '0', STR_PAD_LEFT) : '') . '.pdf';

        return $pdf->download($filename);
    }

    // ─── Données Ventes ──────────────────────────────────────────────────────
    private function getSalesData(Carbon $start, Carbon $end, Carbon $prevStart, Carbon $prevEnd): array
    {
        $revenue = Order::whereBetween('created_at', [$start, $end])->where('status','!=','cancelled')->sum('amount_paid')
                 + CustomOrder::whereBetween('created_at', [$start, $end])->where('status','!=','annule')->sum('amount_paid');

        $prevRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status','!=','cancelled')->sum('amount_paid')
                    + CustomOrder::whereBetween('created_at', [$prevStart, $prevEnd])->where('status','!=','annule')->sum('amount_paid');

        $ordersCount = Order::whereBetween('created_at', [$start, $end])->where('status','!=','cancelled')->count();
        $customCount = CustomOrder::whereBetween('created_at', [$start, $end])->where('status','!=','annule')->count();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.quantity * order_items.unit_price) as total'))
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id','products.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'revenue'      => $revenue,
            'prev_revenue' => $prevRevenue,
            'evolution'    => $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : null,
            'orders_count' => $ordersCount,
            'custom_count' => $customCount,
            'total_orders' => $ordersCount + $customCount,
            'top_products' => $topProducts,
            'by_type'      => [
                'Tissus'        => Order::whereBetween('created_at', [$start, $end])->where('type','tissu')->sum('amount_paid'),
                'Prêt-à-porter' => Order::whereBetween('created_at', [$start, $end])->where('type','pret_a_porter')->sum('amount_paid'),
                'Sur mesure'    => CustomOrder::whereBetween('created_at', [$start, $end])->where('status','!=','annule')->sum('amount_paid'),
            ],
        ];
    }

    // ─── Données Stock ───────────────────────────────────────────────────────
    private function getStockData(Carbon $start, Carbon $end): array
    {
        $totalValue    = DB::table('products')->where('is_active', true)->sum(DB::raw('stock_quantity * cost_price'));
        $lowStockCount = DB::table('products')->whereRaw('stock_quantity <= alert_threshold')->where('is_active', true)->count();
        $outOfStock    = DB::table('products')->where('stock_quantity', 0)->where('is_active', true)->count();

        $movements = StockMovement::with('product')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('type, COUNT(*) as count, SUM(ABS(quantity)) as total_qty')
            ->groupBy('type')
            ->get();

        $purchases = PurchaseOrder::whereBetween('created_at', [$start, $end])->where('status', 'received')->get();

        $topMoving = DB::table('stock_movements')
            ->join('products','products.id','=','stock_movements.product_id')
            ->select('products.name', DB::raw('SUM(ABS(stock_movements.quantity)) as total_mvt'))
            ->whereBetween('stock_movements.created_at', [$start, $end])
            ->groupBy('products.id','products.name')
            ->orderByDesc('total_mvt')
            ->limit(8)
            ->get();

        return [
            'total_value'    => $totalValue,
            'low_stock'      => $lowStockCount,
            'out_of_stock'   => $outOfStock,
            'movements'      => $movements,
            'purchases'      => $purchases,
            'purchases_total'=> $purchases->sum('total_amount'),
            'top_moving'     => $topMoving,
        ];
    }

    // ─── Données Dépenses ────────────────────────────────────────────────────
    private function getExpenseData(Carbon $start, Carbon $end): array
    {
        $total    = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $salaries = SalaryPayment::where('year', $start->year)->where('month', $start->month)->sum('net_amount');
        $purchases= Expense::whereBetween('expense_date', [$start, $end])
            ->whereHas('category', fn($q) => $q->where('type','achat'))
            ->sum('amount');

        $byCategory = DB::table('expenses')
            ->join('expense_categories','expense_categories.id','=','expenses.expense_category_id')
            ->select('expense_categories.name','expense_categories.color', DB::raw('SUM(expenses.amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereBetween('expenses.expense_date', [$start, $end])
            ->groupBy('expense_categories.id','expense_categories.name','expense_categories.color')
            ->orderByDesc('total')
            ->get();

        $recent = Expense::with('category')
            ->whereBetween('expense_date', [$start, $end])
            ->orderByDesc('expense_date')
            ->limit(15)
            ->get();

        return [
            'total'        => $total + $salaries,
            'operations'   => $total,
            'salaries'     => $salaries,
            'purchases'    => $purchases,
            'by_category'  => $byCategory,
            'recent'       => $recent,
        ];
    }

    // ─── Données Maintenance ─────────────────────────────────────────────────
    private function getMaintenanceData(Carbon $start, Carbon $end): array
    {
        $logs = MaintenanceLog::with('equipment')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return [
            'count'       => $logs->count(),
            'total_cost'  => $logs->sum('cost'),
            'resolved'    => $logs->where('status', 'resolved')->count(),
            'pending'     => $logs->whereIn('status', ['open','in_progress'])->count(),
            'logs'        => $logs->sortByDesc('created_at')->take(15)->values(),
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function resolveDates(string $period, int $year, int $month): array
    {
        if ($period === 'year') {
            $start     = Carbon::create($year, 1, 1)->startOfDay();
            $end       = Carbon::create($year, 12, 31)->endOfDay();
            $prevStart = Carbon::create($year - 1, 1, 1)->startOfDay();
            $prevEnd   = Carbon::create($year - 1, 12, 31)->endOfDay();
        } elseif ($period === 'week') {
            $start     = Carbon::now()->startOfWeek();
            $end       = Carbon::now()->endOfWeek();
            $prevStart = $start->copy()->subWeek();
            $prevEnd   = $end->copy()->subWeek();
        } else { // month (default)
            $start     = Carbon::create($year, $month, 1)->startOfDay();
            $end       = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            $prevMonth = Carbon::create($year, $month, 1)->subMonth();
            $prevStart = $prevMonth->copy()->startOfMonth()->startOfDay();
            $prevEnd   = $prevMonth->copy()->endOfMonth()->endOfDay();
        }
        return [$start, $end, $prevStart, $prevEnd];
    }

    private function periodLabel(string $period, int $year, int $month): string
    {
        if ($period === 'year') return "Année $year";
        if ($period === 'week') return "Semaine du " . now()->startOfWeek()->format('d/m/Y');
        return Carbon::create($year, $month)->translatedFormat('F Y');
    }
}
