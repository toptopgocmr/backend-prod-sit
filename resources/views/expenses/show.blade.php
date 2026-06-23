@extends('layouts.app')
@section('title', 'Détail dépense')

@section('breadcrumb')
    <a href="{{ route('expenses.index') }}" class="hover:text-gray-600 transition-colors">Dépenses</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $expense->label }}</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">{{ $expense->label }}</h2>
                <p class="text-xs text-gray-400">Enregistrée le {{ $expense->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('expenses.edit', $expense) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 transition-colors">
                <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                Modifier
            </a>
            @endif
            <a href="{{ route('expenses.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                Retour
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Libellé</p>
                <p class="text-sm font-semibold text-dark">{{ $expense->label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Montant</p>
                <p class="text-xl font-bold text-primary">{{ number_format($expense->amount, 0, ',', ' ') }} FCFA</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Catégorie</p>
                <p class="text-sm text-dark">{{ $expense->category->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Méthode de paiement</p>
                <p class="text-sm text-dark">{{ match($expense->payment_method) {
                    'cash' => 'Espèces',
                    'mobile_money' => 'Mobile Money',
                    'virement' => 'Virement bancaire',
                    'credit' => 'Crédit',
                    default => $expense->payment_method
                } }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Date</p>
                <p class="text-sm text-dark">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Statut</p>
                @if($expense->is_validated)
                    <span class="badge-status bg-green-50 text-green-700">Validée</span>
                @else
                    <span class="badge-status bg-yellow-50 text-yellow-700">En attente de validation</span>
                @endif
            </div>
            @if($expense->reference)
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Référence</p>
                <p class="text-sm font-mono text-dark">{{ $expense->reference }}</p>
            </div>
            @endif
            @if($expense->notes)
            <div class="md:col-span-2">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Notes</p>
                <p class="text-sm text-dark">{{ $expense->notes }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Enregistrée par</p>
                <p class="text-sm text-dark">{{ $expense->user->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    @if(!$expense->is_validated && auth()->user()->isAdmin())
    <div class="flex justify-end">
        <form method="POST" action="{{ route('expenses.validate', $expense) }}">
            @csrf @method('PUT')
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                <i data-lucide="check-circle" style="width:15px;height:15px"></i>
                Valider cette dépense
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
