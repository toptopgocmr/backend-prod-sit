<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Expense, ExpenseCategory};
use Illuminate\Http\Request;

class ExpenseController extends Controller {
    public function index(Request $request) {
        $query = Expense::with(['category','user'])->latest('expense_date');
        if ($request->filled('category')) $query->where('expense_category_id', $request->category);
        if ($request->filled('month'))    $query->whereMonth('expense_date', $request->month);
        $expenses   = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::all();
        $totalMonth = Expense::whereMonth('expense_date', now()->month)->sum('amount');
        return view('expenses.index', compact('expenses','categories','totalMonth'));
    }
    public function create() { return view('expenses.create', ['categories' => ExpenseCategory::all()]); }
    public function store(Request $request) {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'label'               => 'required|string|max:200',
            'amount'              => 'required|numeric|min:1',
            'payment_method'      => 'required|in:cash,mobile_money,virement,credit',
            'expense_date'        => 'required|date',
            'reference'           => 'nullable|string|max:100',
            'notes'               => 'nullable|string',
            'receipt_photo'       => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('expenses','public');
        }
        $validated['user_id'] = auth()->id();
        Expense::create($validated);
        return redirect()->route('expenses.index')->with('success','Dépense enregistrée.');
    }
    public function edit(Expense $expense) { return view('expenses.edit', ['expense'=>$expense,'categories'=>ExpenseCategory::all()]); }
    public function update(Request $request, Expense $expense) {
        $expense->update($request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'label'   => 'required|string|max:200',
            'amount'  => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,mobile_money,virement,credit',
            'expense_date'   => 'required|date',
        ]));
        return redirect()->route('expenses.index')->with('success','Dépense mise à jour.');
    }
    public function validateExpense(Expense $expense) {
        $expense->update(['is_validated'=>true,'validated_by'=>auth()->id()]);
        return back()->with('success','Dépense validée.');
    }
    public function destroy(Expense $expense) { $expense->delete(); return redirect()->route('expenses.index'); }
    public function show(Expense $expense)    { return view('expenses.show', compact('expense')); }
}
