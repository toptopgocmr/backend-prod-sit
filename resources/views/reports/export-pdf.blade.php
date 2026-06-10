<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; }
        .header { background: #f97316; color: white; padding: 20px 25px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 10px; opacity: 0.85; margin-top: 3px; }
        .section { margin-bottom: 20px; padding: 0 15px; }
        .section-title { font-size: 12px; font-weight: bold; color: #f97316; border-bottom: 2px solid #f97316; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 12px; }
        .kpi { background: #f8f8f8; border: 1px solid #e5e5e5; border-radius: 6px; padding: 8px; text-align: center; }
        .kpi-value { font-size: 13px; font-weight: bold; color: #1a1a1a; }
        .kpi-label { font-size: 8px; color: #888; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #f3f3f3; padding: 5px 8px; text-align: left; font-weight: bold; color: #555; border-bottom: 1px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; }
        tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }
        .highlight { color: #f97316; font-weight: bold; }
        .red { color: #dc2626; }
        .green { color: #16a34a; }
        .footer { text-align: center; font-size: 8px; color: #aaa; padding: 15px; border-top: 1px solid #eee; margin-top: 20px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

<div class="header">
    <h1>📊 Rapport GSIT — {{ $period_label }}</h1>
    <p>Généré le {{ $generated_at }} · Type : {{ ucfirst($type) }}</p>
</div>

@if(isset($sales))
<div class="section">
    <div class="section-title">Ventes & Commandes</div>
    <div class="kpi-grid">
        <div class="kpi"><div class="kpi-value highlight">{{ number_format($sales['revenue'], 0, ',', ' ') }}</div><div class="kpi-label">CA (FCFA)</div></div>
        <div class="kpi"><div class="kpi-value">{{ $sales['orders_count'] }}</div><div class="kpi-label">Commandes</div></div>
        <div class="kpi"><div class="kpi-value">{{ $sales['custom_count'] }}</div><div class="kpi-label">Sur mesure</div></div>
        <div class="kpi"><div class="kpi-value {{ ($sales['evolution'] ?? 0) >= 0 ? 'green' : 'red' }}">{{ $sales['evolution'] !== null ? (($sales['evolution'] >= 0 ? '+' : '') . $sales['evolution'] . '%') : 'N/A' }}</div><div class="kpi-label">Évolution</div></div>
    </div>
    @if($sales['top_products']->count())
    <table>
        <thead><tr><th>Produit</th><th class="text-right">Qté</th><th class="text-right">Total (FCFA)</th></tr></thead>
        <tbody>
        @foreach($sales['top_products'] as $p)
            <tr><td>{{ $p->name }}</td><td class="text-right">{{ $p->qty }}</td><td class="text-right">{{ number_format($p->total, 0, ',', ' ') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

@if(isset($stock))
<div class="section">
    <div class="section-title">Stock</div>
    <div class="kpi-grid">
        <div class="kpi"><div class="kpi-value">{{ number_format($stock['total_value'], 0, ',', ' ') }}</div><div class="kpi-label">Valeur stock (FCFA)</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($stock['purchases_total'], 0, ',', ' ') }}</div><div class="kpi-label">Achats période (FCFA)</div></div>
        <div class="kpi"><div class="kpi-value red">{{ $stock['low_stock'] }}</div><div class="kpi-label">Stock faible</div></div>
        <div class="kpi"><div class="kpi-value red">{{ $stock['out_of_stock'] }}</div><div class="kpi-label">Ruptures</div></div>
    </div>
</div>
@endif

@if(isset($expenses))
<div class="section">
    <div class="section-title">Dépenses</div>
    <div class="kpi-grid">
        <div class="kpi"><div class="kpi-value red">{{ number_format($expenses['total'], 0, ',', ' ') }}</div><div class="kpi-label">Total (FCFA)</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($expenses['operations'], 0, ',', ' ') }}</div><div class="kpi-label">Opérations</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($expenses['salaries'], 0, ',', ' ') }}</div><div class="kpi-label">Salaires</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($expenses['purchases'], 0, ',', ' ') }}</div><div class="kpi-label">Achats stock</div></div>
    </div>
    @if($expenses['by_category']->count())
    <table>
        <thead><tr><th>Catégorie</th><th class="text-right">Nb</th><th class="text-right">Montant (FCFA)</th></tr></thead>
        <tbody>
        @foreach($expenses['by_category'] as $cat)
            <tr><td>{{ $cat->name }}</td><td class="text-right">{{ $cat->count }}</td><td class="text-right">{{ number_format($cat->total, 0, ',', ' ') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

@if(isset($maintenance))
<div class="section">
    <div class="section-title">Maintenance</div>
    <div class="kpi-grid">
        <div class="kpi"><div class="kpi-value">{{ number_format($maintenance['total_cost'], 0, ',', ' ') }}</div><div class="kpi-label">Coût (FCFA)</div></div>
        <div class="kpi"><div class="kpi-value">{{ $maintenance['count'] }}</div><div class="kpi-label">Interventions</div></div>
        <div class="kpi"><div class="kpi-value green">{{ $maintenance['resolved'] }}</div><div class="kpi-label">Résolues</div></div>
        <div class="kpi"><div class="kpi-value red">{{ $maintenance['pending'] }}</div><div class="kpi-label">En attente</div></div>
    </div>
</div>
@endif

<div class="footer">GSIT — Gestion Interne · Rapport généré automatiquement le {{ $generated_at }}</div>
</body>
</html>
