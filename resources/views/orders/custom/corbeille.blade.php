@extends('layouts.app')
@section('title', 'Corbeille — Commandes Sur Mesure')

@section('breadcrumb')
    <a href="{{ route('custom-orders.index') }}" class="hover:text-gray-600 transition-colors">Sur mesure</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Corbeille</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-red-50 flex items-center justify-center">
                <i data-lucide="trash-2" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <h2 class="text-lg font-display font-bold text-dark">Corbeille</h2>
                <p class="text-xs text-gray-400">{{ $orders->total() }} commande(s) supprimée(s) — restaurables</p>
            </div>
        </div>
        <a href="{{ route('custom-orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </a>
    </div>

    {{-- Alerte info --}}
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-amber-500"></i>
        <p>Les commandes dans la corbeille peuvent être <strong>restaurées</strong> à tout moment. La <strong>suppression définitive</strong> est irréversible.</p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Vêtement</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Total</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Supprimée le</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-mono text-sm font-bold text-gray-400 line-through">{{ $order->reference }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-dark">{{ $order->client->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client->phone }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-dark">{{ ucfirst($order->garment_type) }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($order->gender) }}</p>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <p class="text-sm font-bold text-dark">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <p class="text-xs font-medium text-red-500">{{ $order->deleted_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $order->deleted_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Restaurer --}}
                                    <form method="POST" action="{{ route('custom-orders.restaurer', $order->id) }}">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-xs font-semibold hover:bg-green-100 transition-colors"
                                                title="Restaurer cette commande">
                                            <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
                                            Restaurer
                                        </button>
                                    </form>
                                    {{-- Supprimer définitivement --}}
                                    <form method="POST" action="{{ route('custom-orders.purger', $order->id) }}"
                                          onsubmit="return confirm('⚠️ Supprimer DÉFINITIVEMENT la commande {{ $order->reference }} ? Cette action est irréversible.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors"
                                                title="Supprimer définitivement">
                                            <i data-lucide="x-circle" style="width:13px;height:13px"></i>
                                            Supprimer définitivement
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="trash-2" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">La corbeille est vide</p>
                                <p class="text-sm mt-1">Aucune commande supprimée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection
