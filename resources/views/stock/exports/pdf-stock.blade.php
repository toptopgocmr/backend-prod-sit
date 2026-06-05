<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; background: #fff; }

    .header {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .header-title { font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
    .header-meta  { font-size: 9px; opacity: 0.85; text-align: right; }

    .stats-grid {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
    }
    .stat-card {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        text-align: center;
    }
    .stat-value  { font-size: 18px; font-weight: 700; }
    .stat-label  { font-size: 8.5px; color: #6b7280; margin-top: 2px; }
    .stat-orange { color: #f97316; }
    .stat-red    { color: #ef4444; }
    .stat-green  { color: #10b981; }
    .stat-dark   { color: #111827; }

    table { width: 100%; border-collapse: collapse; }
    thead tr {
        background: #374151;
        color: white;
    }
    thead th {
        padding: 7px 8px;
        text-align: left;
        font-size: 8.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }

    tbody tr { border-bottom: 1px solid #f3f4f6; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody tr:hover { background: #fff7ed; }

    td { padding: 6px 8px; font-size: 9.5px; }
    td.right  { text-align: right; }
    td.center { text-align: center; }
    td.mono   { font-family: 'Courier New', monospace; font-size: 8.5px; color: #6b7280; }

    .badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 20px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-ok      { background: #dcfce7; color: #166534; }
    .badge-low     { background: #fef3c7; color: #92400e; }
    .badge-rupture { background: #fee2e2; color: #991b1b; }
    .badge-tissu   { background: #dbeafe; color: #1e40af; }
    .badge-pap     { background: #ede9fe; color: #6d28d9; }

    .product-name { font-weight: 600; color: #111827; font-size: 9.5px; }
    .product-ref  { font-size: 8px; color: #9ca3af; font-family: 'Courier New', monospace; }

    .progress-wrap { display: flex; align-items: center; gap: 5px; }
    .progress-bar  { flex: 1; height: 5px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 10px; }
    .fill-ok       { background: #10b981; }
    .fill-low      { background: #f59e0b; }
    .fill-zero     { background: #ef4444; }
    .pct-text      { font-size: 8px; font-weight: 700; min-width: 28px; text-align: right; }

    .footer {
        margin-top: 16px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        font-size: 8px;
        color: #9ca3af;
        display: flex;
        justify-content: space-between;
    }

    .section-title {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        padding-bottom: 4px;
        border-bottom: 2px solid #f97316;
        display: inline-block;
    }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div>
        <div class="header-title">📦 Rapport de Stock</div>
        <div style="font-size:9px;opacity:0.8;margin-top:2px;">État complet du stock — tous produits actifs</div>
    </div>
    <div class="header-meta">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        {{ $stats['total'] }} produit(s) actif(s)
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value stat-dark">{{ $stats['total'] }}</div>
        <div class="stat-label">Produits actifs</div>
    </div>
    <div class="stat-card">
        <div class="stat-value stat-orange">{{ $stats['low_stock'] }}</div>
        <div class="stat-label">Stock faible</div>
    </div>
    <div class="stat-card">
        <div class="stat-value stat-red">{{ $stats['rupture'] }}</div>
        <div class="stat-label">Ruptures</div>
    </div>
    <div class="stat-card">
        <div class="stat-value stat-green" style="font-size:13px;">{{ number_format($stats['total_value'], 0, ',', ' ') }}</div>
        <div class="stat-label">Valeur totale (FCFA)</div>
    </div>
</div>

{{-- Tableau --}}
<div class="section-title">Détail du stock</div>
<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>Type</th>
            <th>Catégorie</th>
            <th class="right">Stock actuel</th>
            <th class="right">Seuil alerte</th>
            <th class="center">Niveau</th>
            <th class="center">État</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        @php
            $isTissu = $product->type === 'tissu';
            $stock   = $isTissu ? $product->available_meters : $product->stock_quantity;
            $unit    = $isTissu ? 'm' : 'pcs';
            $seuil   = $product->alert_threshold ?? 0;
            $pct     = $seuil > 0 ? min(100, round(($stock / $seuil) * 100)) : 100;
            $isZero  = $stock <= 0;
            $isLow   = !$isZero && $seuil > 0 && $stock < $seuil;
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
            <td style="color:#4b5563;">{{ $product->category->name ?? '—' }}</td>
            <td class="right" style="font-weight:700; color:{{ $isZero ? '#dc2626' : ($isLow ? '#d97706' : '#111827') }}">
                {{ number_format($stock, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
            </td>
            <td class="right mono">{{ $seuil ?: '—' }} {{ $seuil ? $unit : '' }}</td>
            <td class="center">
                <div class="progress-wrap">
                    <div class="progress-bar">
                        <div class="progress-fill {{ $isZero ? 'fill-zero' : ($isLow ? 'fill-low' : 'fill-ok') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="pct-text" style="color:{{ $isZero ? '#dc2626' : ($isLow ? '#d97706' : '#059669') }}">{{ $pct }}%</span>
                </div>
            </td>
            <td class="center">
                @if($isZero)
                    <span class="badge badge-rupture">Rupture</span>
                @elseif($isLow)
                    <span class="badge badge-low">Faible</span>
                @else
                    <span class="badge badge-ok">OK</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <span>Rapport généré automatiquement — Confidentiel</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
