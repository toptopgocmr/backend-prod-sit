@extends('layouts.app')
@section('title', 'Dépenses')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">Total ce mois : <span class="font-bold text-dark">{{ number_format($totalMonth, 0, ',', ' ') }} FCFA</span></p>
        </div>
        <a href="{{ route('expenses.create') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle dépense
        </a>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <select name="category" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category')==$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous les mois</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month')==$m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Filtrer</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Libellé</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Catégorie</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Montant</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Méthode</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Validé</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-semibold text-dark">{{ $expense->label }}</p>
                                @if($expense->reference)
                                    <p class="text-xs text-gray-400 font-mono">{{ $expense->reference }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $expense->category->color ?? '#9CA3AF' }}"></div>
                                    <span class="text-sm text-gray-600">{{ $expense->category->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">
                                {{ number_format($expense->amount, 0, ',', ' ') }} <span class="text-xs text-gray-400 font-normal">FCFA</span>
                            </td>
                            <td class="px-5 py-3.5 text-center text-xs text-gray-500">
                                {{ match($expense->payment_method) { 'cash'=>'💵 Espèces','mobile_money'=>'📱 Mobile Money','virement'=>'🏦 Virement',default=>'💳 Crédit' } }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($expense->is_validated)
                                    <span class="badge-status bg-green-50 text-green-700">✓ Validé</span>
                                @else
                                    <form method="POST" action="{{ route('expenses.validate', $expense) }}" class="inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="badge-status bg-yellow-50 text-yellow-700 cursor-pointer hover:bg-yellow-100">En attente</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right text-xs text-gray-400">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Supprimer cette dépense ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600">
                                            <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p>Aucune dépense enregistrée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $expenses->links() }}</div>
        @endif
    </div>

</div>
@endsection
