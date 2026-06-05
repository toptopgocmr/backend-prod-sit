<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class ReportController extends Controller {
    public function index()   { return view('reports.index'); }
    public function sales()   { return view('reports.sales'); }
    public function stock()   { return view('reports.stock'); }
    public function clients() { return view('reports.clients'); }
    public function finance() { return view('reports.finance'); }
    public function export(string $type) { return back()->with('error','Export en cours d\'implémentation.'); }
}
