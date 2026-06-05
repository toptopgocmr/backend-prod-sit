<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; background: #fff; }

    .header {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        padding: 14px 20px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-title { font-size: 15px; font-weight: 700; }
    .header-sub   { font-size: 9px; opacity: 0.85; margin-top: 3px; }
    .header-meta  { font-size: 9px; opacity: 0.85; text-align: right; }

    .alert-banner {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 9.5px;
        color: #92400e;
        font-weight: 600;
    }

    .summary-grid { display: flex; gap: 10px; margin-bottom: 14px; }
    .sum-card {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 14px;
        text-align: center;
    }
    .sum-value { font-size: 20px; font-weight: 700; }
    .sum-label { font-size: 8.5px; color: #6b7280; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #374151; color: white; }
    thead th { padding: 7px 8px; text-align: left; font-size: 8.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }

    tbody tr { border-bottom: 1px solid #f3f4f6; }
    tbody tr:nth-child(even) { background: #fff7f7; }
    td { padding: 7px 8px; font-size: 9.5px; }
    td.right { text-align: right; }
    td.center { text-align: center; }

    .product-name { font-weight: 600; color: #111827; }
    .product-ref  { font-size: 8px; color: #9ca3af; font-family: 'Courier New', monospace; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 8px; font-weight: 700; text-transform: uppercase; }
    .badge-rupture { background: #fee2e2; color: #991b1b; }
    .badge-low     { background: #fef3c7; color: #92400e; }
    .badge-tissu   { background: #dbeafe; color: #1e40af; }
    .badge-pap     { background: #ede9fe; color: #6d28d9; }

    .progress-wrap { display: flex; align-items: center; gap: 5px; }
    .progress-bar  { flex: 1; height: 6px; background: #fee2e2; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 10px; }
    .fill-zero { background: #ef4444; }
    .fill-low  { background: #f59e0b; }

    .action-note {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 14px;
        font-size: 9px;
        color: #166534;
    }

    .footer {
        margin-top: 16px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        font-size: 8px;
        color: #9ca3af;
        display: flex;
        justify-content: space-between;
    }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-title">⚠️ Alertes Stock Faible</div>
        <div class="header-sub">Produits en dessous du seuil d'alerte — Action requise</div>
    </div>
    <div class="header-meta">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        {{ $products->count() }} produit(s) concerné(s)
    </div>
</div>

@if($products->isEmpty())
    <div style="text-align:center; padding: 40px; color: #16a34a; font-weight: 700; font-size: 13px;">
        ✅ Tous les stocks sont suffisants — Aucune alerte active
    </div>
@else

@php
    $tissus = $products->where('type', 'tissu');
    $pap    = $products->where('type', '!=', 'tissu');
    $ruptures = $products->filter(fn($p) => ($p->type === 'tissu' ? $p->available_meters : $p->stock_quantity) <= 0);
@endphp

<div class="alert-banner">
    ⚠️ {{ $products->count() }} produit(s) nécessitent un réapprovisionnement immédiat —
    dont {{ $ruptures->count() }} en rupture totale de stock.
</div>

<div class="summary-grid">
    <div class="sum-card">
        <div class="sum-value" style="color:#f97316">{{ $tissus->count() }}</div>
        <div class="sum-label">Tissu(s) en alerte</div>
    </div>
    <div class="sum-card">
        <div class="sum-value" style="color:#8b5cf6">{{ $pap->count() }}</div>
        <div class="sum-label">Article(s) en alerte</div>
    </div>
    <div class="sum-card">
        <div class="sum-value" style="color:#ef4444">{{ $ruptures->count() }}</div>
        <div class="sum-label">Ruptures totales</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>Type</th>
            <th class="right">Stock actuel</th>
            <th class="right">Seuil alerte</th>
            <th class="right">Manque</th>
            <th class="center">Niveau</th>
            <th class="center">Urgence</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products->sortBy(fn($p) => $p->type === 'tissu' ? $p->available_meters : $p->stock_quantity) as $product)
        @php
            $isTissu = $product->type === 'tissu';
            $stock   = $isTissu ? $product->available_meters : $product->stock_quantity;
            $unit    = $isTissu ? 'm' : 'pcs';
            $seuil   = $product->alert_threshold ?? 0;
            $manque  = max(0, $seuil - $stock);
            $pct     = $seuil > 0 ? min(100, round(($stock / $seuil) * 100)) : 0;
            $isZero  = $stock <= 0;
        @endphp
        <tr>
            <td>
                <div class="product-name">{{ $product->name }}</div>
                <div class="product-ref">{{ $product->reference }}</div>
            </td>
            <td>
                <span class="badge {{ $isTissu ? 'badge-tissu' : 'badge-pap' }}">
                    {{ $isTissu ? 'Tissu' : 'Article' }}
                </span>
            </td>
            <td class="right" style="font-weight:700; color:{{ $isZero ? '#dc2626' : '#d97706' }}">
                {{ number_format($stock, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                @if($isZero) <br><span style="font-size:8px;color:#dc2626;font-weight:700;">RUPTURE</span> @endif
            </td>
            <td class="right" style="color:#6b7280;">{{ $seuil ?: '—' }} {{ $seuil ? $unit : '' }}</td>
            <td class="right" style="color:#ef4444; font-weight:700;">
                {{ $manque > 0 ? '+' . number_format($manque, $isTissu ? 1 : 0, ',', ' ') . ' ' . $unit : '—' }}
            </td>
            <td class="center">
                <div class="progress-wrap">
                    <div class="progress-bar">
                        <div class="progress-fill {{ $isZero ? 'fill-zero' : 'fill-low' }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                    <span style="font-size:8px;font-weight:700;color:{{ $isZero ? '#dc2626' : '#d97706' }};min-width:28px;text-align:right">{{ $pct }}%</span>
                </div>
            </td>
            <td class="center">
                @if($isZero)
                    <span class="badge badge-rupture">🔴 Critique</span>
                @else
                    <span class="badge badge-low">🟡 Urgent</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="action-note">
    ✅ <strong>Actions recommandées :</strong>
    Contacter les fournisseurs pour les produits en rupture en priorité.
    Planifier les réapprovisionnements selon les délais de livraison habituels.
    Ce rapport peut être transmis directement au responsable des achats.
</div>

@endif

<div class="footer">
    <span>Document confidentiel — Usage interne uniquement</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
