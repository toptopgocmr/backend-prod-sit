<?php

namespace App\Services;

use App\Models\{Order, CustomOrder, Expense, Payment, SalaryPayment, PurchaseOrder, MaintenanceLog};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceService
{
    public function getMonthlyReport(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // Entrées
        $orderRevenue  = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')->sum('amount_paid');
        $customRevenue = CustomOrder::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'annule')->sum('amount_paid');
        $totalRevenue  = $orderRevenue + $customRevenue;

        // Sorties — opérations (toutes dépenses confondues, y compris achats stock)
        $expenses   = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $salaries   = SalaryPayment::where('month', $month)->where('year', $year)->sum('net_amount');

        // Détail achats stock (sous-ensemble des dépenses)
        $purchasesAmount = Expense::whereBetween('expense_date', [$start, $end])
            ->whereHas('category', fn($q) => $q->where('type', 'achat'))
            ->sum('amount');

        $totalExpenses = $expenses + $salaries;

        // Dépenses par catégorie (toutes, incluant achats)
        $expenseByCategory = DB::table('expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->select('expense_categories.name', 'expense_categories.color', 'expense_categories.type', DB::raw('SUM(expenses.amount) as total'))
            ->whereBetween('expenses.expense_date', [$start, $end])
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.color', 'expense_categories.type')
            ->orderByDesc('total')
            ->get();

        // Ventes par type
        $salesByType = [
            'Tissus'        => Order::whereBetween('created_at', [$start, $end])->where('type', 'tissu')->sum('amount_paid'),
            'Prêt-à-porter' => Order::whereBetween('created_at', [$start, $end])->where('type', 'pret_a_porter')->sum('amount_paid'),
            'Sur mesure'    => $customRevenue,
        ];

        // Commandes impayées (créances)
        $unpaidOrders  = Order::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum(DB::raw('total - amount_paid'));
        $unpaidCustom  = CustomOrder::where('payment_status', '!=', 'paid')->where('status', '!=', 'annule')->sum(DB::raw('total - amount_paid'));

        // Maintenance du mois
        $maintenanceCost = MaintenanceLog::whereBetween('created_at', [$start, $end])->sum('cost');

        return [
            'period'             => Carbon::create($year, $month)->translatedFormat('F Y'),
            'year'               => $year,
            'month'              => $month,
            'revenue'            => [
                'orders'  => $orderRevenue,
                'custom'  => $customRevenue,
                'total'   => $totalRevenue,
            ],
            'expenses'           => [
                'operations' => $expenses,
                'salaries'   => $salaries,
                'purchases'  => $purchasesAmount,
                'maintenance'=> $maintenanceCost,
                'total'      => $totalExpenses,
            ],
            'profit'             => $totalRevenue - $totalExpenses,
            'margin'             => $totalRevenue > 0 ? round((($totalRevenue - $totalExpenses) / $totalRevenue) * 100, 1) : 0,
            'expense_breakdown'  => $expenseByCategory,
            'sales_by_type'      => $salesByType,
            'unpaid_receivables' => $unpaidOrders + $unpaidCustom,
            'orders_count'       => Order::whereBetween('created_at', [$start, $end])->count(),
            'custom_count'       => CustomOrder::whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    public function getAnnualReport(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfDay();
            $end   = Carbon::create($year, $m, 1)->endOfMonth()->endOfDay();

            $revenue  = Order::whereBetween('created_at', [$start, $end])->where('status','!=','cancelled')->sum('amount_paid')
                      + CustomOrder::whereBetween('created_at', [$start, $end])->where('status','!=','annule')->sum('amount_paid');
            $expenses = Expense::whereBetween('expense_date', [$start, $end])->sum('amount')
                      + SalaryPayment::where('month', $m)->where('year', $year)->sum('net_amount');

            $months[] = [
                'month'    => $m,
                'label'    => Carbon::create($year, $m)->translatedFormat('M'),
                'revenue'  => $revenue,
                'expenses' => $expenses,
                'profit'   => $revenue - $expenses,
            ];
        }

        $totalRevenue  = collect($months)->sum('revenue');
        $totalExpenses = collect($months)->sum('expenses');

        return [
            'year'          => $year,
            'months'        => $months,
            'total_revenue' => $totalRevenue,
            'total_expenses'=> $totalExpenses,
            'total_profit'  => $totalRevenue - $totalExpenses,
        ];
    }

    public function getDailyRevenue(int $days = 30): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $result[] = [
                'date'    => $date,
                'label'   => Carbon::parse($date)->translatedFormat('d M'),
                'orders'  => Order::whereDate('created_at', $date)->where('status','!=','cancelled')->sum('amount_paid'),
                'custom'  => CustomOrder::whereDate('created_at', $date)->where('status','!=','annule')->sum('amount_paid'),
            ];
        }
        return $result;
    }

    public function recordPayment(string $payableType, int $payableId, int $clientId, float $amount, string $method, ?string $transactionId = null): Payment
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'reference'      => 'PAY-' . date('Ymd') . '-' . strtoupper(\Str::random(6)),
                'payable_type'   => $payableType,
                'payable_id'     => $payableId,
                'client_id'      => $clientId,
                'cashier_id'     => auth()->id(),
                'amount'         => $amount,
                'method'         => $method,
                'transaction_id' => $transactionId,
            ]);

            $payable = app($payableType)::find($payableId);
            $newPaid = $payable->amount_paid + $amount;
            $newStatus = $newPaid >= $payable->total ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
            $payable->update(['amount_paid' => $newPaid, 'payment_status' => $newStatus]);

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
