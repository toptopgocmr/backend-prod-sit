<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $payments = SalaryPayment::with(['employee', 'paidBy'])
            ->where('year',  $year)
            ->where('month', $month)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'           => $p->id,
                    'employee_id'  => $p->employee_id,
                    'employee_name'=> $p->employee?->name,
                    'role'         => $p->employee?->role,
                    'gross_amount' => $p->base_salary + ($p->bonus ?? 0),
                    'deductions'   => $p->deduction ?? 0,
                    'net_amount'   => $p->net_amount,
                    'month'        => $p->month,
                    'year'         => $p->year,
                    'payment_method' => $p->payment_method,
                    'payment_date' => $p->paid_at?->toDateString(),
                    'is_paid'      => !is_null($p->paid_at),
                    'status'       => $p->paid_at ? 'paid' : 'pending',
                    'notes'        => $p->notes,
                    'base_salary'  => $p->base_salary,
                    'bonus'        => $p->bonus ?? 0,
                ];
            });

        return response()->json(['data' => $payments]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'     => 'required|exists:users,id',
            'base_salary'     => 'required|numeric',
            'bonus'           => 'nullable|numeric',
            'deduction'       => 'nullable|numeric',
            'net_amount'      => 'required|numeric',
            'month'           => 'required|integer|min:1|max:12',
            'year'            => 'required|integer',
            'payment_method'  => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        // Prevent duplicate entry for same employee/month/year
        $existing = SalaryPayment::where('employee_id', $data['employee_id'])
            ->where('month', $data['month'])
            ->where('year',  $data['year'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Un salaire existe déjà pour cet employé ce mois-ci.',
            ], 422);
        }

        $data['paid_by'] = auth()->id();
        $data['paid_at'] = now();

        $payment = SalaryPayment::create($data);
        return response()->json($payment->load('employee'), 201);
    }

    public function show(SalaryPayment $salary)
    {
        return response()->json($salary->load(['employee', 'paidBy']));
    }

    public function employees()
    {
        $employees = User::where('is_active', true)
            ->whereIn('role', ['employe', 'couturier', 'livreur', 'caissier', 'stock_manager'])
            ->select('id', 'name', 'role')
            ->get();

        return response()->json(['data' => $employees]);
    }
}
