<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{SalaryPayment, User};
use Illuminate\Http\Request;

class SalaryController extends Controller {
    public function index(Request $request) {
        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', now()->month);
        $payments  = SalaryPayment::with('employee')->where('year',$year)->where('month',$month)->get();
        $employees = User::whereIn('role',['couturier','stock_manager','cashier','delivery'])->where('is_active',true)->get();
        $totalPaid = $payments->sum('net_amount');
        return view('finance.salaries', compact('payments','employees','year','month','totalPaid'));
    }
    public function store(Request $request) {
        $validated = $request->validate(['employee_id'=>'required|exists:users,id','base_salary'=>'required|numeric|min:0','bonus'=>'nullable|numeric|min:0','deduction'=>'nullable|numeric|min:0','payment_method'=>'required|in:cash,mobile_money,virement','paid_at'=>'required|date','month'=>'required|integer|min:1|max:12','year'=>'required|integer|min:2020']);
        $validated['paid_by']    = auth()->id();
        $validated['net_amount'] = $validated['base_salary'] + ($validated['bonus']??0) - ($validated['deduction']??0);
        SalaryPayment::create($validated);
        return redirect()->route('salaries.index')->with('success','Salaire enregistré.');
    }
    public function export(int $month, int $year) { return back()->with('error','Export en cours d\'implémentation.'); }
}
