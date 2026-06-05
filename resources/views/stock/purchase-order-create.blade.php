@extends('layouts.app')
@section('title', 'Nouveau bon de commande')

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-gray-600 transition-colors">Stock</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <a href="{{ route('purchase-orders.index') }}" class="hover:text-gray-600 transition-colors">Bons de commande</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau</span>
@endsection

@section('content')
<div class="space-y-5" x-data="purchaseOrderForm()">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Nouveau bon de commande</h2>
                <p class="text-xs text-gray-400">Commande fournisseur avec entrée en stock automatique</p>
            </div>
        </div>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Retour
        </a>
    </div>

    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-5">
        @csrf

        {{-- Infos générales --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h3 class="font-display font-bold text-dark text-sm flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                Informations fournisseur
            </h3>
            <div>
                <label class="block text-sm font-semibold text-dark mb-1.5">Nom du fournisseur <span class="text-red-500">*</span></label>
                <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" required
                       placeholder="Ex: Tissus Import SA, Marché Total..."
                       class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('supplier_name') border-red-400 @enderror">
                @error('supplier_name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Lignes de commande --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-display font-bold text-dark text-sm flex items-center gap-2">
                    <i data-lucide="package" class="w-4 h-4 text-gray-400"></i>
                    Articles commandés
                </h3>
                <button type="button" @click="addLine()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 transition-colors">
                    <i data-lucide="plus" style="width:13px;height:13px"></i>
                    Ajouter un article
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(line, index) in lines" :key="index">
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        {{-- Produit --}}
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Produit</label>
                            <select :name="`items[${index}][product_id]`" x-model="line.product_id"
                                    @change="onProductChange(index)"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                <option value="">— Choisir —</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-unit="{{ $product->type === 'tissu' ? 'm' : 'pcs' }}"
                                            data-cost="{{ $product->cost_price ?? 0 }}">
                                        {{ $product->name }} ({{ $product->reference }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Quantité --}}
                        <div class="w-32">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">
                                Quantité (<span x-text="line.unit || 'pcs'"></span>)
                            </label>
                            <input type="number" :name="`items[${index}][quantity_ordered]`"
                                   x-model="line.quantity" @input="calcTotal()"
                                   min="0.01" step="0.01" placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        {{-- Coût unitaire --}}
                        <div class="w-36">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Coût unitaire (FCFA)</label>
                            <input type="number" :name="`items[${index}][unit_cost]`"
                                   x-model="line.unit_cost" @input="calcTotal()"
                                   min="0" step="1" placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        {{-- Sous-total --}}
                        <div class="w-32 text-right">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Sous-total</label>
                            <p class="text-sm font-bold text-dark" x-text="formatMoney(subtotal(line))"></p>
                        </div>
                        {{-- Supprimer --}}
                        <button type="button" @click="removeLine(index)"
                                x-show="lines.length > 1"
                                class="mt-4 p-1.5 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 transition-colors">
                            <i data-lucide="x" style="width:15px;height:15px"></i>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Total général --}}
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <div class="text-right">
                    <p class="text-xs text-gray-400 mb-0.5">Montant total estimé</p>
                    <p class="text-2xl font-display font-bold text-dark" x-text="formatMoney(total)"></p>
                    <p class="text-xs text-gray-400">FCFA</p>
                </div>
            </div>
        </div>

        {{-- Boutons --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}"
               class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                <i data-lucide="save" style="width:15px;height:15px"></i>
                Créer le bon de commande
            </button>
        </div>

    </form>
</div>

<script>
function purchaseOrderForm() {
    return {
        lines: [{ product_id: '', quantity: '', unit_cost: '', unit: 'pcs' }],
        total: 0,

        init() {
            // Recalcule réactivement à chaque modification d'une ligne
            this.$watch('lines', () => this.calcTotal(), { deep: true });
            this.calcTotal();
        },

        addLine() {
            this.lines.push({ product_id: '', quantity: '', unit_cost: '', unit: 'pcs' });
        },
        removeLine(i) {
            this.lines.splice(i, 1);
            this.calcTotal();
        },

        onProductChange(i) {
            const sel = document.querySelectorAll('select[name^="items"]')[i];
            const opt = sel?.options[sel.selectedIndex];
            if (opt) {
                this.lines[i].unit      = opt.dataset.unit || 'pcs';
                // parseInt évite "8500.00" — garantit un Number propre pour Alpine
                this.lines[i].unit_cost = parseInt(opt.dataset.cost, 10) || 0;
            }
            this.calcTotal();
        },

        subtotal(line) {
            return (parseFloat(line.quantity) || 0) * (parseFloat(line.unit_cost) || 0);
        },

        calcTotal() {
            this.total = this.lines.reduce((s, l) => s + this.subtotal(l), 0);
        },

        formatMoney(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0));
        },
    }
}
</script>
@endsection
