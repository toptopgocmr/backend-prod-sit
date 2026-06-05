<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1A1A2E; background: #fff; }

    .header { padding: 20px 24px 16px; border-bottom: 3px solid #E8820C; display: flex; justify-content: space-between; align-items: flex-start; }
    .logo-block { display: flex; align-items: center; gap: 12px; }
    .logo-block img { width: 48px; height: 48px; border-radius: 10px; object-fit: contain; }
    .logo-block .company { font-size: 20px; font-weight: 700; color: #1A1A2E; letter-spacing: 1px; }
    .logo-block .subtitle { font-size: 9px; color: #E8820C; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; }
    .meta { text-align: right; }
    .meta .title { font-size: 16px; font-weight: 700; color: #E8820C; }
    .meta .date { font-size: 9px; color: #888; margin-top: 4px; }

    .filters { padding: 8px 24px; background: #FEF3E2; font-size: 9px; color: #9E5806; }

    .stats { display: flex; gap: 0; border-bottom: 1px solid #eee; }
    .stat { flex: 1; padding: 10px 16px; border-right: 1px solid #eee; }
    .stat:last-child { border-right: none; }
    .stat .label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .stat .value { font-size: 14px; font-weight: 700; color: #1A1A2E; margin-top: 2px; }
    .stat .value.orange { color: #E8820C; }
    .stat .value.green  { color: #16a34a; }
    .stat .value.red    { color: #dc2626; }

    table { width: 100%; border-collapse: collapse; margin-top: 0; }
    thead tr { background: #1A1A2E; color: #fff; }
    thead th { padding: 8px 10px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }

    tbody tr { border-bottom: 1px solid #f0f0f0; }
    tbody tr:nth-child(even) { background: #fafafa; }
    td { padding: 7px 10px; font-size: 9px; vertical-align: middle; }
    td.right { text-align: right; }
    td.center { text-align: center; }
    td.mono { font-family: DejaVu Sans Mono, monospace; font-size: 8px; color: #E8820C; font-weight: 700; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 8px; font-weight: 600; }
    .badge-paid    { background: #dcfce7; color: #15803d; }
    .badge-partial { background: #fef9c3; color: #a16207; }
    .badge-unpaid  { background: #fee2e2; color: #b91c1c; }
    .badge-status  { background: #e0e7ff; color: #3730a3; }

    .footer { margin-top: 16px; padding: 10px 24px; border-top: 1px solid #eee; display: flex; justify-content: space-between; font-size: 8px; color: #aaa; }
    .bold { font-weight: 700; }
    .orange { color: #E8820C; }
</style>
</head>
<body>

<div class="header">
    <div class="logo-block">
        <img src="{{ public_path('images/logo-gsit.jpg') }}" alt="GSIT">
        <div>
            <div class="company">GSIT</div>
            <div class="subtitle">Haute Couture</div>
        </div>
    </div>
    <div class="meta">
        <div class="title">Liste des ventes</div>
        <div class="date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        @if(request()->hasAny(['search','status','payment_status']))
        <div class="date" style="color:#E8820C;margin-top:2px;">Filtres actifs</div>
        @endif
    </div>
</div>

@if(request()->hasAny(['search','status','payment_status']))
<div class="filters">
    Filtres :
    @if(request('search')) Recherche : <strong>{{ request('search') }}</strong> &nbsp;|&nbsp; @endif
    @if(request('status')) Statut : <strong>{{ request('status') }}</strong> &nbsp;|&nbsp; @endif
    @if(request('payment_status')) Paiement : <strong>{{ request('payment_status') }}</strong> @endif
</div>
@endif

<div class="stats">
    <div class="stat">
        <div class="label">Total ventes</div>
        <div class="value">{{ $orders->count() }}</div>
    </div>
    <div class="stat">
        <div class="label">Chiffre d'affaires</div>
        <div class="value orange">{{ number_format($orders->sum('total'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">Encaissé</div>
        <div class="value green">{{ number_format($orders->sum('amount_paid'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">Reste à percevoir</div>
        <div class="value red">{{ number_format($orders->sum('balance'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">Payées intégralement</div>
        <div class="value green">{{ $orders->where('payment_status','paid')->count() }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Référence</th>
            <th>Date</th>
            <th>Client</th>
            <th>Type</th>
            <th class="right">Total</th>
            <th class="right">Acompte</th>
            <th class="right">Reste</th>
            <th class="center">Paiement</th>
            <th class="center">Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td class="mono">{{ $order->reference }}</td>
            <td>{{ $order->created_at->format('d/m/Y') }}</td>
            <td>
                <div class="bold">{{ $order->client->full_name }}</div>
                <div style="color:#888;font-size:8px;">{{ $order->client->phone }}</div>
            </td>
            <td>{{ match($order->type) { 'tissu'=>'Tissu','pret_a_porter'=>'Prêt-à-porter',default=>'Mixte' } }}</td>
            <td class="right bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($order->amount_paid, 0, ',', ' ') }}</td>
            <td class="right {{ $order->balance > 0 ? 'orange bold' : '' }}">
                {{ $order->balance > 0 ? number_format($order->balance, 0, ',', ' ') : '—' }}
            </td>
            <td class="center">
                <span class="badge {{ match($order->payment_status) { 'paid'=>'badge-paid','partial'=>'badge-partial',default=>'badge-unpaid' } }}">
                    {{ match($order->payment_status) { 'paid'=>'Payé','partial'=>'Partiel',default=>'Impayé' } }}
                </span>
            </td>
            <td class="center">
                <span class="badge badge-status">{{ $order->getStatusLabel() }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" style="text-align:center;padding:20px;color:#aaa;">Aucune vente trouvée</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>GSIT Haute Couture &copy; {{ date('Y') }}</span>
    <span>{{ $orders->count() }} vente(s) — Total : <strong>{{ number_format($orders->sum('total'), 0, ',', ' ') }} FCFA</strong></span>
</div>

</body>
</html>
