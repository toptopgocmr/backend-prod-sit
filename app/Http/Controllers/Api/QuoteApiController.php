<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteApiController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::with(['client', 'creator', 'fabric'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->client_id, fn($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(30);

        return response()->json($quotes);
    }

    public function show(Quote $quote)
    {
        return response()->json($quote->load(['client', 'creator', 'fabric', 'customOrder']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'gender'             => 'nullable|string',
            'garment_type'       => 'nullable|string',
            'model_name'         => 'nullable|string',
            'model_description'  => 'nullable|string',
            'fabric_product_id'  => 'nullable|exists:products,id',
            'fabric_meters'      => 'nullable|numeric',
            'fabric_color'       => 'nullable|string',
            'fabric_cost'        => 'nullable|numeric',
            'labor_cost'         => 'nullable|numeric',
            'accessories_cost'   => 'nullable|numeric',
            'total'              => 'required|numeric',
            'valid_until'        => 'nullable|date',
            'delivery_date'      => 'nullable|date',
            'notes'              => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'brouillon';

        $quote = Quote::create($data);
        return response()->json($quote->load('client'), 201);
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'gender'            => 'nullable|string',
            'garment_type'      => 'nullable|string',
            'model_name'        => 'nullable|string',
            'model_description' => 'nullable|string',
            'fabric_cost'       => 'nullable|numeric',
            'labor_cost'        => 'nullable|numeric',
            'accessories_cost'  => 'nullable|numeric',
            'total'             => 'nullable|numeric',
            'valid_until'       => 'nullable|date',
            'delivery_date'     => 'nullable|date',
            'notes'             => 'nullable|string',
        ]);

        $quote->update($data);
        return response()->json($quote->load('client'));
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        $request->validate([
            'status' => 'required|in:brouillon,envoye,accepte,refuse,expire',
        ]);

        $quote->update(['status' => $request->status]);
        return response()->json($quote->load('client'));
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return response()->json(['success' => true]);
    }
}
