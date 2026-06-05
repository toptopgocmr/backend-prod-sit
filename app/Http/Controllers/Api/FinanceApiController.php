<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class FinanceApiController extends Controller {
    public function __construct(private FinanceService $financeService) {}
    public function report(Request $request) {
        return response()->json($this->financeService->getMonthlyReport($request->get('year',now()->year),$request->get('month',now()->month)));
    }
    public function expenses(Request $request) {
        return response()->json(Expense::with('category')->when($request->month,fn($q,$m)=>$q->whereMonth('expense_date',$m))->latest('expense_date')->paginate(20));
    }
    public function storeExpense(Request $request) {
        $expense = Expense::create(array_merge($request->validate(['expense_category_id'=>'required|exists:expense_categories,id','label'=>'required|string','amount'=>'required|numeric|min:1','payment_method'=>'required|in:cash,mobile_money,virement,credit','expense_date'=>'required|date']),['user_id'=>auth()->id()]));
        return response()->json($expense, 201);
    }
}
