<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{CustomOrder, User};
use Illuminate\Support\Facades\DB;

class AtelierController extends Controller {
    public function index() {
        $orders     = CustomOrder::with(['client','couturier'])->whereNotIn('status',['livre','annule'])->orderBy('delivery_date')->get();
        $couturiers = User::where('role','couturier')->where('is_active',true)->get();
        return view('atelier.index', compact('orders','couturiers'));
    }
    public function planning() {
        $orders = CustomOrder::with(['client','couturier'])->whereNotIn('status',['livre','annule'])->orderBy('delivery_date')->get();
        return view('atelier.planning', compact('orders'));
    }
    public function performance() {
        $perf = DB::table('custom_orders')->join('users','users.id','=','custom_orders.assigned_to')
            ->select('users.name',DB::raw('COUNT(*) as total'),DB::raw("SUM(CASE WHEN status='livre' THEN 1 ELSE 0 END) as completed"),DB::raw('SUM(labor_cost) as revenue'))
            ->whereMonth('custom_orders.created_at', now()->month)->groupBy('users.id','users.name')->get();
        return view('atelier.performance', compact('perf'));
    }
}
