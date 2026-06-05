<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller {
    public function __construct(private FinanceService $financeService) {}
    public function index() {
        $report = $this->financeService->getMonthlyReport(now()->year, now()->month);
        return view('finance.index', compact('report'));
    }
    public function monthlyReport(Request $request, ?int $year = null, ?int $month = null) {
        $year  = $year  ?? $request->get('year',  now()->year);
        $month = $month ?? $request->get('month', now()->month);
        $report = $this->financeService->getMonthlyReport($year, $month);
        return view('finance.index', compact('report'));
    }
    public function cashflow() {
        $data = $this->financeService->getDailyRevenue(30);
        return view('finance.cashflow', compact('data'));
    }
}
