<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset & Base ─────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:9.5px; color:#1a1a1a; background:#fff; }

/* ── Page ─────────────────────────────────────────────── */
@page { margin: 0; size: A4 portrait; }
.page { width:210mm; min-height:297mm; position:relative; page-break-after:always; }
.page:last-child { page-break-after:auto; }

/* ── Cover Page ────────────────────────────────────────── */
.cover { background:#0a0a0a; width:210mm; min-height:297mm; position:relative; color:#fff; }
.cover-accent { position:absolute; top:0; right:0; width:80mm; height:80mm; background:#1a1a1a; border-radius:0 0 0 80mm; }
.cover-bottom-accent { position:absolute; bottom:0; left:0; width:60mm; height:40mm; background:#1a1a1a; border-radius:0 40mm 0 0; }
.cover-logo-wrap { padding:14mm 14mm 0; position:relative; z-index:10; }
.cover-logo { width:18mm; height:18mm; background:#fff; border-radius:4mm; padding:2mm; }
.cover-brand { font-size:22px; font-weight:bold; letter-spacing:6px; margin-top:2mm; color:#fff; }
.cover-sub { font-size:9px; color:#888; letter-spacing:3px; text-transform:uppercase; }
.cover-body { padding:20mm 14mm 0; position:relative; z-index:10; }
.cover-label { font-size:9px; color:#888; text-transform:uppercase; letter-spacing:2px; border-left:2px solid #C9A84C; padding-left:4px; margin-bottom:4mm; }
.cover-title { font-size:26px; font-weight:bold; color:#fff; line-height:1.25; margin-bottom:3mm; }
.cover-period { font-size:14px; color:#C9A84C; font-weight:bold; margin-bottom:10mm; }
.cover-divider { border:none; border-top:1px solid #333; margin:8mm 0; }
.cover-kpi-grid { display:table; width:100%; margin-bottom:10mm; }
.cover-kpi-row { display:table-row; }
.cover-kpi { display:table-cell; width:25%; padding:4mm; border:1px solid #222; vertical-align:top; }
.cover-kpi-val { font-size:18px; font-weight:bold; color:#fff; }
.cover-kpi-lbl { font-size:7.5px; color:#777; text-transform:uppercase; letter-spacing:1px; margin-top:1mm; }
.cover-kpi-trend { font-size:8px; margin-top:1.5mm; font-weight:bold; }
.cover-meta { font-size:8px; color:#555; }
.cover-footer { position:absolute; bottom:8mm; left:14mm; right:14mm; z-index:10; }
.cover-footer-line { border:none; border-top:1px solid #222; margin-bottom:3mm; }
.cover-footer-text { font-size:7.5px; color:#555; display:table; width:100%; }
.cover-footer-left  { display:table-cell; text-align:left; }
.cover-footer-right { display:table-cell; text-align:right; }

/* ── Inner Pages ───────────────────────────────────────── */
.inner { padding:0; }
.page-header { background:#0a0a0a; padding:5mm 10mm; display:table; width:100%; }
.ph-left  { display:table-cell; vertical-align:middle; }
.ph-right { display:table-cell; vertical-align:middle; text-align:right; }
.ph-title { font-size:12px; font-weight:bold; color:#fff; letter-spacing:1px; }
.ph-sub   { font-size:7.5px; color:#888; margin-top:0.5mm; }
.ph-period{ font-size:8px; color:#C9A84C; font-weight:bold; }
.ph-logo  { width:8mm; height:8mm; background:#fff; border-radius:1.5mm; padding:0.8mm; vertical-align:middle; }

.content { padding:6mm 10mm; }

/* ── Section Headers ───────────────────────────────────── */
.section { margin-bottom:7mm; }
.section-header { display:table; width:100%; margin-bottom:4mm; border-bottom:1.5px solid #0a0a0a; padding-bottom:2mm; }
.section-icon   { display:table-cell; width:6mm; vertical-align:middle; }
.section-title  { display:table-cell; font-size:11px; font-weight:bold; color:#0a0a0a; vertical-align:middle; letter-spacing:0.5px; }
.section-badge  { display:table-cell; text-align:right; vertical-align:middle; }
.badge { font-size:7px; font-weight:bold; padding:1mm 3mm; border-radius:2mm; text-transform:uppercase; letter-spacing:0.5px; }
.badge-black  { background:#0a0a0a; color:#fff; }
.badge-green  { background:#dcfce7; color:#166534; }
.badge-red    { background:#fee2e2; color:#991b1b; }
.badge-gold   { background:#fef3c7; color:#92400e; }

/* ── KPI Cards ─────────────────────────────────────────── */
.kpi-row { display:table; width:100%; margin-bottom:5mm; border-collapse:separate; }
.kpi-cell { display:table-cell; padding-right:3mm; vertical-align:top; }
.kpi-cell:last-child { padding-right:0; }
.kpi-card { background:#f8f8f8; border:1px solid #e5e5e5; border-radius:2.5mm; padding:3.5mm; border-left:3px solid #0a0a0a; }
.kpi-card.accent { border-left-color:#C9A84C; }
.kpi-card.danger { border-left-color:#dc2626; }
.kpi-card.success{ border-left-color:#16a34a; }
.kpi-val  { font-size:14px; font-weight:bold; color:#0a0a0a; }
.kpi-val.gold    { color:#C9A84C; }
.kpi-val.red     { color:#dc2626; }
.kpi-val.green   { color:#16a34a; }
.kpi-lbl  { font-size:7.5px; color:#888; margin-top:1mm; text-transform:uppercase; letter-spacing:0.5px; }
.kpi-trend{ font-size:8px; font-weight:bold; margin-top:1.5mm; }

/* ── Tables ────────────────────────────────────────────── */
table { width:100%; border-collapse:collapse; font-size:8.5px; }
thead tr { background:#0a0a0a; }
thead th { color:#fff; padding:3mm 4mm; text-align:left; font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; }
thead th.right { text-align:right; }
thead th.center { text-align:center; }
tbody tr:nth-child(even) td { background:#f9f9f9; }
tbody tr td { padding:2.5mm 4mm; border-bottom:1px solid #f0f0f0; color:#1a1a1a; }
tbody td.right  { text-align:right; }
tbody td.center { text-align:center; }
tbody td.bold   { font-weight:bold; }
tbody td.gold   { color:#C9A84C; font-weight:bold; }
tbody td.red    { color:#dc2626; font-weight:bold; }
tbody td.green  { color:#16a34a; font-weight:bold; }
tfoot tr td { padding:2.5mm 4mm; background:#0a0a0a; color:#fff; font-weight:bold; font-size:8.5px; border-top:1px solid #333; }
tfoot td.right { text-align:right; color:#C9A84C; }
.rank-badge { display:inline-block; background:#0a0a0a; color:#fff; font-size:7px; font-weight:bold; padding:0.5mm 1.5mm; border-radius:1mm; min-width:5mm; text-align:center; }

/* ── Analysis Box ───────────────────────────────────────── */
.analysis { background:#f0f0f0; border-left:3px solid #0a0a0a; border-radius:0 2mm 2mm 0; padding:3mm 4mm; margin:4mm 0; }
.analysis-title { font-size:8px; font-weight:bold; color:#0a0a0a; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:1.5mm; }
.analysis-text { font-size:8px; color:#444; line-height:1.6; }

.alert-box { padding:2.5mm 4mm; border-radius:2mm; margin:3mm 0; font-size:8px; }
.alert-warn { background:#fef3c7; border-left:3px solid #d97706; color:#92400e; }
.alert-good { background:#dcfce7; border-left:3px solid #16a34a; color:#166534; }
.alert-info { background:#eff6ff; border-left:3px solid #2563eb; color:#1e40af; }

/* ── Two-col layout ─────────────────────────────────────── */
.two-col { display:table; width:100%; }
.col-left  { display:table-cell; width:55%; padding-right:4mm; vertical-align:top; }
.col-right { display:table-cell; width:45%; vertical-align:top; }

/* ── Progress Bars ──────────────────────────────────────── */
.bar-row { display:table; width:100%; margin-bottom:2mm; }
.bar-label { display:table-cell; width:35%; font-size:8px; color:#444; vertical-align:middle; padding-right:2mm; }
.bar-track { display:table-cell; vertical-align:middle; }
.bar-bg { background:#e5e5e5; border-radius:1mm; height:4px; }
.bar-fill { height:4px; border-radius:1mm; background:#0a0a0a; }
.bar-fill.gold { background:#C9A84C; }
.bar-fill.red  { background:#dc2626; }
.bar-val { display:table-cell; width:20%; text-align:right; font-size:8px; font-weight:bold; color:#0a0a0a; padding-left:2mm; vertical-align:middle; }

/* ── Page Footer ────────────────────────────────────────── */
.page-footer { background:#f8f8f8; border-top:1px solid #e5e5e5; padding:2.5mm 10mm; display:table; width:100%; margin-top:4mm; }
.pf-left  { display:table-cell; font-size:7px; color:#aaa; }
.pf-center{ display:table-cell; font-size:7px; color:#aaa; text-align:center; }
.pf-right { display:table-cell; font-size:7px; color:#aaa; text-align:right; }

/* ── Helpers ─────────────────────────────────────────────── */
.mt2 { margin-top:2mm; }
.mt4 { margin-top:4mm; }
.mt6 { margin-top:6mm; }
.mb2 { margin-bottom:2mm; }
.mb4 { margin-bottom:4mm; }
.divider { border:none; border-top:1px solid #e5e5e5; margin:4mm 0; }
.text-right { text-align:right; }
.text-center { text-align:center; }
.bold { font-weight:bold; }
.small { font-size:7.5px; color:#888; }
</style>
</head>
<body>

@php
/* ════════════════════════════════════════════════════════
   Calculs analytiques
════════════════════════════════════════════════════════ */
$revenue   = $sales['revenue']  ?? 0;
$expenses  = $expenses['total'] ?? 0;
$profit    = $revenue - $expenses;
$margin    = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
$evolution = $sales['evolution'] ?? null;

// Analyse ventes
$salesPerf = $evolution !== null
    ? ($evolution >= 10 ? 'excellente' : ($evolution >= 0 ? 'positive' : 'en recul'))
    : 'stable';
$topProduct = $sales['top_products']->first();

// Analyse stock
$stockHealth = ($stock['out_of_stock'] ?? 0) > 3 ? 'critique'
    : (($stock['low_stock'] ?? 0) > 5 ? 'faible' : 'satisfaisant');

// Analyse financière
$marginStatus = $margin >= 20 ? 'excellente' : ($margin >= 10 ? 'correcte' : ($margin > 0 ? 'faible' : 'négative'));

$logoPath = public_path('images/logo-gsit.jpg');
$logoSrc  = file_exists($logoPath) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logoPath)) : '';
@endphp

{{-- ══════════════════════════════════════════════════════
     PAGE 1 — COUVERTURE
══════════════════════════════════════════════════════ --}}
<div class="page cover">
    <div class="cover-accent"></div>
    <div class="cover-bottom-accent"></div>

    <div class="cover-logo-wrap">
        @if($logoSrc)
        <img src="{{ $logoSrc }}" class="cover-logo" alt="GSIT">
        @endif
        <div class="cover-brand">GSIT</div>
        <div class="cover-sub">Mode &amp; Confection</div>
    </div>

    <div class="cover-body">
        <div class="cover-label">Rapport de Gestion</div>
        <div class="cover-title">Rapport Mensuel<br>de Performance</div>
        <div class="cover-period">{{ $period_label }}</div>

        <hr class="cover-divider">

        {{-- KPIs couverture --}}
        <div class="cover-kpi-grid">
            <div class="cover-kpi-row">
                <div class="cover-kpi">
                    <div class="cover-kpi-val" style="color:#C9A84C">{{ number_format($revenue,0,',',' ') }}</div>
                    <div class="cover-kpi-lbl">Chiffre d'Affaires (FCFA)</div>
                    @if($evolution !== null)
                    <div class="cover-kpi-trend" style="color:{{ $evolution >= 0 ? '#16a34a' : '#dc2626' }}">
                        {{ $evolution >= 0 ? '▲' : '▼' }} {{ abs($evolution) }}% vs période préc.
                    </div>
                    @endif
                </div>
                <div class="cover-kpi">
                    <div class="cover-kpi-val" style="color:#dc2626">{{ number_format($expenses,0,',',' ') }}</div>
                    <div class="cover-kpi-lbl">Total Dépenses (FCFA)</div>
                </div>
                <div class="cover-kpi">
                    <div class="cover-kpi-val" style="color:{{ $profit >= 0 ? '#16a34a' : '#dc2626' }}">{{ number_format($profit,0,',',' ') }}</div>
                    <div class="cover-kpi-lbl">Bénéfice Net (FCFA)</div>
                </div>
                <div class="cover-kpi">
                    <div class="cover-kpi-val" style="color:{{ $margin >= 10 ? '#16a34a' : ($margin >= 0 ? '#C9A84C' : '#dc2626') }}">{{ $margin }}%</div>
                    <div class="cover-kpi-lbl">Marge Nette</div>
                </div>
            </div>
        </div>

        <hr class="cover-divider">

        {{-- Résumé exécutif --}}
        <div style="margin-top:4mm">
            <div style="font-size:8px;color:#888;text-transform:uppercase;letter-spacing:2px;margin-bottom:2mm">Résumé Exécutif</div>
            <div style="font-size:9px;color:#ccc;line-height:1.8">
                Au titre de la période <strong style="color:#fff">{{ $period_label }}</strong>,
                GSIT Mode &amp; Confection enregistre un chiffre d'affaires de
                <strong style="color:#C9A84C">{{ number_format($revenue,0,',',' ') }} FCFA</strong>
                @if($evolution !== null)
                    , soit une évolution de <strong style="color:{{ $evolution >= 0 ? '#16a34a' : '#dc2626' }}">{{ ($evolution >= 0 ? '+' : '') . $evolution }}%</strong> par rapport à la période précédente
                @endif
                . La marge nette ressort à <strong style="color:{{ $margin >= 10 ? '#16a34a' : '#C9A84C' }}">{{ $margin }}%</strong>,
                qualifiée de <strong style="color:#fff">{{ $marginStatus }}</strong>.
                @if($topProduct)
                Le produit <strong style="color:#C9A84C">{{ $topProduct->name }}</strong> demeure le meilleur contributeur au chiffre d'affaires.
                @endif
                @if(($stock['low_stock'] ?? 0) > 0)
                Une attention particulière est requise sur la gestion des stocks avec {{ $stock['low_stock'] ?? 0 }} article(s) en alerte.
                @endif
            </div>
        </div>

        <hr class="cover-divider" style="margin-top:6mm">

        <div style="display:table;width:100%;margin-top:4mm">
            <div style="display:table-cell;width:33%;font-size:8px;color:#555">
                <div style="color:#888;font-size:7px;text-transform:uppercase;letter-spacing:1px;margin-bottom:1mm">Total Commandes</div>
                <div style="font-size:16px;font-weight:bold;color:#fff">{{ ($sales['total_orders'] ?? 0) }}</div>
                <div style="font-size:7px;color:#666">dont {{ $sales['custom_count'] ?? 0 }} sur mesure</div>
            </div>
            <div style="display:table-cell;width:33%;font-size:8px;color:#555;text-align:center">
                <div style="color:#888;font-size:7px;text-transform:uppercase;letter-spacing:1px;margin-bottom:1mm">Valeur du Stock</div>
                <div style="font-size:16px;font-weight:bold;color:#fff">{{ number_format($stock['total_value'] ?? 0,0,',',' ') }}</div>
                <div style="font-size:7px;color:#666">FCFA immobilisés</div>
            </div>
            <div style="display:table-cell;width:33%;font-size:8px;color:#555;text-align:right">
                <div style="color:#888;font-size:7px;text-transform:uppercase;letter-spacing:1px;margin-bottom:1mm">Maintenance</div>
                <div style="font-size:16px;font-weight:bold;color:#fff">{{ $maintenance['count'] ?? 0 }}</div>
                <div style="font-size:7px;color:#666">interventions — {{ $maintenance['resolved'] ?? 0 }} résolues</div>
            </div>
        </div>
    </div>

    <div class="cover-footer">
        <hr class="cover-footer-line">
        <div class="cover-footer-text">
            <div class="cover-footer-left">Confidentiel — Usage interne uniquement</div>
            <div class="cover-footer-right">Généré le {{ $generated_at }}</div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 2 — VENTES & COMMANDES
══════════════════════════════════════════════════════ --}}
@if(isset($sales))
<div class="page inner">
    <div class="page-header">
        <div class="ph-left">
            <div class="ph-title">VENTES &amp; COMMANDES</div>
            <div class="ph-sub">Analyse de la performance commerciale</div>
        </div>
        <div class="ph-right">
            @if($logoSrc)<img src="{{ $logoSrc }}" class="ph-logo" alt="GSIT">@endif
            <div class="ph-period" style="margin-top:1mm">{{ $period_label }}</div>
        </div>
    </div>

    <div class="content">
        {{-- KPIs --}}
        <div class="kpi-row">
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card accent">
                    <div class="kpi-val gold">{{ number_format($sales['revenue'],0,',',' ') }}</div>
                    <div class="kpi-lbl">Chiffre d'affaires FCFA</div>
                    @if($evolution !== null)
                    <div class="kpi-trend" style="color:{{ $evolution>=0?'#16a34a':'#dc2626' }}">
                        {{ $evolution>=0?'▲':'▼' }} {{ abs($evolution) }}%
                    </div>
                    @endif
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $sales['orders_count'] }}</div>
                    <div class="kpi-lbl">Commandes normales</div>
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $sales['custom_count'] }}</div>
                    <div class="kpi-lbl">Commandes sur mesure</div>
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $sales['total_orders'] }}</div>
                    <div class="kpi-lbl">Total commandes</div>
                </div>
            </div>
        </div>

        {{-- Analyse --}}
        <div class="analysis">
            <div class="analysis-title">■ Analyse de la Performance</div>
            <div class="analysis-text">
                La performance commerciale du mois de <strong>{{ $period_label }}</strong> est <strong>{{ $salesPerf }}</strong>.
                @if($evolution !== null)
                    Le chiffre d'affaires évolue de <strong>{{ ($evolution >= 0 ? '+' : '') . $evolution }}%</strong> par rapport à la période précédente,
                    ce qui {{ $evolution >= 0 ? 'témoigne d\'une dynamique positive sur la clientèle et les ventes' : 'appelle une analyse des causes de ce recul afin de mettre en œuvre des mesures correctives' }}.
                @endif
                @if($topProduct)
                    Le produit phare reste <strong>{{ $topProduct->name }}</strong> avec <strong>{{ number_format($topProduct->total,0,',',' ') }} FCFA</strong> générés sur la période.
                @endif
                La répartition entre ventes normales ({{ $sales['orders_count'] }}) et sur mesure ({{ $sales['custom_count'] }}) indique
                {{ $sales['custom_count'] > $sales['orders_count'] ? 'une prédominance de la confection sur mesure, valorisant notre savoir-faire artisanal.' : 'une dominance des ventes catalogue, signe d\'une demande standard soutenue.' }}
            </div>
        </div>

        <div class="two-col">
            <div class="col-left">
                {{-- Répartition par type --}}
                <div class="section-header">
                    <div class="section-title" style="font-size:9px">Répartition par type de vente</div>
                </div>
                @php $totalByType = array_sum(array_values($sales['by_type'])); @endphp
                @foreach($sales['by_type'] as $label => $amount)
                @php $pct = $totalByType > 0 ? round(($amount/$totalByType)*100,1) : 0; @endphp
                <div class="bar-row">
                    <div class="bar-label">{{ $label }}</div>
                    <div class="bar-track">
                        <div class="bar-bg"><div class="bar-fill gold" style="width:{{ $pct }}%"></div></div>
                    </div>
                    <div class="bar-val">{{ number_format($amount,0,',',' ') }} F</div>
                </div>
                @endforeach

                {{-- Graphique SVG barres --}}
                @if($sales['top_products']->count())
                <div class="mt4">
                    <div class="section-header">
                        <div class="section-title" style="font-size:9px">CA par produit (FCFA)</div>
                    </div>
                    @php
                        $svgW = 230; $svgH = 120;
                        $maxVal = $sales['top_products']->max('total');
                        $items  = $sales['top_products']->take(6);
                        $barH   = 14; $gap = 4;
                        $labelW = 70; $barMaxW = $svgW - $labelW - 40;
                    @endphp
                    <svg width="{{ $svgW }}" height="{{ $svgH }}" xmlns="http://www.w3.org/2000/svg">
                        @foreach($items as $idx => $p)
                        @php
                            $y      = $idx * ($barH + $gap) + 4;
                            $bw     = $maxVal > 0 ? ($p->total / $maxVal) * $barMaxW : 0;
                            $pctBar = $sales['revenue'] > 0 ? round(($p->total/$sales['revenue'])*100,0) : 0;
                        @endphp
                        <text x="{{ $labelW - 3 }}" y="{{ $y + $barH - 4 }}" font-family="DejaVu Sans" font-size="6.5" fill="#444" text-anchor="end">{{ \Str::limit($p->name,18) }}</text>
                        <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ max(2,$bw) }}" height="{{ $barH }}" fill="#0a0a0a" rx="1.5"/>
                        <text x="{{ $labelW + $bw + 3 }}" y="{{ $y + $barH - 4 }}" font-family="DejaVu Sans" font-size="6" fill="#C9A84C" font-weight="bold">{{ number_format($p->total,0,',',' ') }} ({{ $pctBar }}%)</text>
                        @endforeach
                    </svg>
                </div>
                @endif
            </div>

            <div class="col-right">
                {{-- Tableau top produits --}}
                <div class="section-header">
                    <div class="section-title" style="font-size:9px">Top produits vendus</div>
                </div>
                @if($sales['top_products']->count())
                <table>
                    <thead>
                        <tr>
                            <th style="width:8%">#</th>
                            <th>Produit</th>
                            <th class="right" style="width:14%">Qté</th>
                            <th class="right" style="width:28%">CA (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales['top_products'] as $i => $p)
                        @php $pctP = $sales['revenue'] > 0 ? round(($p->total/$sales['revenue'])*100,1) : 0; @endphp
                        <tr>
                            <td><span class="rank-badge">{{ $i+1 }}</span></td>
                            <td class="bold">{{ \Str::limit($p->name,20) }}</td>
                            <td class="right">{{ $p->qty }}</td>
                            <td class="right gold">{{ number_format($p->total,0,',',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">TOTAL PÉRIODE</td>
                            <td class="right">{{ number_format($sales['revenue'],0,',',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
                @if($sales['evolution'] !== null)
                <div class="alert-box {{ $sales['evolution'] >= 0 ? 'alert-good' : 'alert-warn' }}" style="margin-top:3mm">
                    {{ $sales['evolution'] >= 0
                        ? '✓ La progression des ventes est positive. Maintenir les efforts marketing et la qualité de service.'
                        : '⚠ Recul des ventes détecté. Recommandation : revoir la stratégie de prospection et les offres promotionnelles.' }}
                </div>
                @endif
                @else
                <div class="alert-box alert-info">Aucune vente enregistrée sur cette période.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="page-footer">
        <div class="pf-left">GSIT — Rapport de Gestion {{ $period_label }}</div>
        <div class="pf-center">Confidentiel</div>
        <div class="pf-right">Page 2</div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     PAGE 3 — FINANCE (CA vs DÉPENSES vs BÉNÉFICE)
══════════════════════════════════════════════════════ --}}
@if(isset($finance) || isset($sales))
<div class="page inner">
    <div class="page-header">
        <div class="ph-left">
            <div class="ph-title">ANALYSE FINANCIÈRE</div>
            <div class="ph-sub">Rentabilité, charges et bénéfice net</div>
        </div>
        <div class="ph-right">
            @if($logoSrc)<img src="{{ $logoSrc }}" class="ph-logo" alt="GSIT">@endif
            <div class="ph-period" style="margin-top:1mm">{{ $period_label }}</div>
        </div>
    </div>

    <div class="content">
        @php
            $expTotal  = $expenses['total'] ?? 0;
            $expOps    = $expenses['operations'] ?? 0;
            $expSal    = $expenses['salaries'] ?? 0;
            $expPurch  = $expenses['purchases'] ?? 0;
        @endphp

        {{-- Synthèse financière --}}
        <div class="kpi-row">
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card accent">
                    <div class="kpi-val gold">{{ number_format($revenue,0,',',' ') }}</div>
                    <div class="kpi-lbl">Chiffre d'Affaires FCFA</div>
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card danger">
                    <div class="kpi-val red">{{ number_format($expTotal,0,',',' ') }}</div>
                    <div class="kpi-lbl">Total Dépenses FCFA</div>
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card {{ $profit >= 0 ? 'success' : 'danger' }}">
                    <div class="kpi-val {{ $profit >= 0 ? 'green' : 'red' }}">{{ number_format($profit,0,',',' ') }}</div>
                    <div class="kpi-lbl">Bénéfice Net FCFA</div>
                </div>
            </div>
            <div class="kpi-cell" style="width:25%">
                <div class="kpi-card {{ $margin >= 10 ? 'success' : 'accent' }}">
                    <div class="kpi-val {{ $margin >= 10 ? 'green' : ($margin >= 0 ? 'gold' : 'red') }}">{{ $margin }}%</div>
                    <div class="kpi-lbl">Marge Nette</div>
                </div>
            </div>
        </div>

        {{-- Analyse --}}
        <div class="analysis">
            <div class="analysis-title">■ Commentaire Financier</div>
            <div class="analysis-text">
                Sur la période <strong>{{ $period_label }}</strong>, GSIT dégage un bénéfice net de
                <strong>{{ number_format($profit,0,',',' ') }} FCFA</strong>, représentant une marge de <strong>{{ $margin }}%</strong> — qualifiée de <strong>{{ $marginStatus }}</strong>.
                Les charges totales s'élèvent à <strong>{{ number_format($expTotal,0,',',' ') }} FCFA</strong>,
                @if($revenue > 0)
                    soit {{ round(($expTotal/$revenue)*100,1) }}% du chiffre d'affaires.
                @endif
                @if($expSal > 0) Les salaires représentent <strong>{{ number_format($expSal,0,',',' ') }} FCFA</strong> ({{ $expTotal > 0 ? round(($expSal/$expTotal)*100,0).'%' : 'N/A' }} des charges totales). @endif
                @if($margin < 10 && $margin >= 0) La marge reste perfectible : une réduction des charges opérationnelles ou une augmentation des prix de vente permettraient d'améliorer la rentabilité. @endif
                @if($margin < 0) Attention : la structure de coûts dépasse le chiffre d'affaires. Une revue immédiate des charges s'impose. @endif
                @if($margin >= 20) Excellente maîtrise des coûts. La rentabilité est à son niveau optimal, démontrant l'efficacité opérationnelle de l'entreprise. @endif
            </div>
        </div>

        <div class="two-col">
            <div class="col-left">
                {{-- Graphique SVG CA vs Dépenses vs Bénéfice --}}
                <div class="section-header">
                    <div class="section-title" style="font-size:9px">Synthèse financière (FCFA)</div>
                </div>
                @php
                    $fMax = max($revenue, $expTotal, max(0,$profit)) ?: 1;
                    $fW = 220; $fH = 100;
                    $bW = 40; $bGap = 20; $fBottomY = 90;
                    $fItems = [
                        ['label'=>'CA',       'val'=>$revenue,  'color'=>'#C9A84C'],
                        ['label'=>'Dépenses', 'val'=>$expTotal, 'color'=>'#dc2626'],
                        ['label'=>'Bénéfice', 'val'=>max(0,$profit), 'color'=>'#16a34a'],
                    ];
                    $startX = 30;
                @endphp
                <svg width="{{ $fW }}" height="{{ $fH }}" xmlns="http://www.w3.org/2000/svg">
                    <!-- Grille -->
                    @foreach([0,25,50,75,100] as $pct)
                    @php $gridY = $fBottomY - ($pct/100)*($fBottomY-10); @endphp
                    <line x1="25" y1="{{ $gridY }}" x2="{{ $fW-5 }}" y2="{{ $gridY }}" stroke="#eee" stroke-width="0.5"/>
                    <text x="22" y="{{ $gridY+2 }}" font-family="DejaVu Sans" font-size="5" fill="#bbb" text-anchor="end">{{ number_format($fMax*$pct/100/1000,0).'k' }}</text>
                    @endforeach
                    <!-- Barres -->
                    @foreach($fItems as $fi => $fitem)
                    @php
                        $bH = $fitem['val'] > 0 ? ($fitem['val']/$fMax)*($fBottomY-12) : 2;
                        $bX = $startX + $fi * ($bW + $bGap);
                        $bY = $fBottomY - $bH;
                    @endphp
                    <rect x="{{ $bX }}" y="{{ $bY }}" width="{{ $bW }}" height="{{ $bH }}" fill="{{ $fitem['color'] }}" rx="2"/>
                    <text x="{{ $bX + $bW/2 }}" y="{{ $fBottomY + 8 }}" font-family="DejaVu Sans" font-size="6.5" fill="#444" text-anchor="middle">{{ $fitem['label'] }}</text>
                    <text x="{{ $bX + $bW/2 }}" y="{{ $bY - 3 }}" font-family="DejaVu Sans" font-size="5.5" fill="{{ $fitem['color'] }}" text-anchor="middle" font-weight="bold">{{ number_format($fitem['val']/1000,0).'k' }}</text>
                    @endforeach
                </svg>

                <div class="mt4">
                    <div class="section-header">
                        <div class="section-title" style="font-size:9px">Répartition des charges</div>
                    </div>
                    @foreach([['Opérations',$expOps],['Salaires',$expSal],['Achats stock',$expPurch]] as $item)
                    @php $pctItem = $expTotal > 0 ? round(($item[1]/$expTotal)*100,1) : 0; @endphp
                    <div class="bar-row">
                        <div class="bar-label">{{ $item[0] }}</div>
                        <div class="bar-track">
                            <div class="bar-bg"><div class="bar-fill red" style="width:{{ $pctItem }}%"></div></div>
                        </div>
                        <div class="bar-val" style="color:#dc2626">{{ number_format($item[1],0,',',' ') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-right">
                {{-- Tableau dépenses par catégorie --}}
                <div class="section-header">
                    <div class="section-title" style="font-size:9px">Dépenses par catégorie</div>
                </div>
                @if(isset($expenses) && $expenses['by_category']->count())
                <table>
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th class="right">Nb</th>
                            <th class="right">Montant</th>
                            <th class="right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses['by_category'] as $cat)
                        @php $pctCat = $expTotal > 0 ? round(($cat->total/$expTotal)*100,1) : 0; @endphp
                        <tr>
                            <td>{{ $cat->name }}</td>
                            <td class="right">{{ $cat->count }}</td>
                            <td class="right red">{{ number_format($cat->total,0,',',' ') }}</td>
                            <td class="right">{{ $pctCat }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">TOTAL</td>
                            <td class="right">{{ number_format($expTotal,0,',',' ') }}</td>
                            <td class="right">100%</td>
                        </tr>
                    </tfoot>
                </table>
                @endif

                <div class="mt4">
                    @if($profit >= 0)
                    <div class="alert-box alert-good">
                        <strong>✓ Situation financière {{ $marginStatus }}</strong><br>
                        Le bénéfice net de {{ number_format($profit,0,',',' ') }} FCFA confirme la viabilité de l'activité sur cette période.
                    </div>
                    @else
                    <div class="alert-box alert-warn">
                        <strong>⚠ Déficit enregistré</strong><br>
                        Les charges ({{ number_format($expTotal,0,',',' ') }} FCFA) dépassent le CA ({{ number_format($revenue,0,',',' ') }} FCFA). Un plan d'action correctif est nécessaire.
                    </div>
                    @endif

                    {{-- Dépenses récentes --}}
                    @if(isset($expenses) && $expenses['recent']->count())
                    <div class="section-header mt4">
                        <div class="section-title" style="font-size:9px">Dernières dépenses</div>
                    </div>
                    <table>
                        <thead><tr><th>Libellé</th><th>Catégorie</th><th class="right">Montant</th></tr></thead>
                        <tbody>
                            @foreach($expenses['recent']->take(8) as $e)
                            <tr>
                                <td>{{ \Str::limit($e->label,18) }}</td>
                                <td class="small">{{ $e->category->name ?? '—' }}</td>
                                <td class="right red">{{ number_format($e->amount,0,',',' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="page-footer">
        <div class="pf-left">GSIT — Rapport de Gestion {{ $period_label }}</div>
        <div class="pf-center">Confidentiel</div>
        <div class="pf-right">Page 3</div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     PAGE 4 — STOCK & MAINTENANCE
══════════════════════════════════════════════════════ --}}
@if(isset($stock) || isset($maintenance))
<div class="page inner">
    <div class="page-header">
        <div class="ph-left">
            <div class="ph-title">STOCK &amp; MAINTENANCE</div>
            <div class="ph-sub">Gestion des ressources matérielles et opérationnelles</div>
        </div>
        <div class="ph-right">
            @if($logoSrc)<img src="{{ $logoSrc }}" class="ph-logo" alt="GSIT">@endif
            <div class="ph-period" style="margin-top:1mm">{{ $period_label }}</div>
        </div>
    </div>

    <div class="content">
        @if(isset($stock))
        <div class="section">
            <div class="section-header">
                <div class="section-title">■ Gestion du Stock</div>
                <div class="section-badge">
                    <span class="badge {{ $stockHealth === 'satisfaisant' ? 'badge-green' : ($stockHealth === 'faible' ? 'badge-gold' : 'badge-red') }}">
                        Stock {{ $stockHealth }}
                    </span>
                </div>
            </div>

            <div class="kpi-row">
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card accent">
                        <div class="kpi-val gold">{{ number_format($stock['total_value'],0,',',' ') }}</div>
                        <div class="kpi-lbl">Valeur stock FCFA</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card">
                        <div class="kpi-val">{{ number_format($stock['purchases_total'],0,',',' ') }}</div>
                        <div class="kpi-lbl">Achats période FCFA</div>
                        <div class="kpi-trend" style="color:#888">{{ $stock['purchases']->count() }} bon(s)</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card {{ $stock['low_stock'] > 3 ? 'danger' : '' }}">
                        <div class="kpi-val {{ $stock['low_stock'] > 3 ? 'red' : '' }}">{{ $stock['low_stock'] }}</div>
                        <div class="kpi-lbl">Articles stock faible</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card {{ $stock['out_of_stock'] > 0 ? 'danger' : 'success' }}">
                        <div class="kpi-val {{ $stock['out_of_stock'] > 0 ? 'red' : 'green' }}">{{ $stock['out_of_stock'] }}</div>
                        <div class="kpi-lbl">Ruptures de stock</div>
                    </div>
                </div>
            </div>

            <div class="analysis">
                <div class="analysis-title">■ Analyse du Stock</div>
                <div class="analysis-text">
                    Le stock représente une valeur immobilisée de <strong>{{ number_format($stock['total_value'],0,',',' ') }} FCFA</strong>.
                    @if($stock['out_of_stock'] > 0)
                        <strong>{{ $stock['out_of_stock'] }} article(s) sont en rupture complète</strong> — une commande de réapprovisionnement urgente est recommandée pour éviter toute perte de vente.
                    @endif
                    @if($stock['low_stock'] > 0)
                        {{ $stock['low_stock'] }} article(s) sont en dessous du seuil d'alerte et nécessitent une attention rapprochée.
                    @endif
                    @if($stock['low_stock'] == 0 && $stock['out_of_stock'] == 0)
                        L'ensemble des articles est correctement approvisionné. La gestion des stocks est maîtrisée.
                    @endif
                </div>
            </div>

            <div class="two-col">
                <div class="col-left">
                    {{-- Graphique mouvements --}}
                    @if($stock['movements']->count())
                    <div class="section-header"><div class="section-title" style="font-size:9px">Mouvements de stock</div></div>
                    @php
                        $mvtMax = $stock['movements']->max('total_qty') ?: 1;
                        $mvtW = 230; $mvtH = 90; $mvtBarH = 16; $mvtGap = 5;
                        $mvtLabelW = 55;
                    @endphp
                    <svg width="{{ $mvtW }}" height="{{ $mvtH }}" xmlns="http://www.w3.org/2000/svg">
                        @foreach($stock['movements']->take(4) as $mi => $mvt)
                        @php
                            $my = $mi * ($mvtBarH + $mvtGap) + 2;
                            $mw = $mvtMax > 0 ? (($mvt->total_qty/$mvtMax) * ($mvtW - $mvtLabelW - 50)) : 0;
                        @endphp
                        <text x="{{ $mvtLabelW - 3 }}" y="{{ $my + $mvtBarH - 4 }}" font-family="DejaVu Sans" font-size="7" fill="#555" text-anchor="end">{{ ucfirst($mvt->type) }}</text>
                        <rect x="{{ $mvtLabelW }}" y="{{ $my }}" width="{{ max(2,$mw) }}" height="{{ $mvtBarH }}" fill="#0a0a0a" rx="2"/>
                        <text x="{{ $mvtLabelW + $mw + 3 }}" y="{{ $my + $mvtBarH - 4 }}" font-family="DejaVu Sans" font-size="6.5" fill="#C9A84C" font-weight="bold">{{ $mvt->total_qty }} u. ({{ $mvt->count }} op.)</text>
                        @endforeach
                    </svg>
                    @endif
                </div>
                <div class="col-right">
                    {{-- Top produits actifs --}}
                    @if($stock['top_moving']->count())
                    <div class="section-header"><div class="section-title" style="font-size:9px">Produits les plus actifs</div></div>
                    <table>
                        <thead><tr><th>#</th><th>Produit</th><th class="right">Mouvements</th></tr></thead>
                        <tbody>
                            @foreach($stock['top_moving']->take(6) as $si => $sp)
                            <tr>
                                <td><span class="rank-badge">{{ $si+1 }}</span></td>
                                <td>{{ \Str::limit($sp->name,20) }}</td>
                                <td class="right bold">{{ $sp->total_mvt }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    @if($stock['out_of_stock'] > 0)
                    <div class="alert-box alert-warn mt4">
                        <strong>⚠ Action requise :</strong> {{ $stock['out_of_stock'] }} rupture(s) détectée(s). Déclencher immédiatement les commandes de réapprovisionnement.
                    </div>
                    @elseif($stock['low_stock'] > 0)
                    <div class="alert-box alert-warn mt4">
                        <strong>⚠ Vigilance :</strong> {{ $stock['low_stock'] }} article(s) proche(s) du seuil critique.
                    </div>
                    @else
                    <div class="alert-box alert-good mt4">
                        <strong>✓ Stock sain :</strong> Aucune rupture ni alerte critique détectée sur la période.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if(isset($maintenance))
        <hr class="divider">
        <div class="section mt4">
            <div class="section-header">
                <div class="section-title">■ Maintenance</div>
                <div class="section-badge">
                    <span class="badge {{ $maintenance['pending'] == 0 ? 'badge-green' : 'badge-gold' }}">
                        {{ $maintenance['pending'] }} en attente
                    </span>
                </div>
            </div>

            <div class="kpi-row">
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card">
                        <div class="kpi-val">{{ number_format($maintenance['total_cost'],0,',',' ') }}</div>
                        <div class="kpi-lbl">Coût total FCFA</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card">
                        <div class="kpi-val">{{ $maintenance['count'] }}</div>
                        <div class="kpi-lbl">Interventions totales</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card success">
                        <div class="kpi-val green">{{ $maintenance['resolved'] }}</div>
                        <div class="kpi-lbl">Résolues</div>
                    </div>
                </div>
                <div class="kpi-cell" style="width:25%">
                    <div class="kpi-card {{ $maintenance['pending'] > 0 ? 'danger' : 'success' }}">
                        <div class="kpi-val {{ $maintenance['pending'] > 0 ? 'red' : 'green' }}">{{ $maintenance['pending'] }}</div>
                        <div class="kpi-lbl">En attente</div>
                    </div>
                </div>
            </div>

            @if($maintenance['logs']->count())
            <table>
                <thead>
                    <tr>
                        <th>Équipement</th>
                        <th>Description</th>
                        <th class="center">Date</th>
                        <th class="center">Statut</th>
                        <th class="right">Coût FCFA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($maintenance['logs']->take(10) as $log)
                    <tr>
                        <td class="bold">{{ $log->equipment->name ?? '—' }}</td>
                        <td class="small">{{ \Str::limit($log->description ?? $log->title ?? '—', 28) }}</td>
                        <td class="center small">{{ $log->created_at->format('d/m/Y') }}</td>
                        <td class="center">
                            <span style="font-size:7px;font-weight:bold;color:{{ $log->status==='resolu'?'#16a34a':($log->status==='en_cours'?'#2563eb':'#d97706') }}">
                                {{ $log->status==='resolu'?'Résolu':($log->status==='en_cours'?'En cours':'Ouvert') }}
                            </span>
                        </td>
                        <td class="right {{ $log->cost > 0 ? 'bold' : 'small' }}">
                            {{ $log->cost > 0 ? number_format($log->cost,0,',',' ') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">TOTAL COÛTS MAINTENANCE</td>
                        <td class="right">{{ number_format($maintenance['total_cost'],0,',',' ') }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>
        @endif
    </div>

    <div class="page-footer">
        <div class="pf-left">GSIT — Rapport de Gestion {{ $period_label }}</div>
        <div class="pf-center">Confidentiel</div>
        <div class="pf-right">Page 4</div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     PAGE 5 — CONCLUSION & RECOMMANDATIONS
══════════════════════════════════════════════════════ --}}
<div class="page inner">
    <div class="page-header">
        <div class="ph-left">
            <div class="ph-title">CONCLUSIONS &amp; RECOMMANDATIONS</div>
            <div class="ph-sub">Synthèse à l'intention du Conseil d'Administration</div>
        </div>
        <div class="ph-right">
            @if($logoSrc)<img src="{{ $logoSrc }}" class="ph-logo" alt="GSIT">@endif
            <div class="ph-period" style="margin-top:1mm">{{ $period_label }}</div>
        </div>
    </div>

    <div class="content">
        {{-- Tableau de bord synthétique --}}
        <div class="section-header">
            <div class="section-title">■ Tableau de Bord Synthétique</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Indicateur</th>
                    <th class="center">Valeur</th>
                    <th class="center">Appréciation</th>
                    <th>Observation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="bold">Chiffre d'affaires</td>
                    <td class="center gold bold">{{ number_format($revenue,0,',',' ') }} F</td>
                    <td class="center">
                        <span style="font-size:7px;font-weight:bold;color:{{ $evolution===null||$evolution>=0?'#16a34a':'#dc2626' }}">
                            {{ $evolution===null ? '—' : ($evolution >= 0 ? '✓ Positif' : '⚠ Recul') }}
                        </span>
                    </td>
                    <td>{{ $evolution !== null ? (($evolution>=0?'+':'').$evolution.'% vs période préc.') : 'Première période de référence' }}</td>
                </tr>
                <tr>
                    <td class="bold">Total dépenses</td>
                    <td class="center red bold">{{ number_format($expTotal,0,',',' ') }} F</td>
                    <td class="center">
                        <span style="font-size:7px;font-weight:bold;color:{{ $revenue>0&&($expTotal/$revenue)<0.8?'#16a34a':'#d97706' }}">
                            {{ $revenue > 0 ? round(($expTotal/$revenue)*100).'% du CA' : '—' }}
                        </span>
                    </td>
                    <td>Dont {{ number_format($expSal,0,',',' ') }} F de salaires</td>
                </tr>
                <tr>
                    <td class="bold">Bénéfice net</td>
                    <td class="center {{ $profit>=0?'green':'red' }} bold">{{ number_format($profit,0,',',' ') }} F</td>
                    <td class="center">
                        <span style="font-size:7px;font-weight:bold;color:{{ $margin>=10?'#16a34a':($margin>=0?'#d97706':'#dc2626') }}">
                            Marge {{ $margin }}%
                        </span>
                    </td>
                    <td>{{ ucfirst($marginStatus) }}</td>
                </tr>
                <tr>
                    <td class="bold">Stock</td>
                    <td class="center bold">{{ number_format($stock['total_value']??0,0,',',' ') }} F</td>
                    <td class="center">
                        <span style="font-size:7px;font-weight:bold;color:{{ $stockHealth==='satisfaisant'?'#16a34a':($stockHealth==='faible'?'#d97706':'#dc2626') }}">
                            {{ ucfirst($stockHealth) }}
                        </span>
                    </td>
                    <td>{{ $stock['out_of_stock']??0 }} rupture(s), {{ $stock['low_stock']??0 }} alerte(s)</td>
                </tr>
                <tr>
                    <td class="bold">Commandes</td>
                    <td class="center bold">{{ $sales['total_orders']??0 }}</td>
                    <td class="center"><span style="font-size:7px;font-weight:bold;color:#2563eb">Actif</span></td>
                    <td>{{ $sales['orders_count']??0 }} normales + {{ $sales['custom_count']??0 }} sur mesure</td>
                </tr>
                <tr>
                    <td class="bold">Maintenance</td>
                    <td class="center bold">{{ $maintenance['count']??0 }} interv.</td>
                    <td class="center">
                        <span style="font-size:7px;font-weight:bold;color:{{ ($maintenance['pending']??0)==0?'#16a34a':'#d97706' }}">
                            {{ ($maintenance['pending']??0)==0?'✓ Résolu':'⚠ En cours' }}
                        </span>
                    </td>
                    <td>Coût : {{ number_format($maintenance['total_cost']??0,0,',',' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>

        <div class="mt6">
            <div class="section-header">
                <div class="section-title">■ Recommandations Stratégiques</div>
            </div>
        </div>

        @php
            $recs = [];
            if ($margin < 10)  $recs[] = ['type'=>'warn', 'title'=>'Amélioration de la marge', 'text'=>'La marge nette de '.$margin.'% est insuffisante. Recommandation : réviser la politique tarifaire, réduire les charges opérationnelles et identifier les lignes de produits les moins rentables.'];
            if ($margin >= 20) $recs[] = ['type'=>'good', 'title'=>'Maintenir l\'excellence opérationnelle', 'text'=>'Excellente maîtrise des coûts avec une marge de '.$margin.'%. Consolider cette performance en standardisant les processus et en fidélisant la clientèle existante.'];
            if (($stock['out_of_stock']??0) > 0) $recs[] = ['type'=>'warn', 'title'=>'Réapprovisionnement urgent', 'text'=>($stock['out_of_stock']).' article(s) en rupture. Déclencher les commandes fournisseurs immédiatement pour ne pas manquer d\'opportunités commerciales.'];
            if (($stock['low_stock']??0) > 3) $recs[] = ['type'=>'warn', 'title'=>'Renforcement du stock tampon', 'text'=>'Plusieurs articles approchent du seuil d\'alerte. Envisager une augmentation des quantités minimales de commande pour sécuriser la production.'];
            if (($maintenance['pending']??0) > 0) $recs[] = ['type'=>'warn', 'title'=>'Clôture des interventions en attente', 'text'=>($maintenance['pending']).' intervention(s) de maintenance restent ouvertes. La résolution rapide est essentielle pour garantir la continuité de la production.'];
            if ($evolution !== null && $evolution < 0) $recs[] = ['type'=>'warn', 'title'=>'Relance commerciale', 'text'=>'Recul du CA de '.abs($evolution).'% vs période précédente. Mettre en place des actions commerciales ciblées : promotions, relance clients inactifs, développement de nouveaux canaux de vente.'];
            if ($evolution !== null && $evolution >= 10) $recs[] = ['type'=>'good', 'title'=>'Capitaliser sur la dynamique de croissance', 'text'=>'Croissance de '.$evolution.'% du CA. Renforcer les capacités de production, anticiper les besoins en stock et envisager le recrutement si nécessaire.'];
            if (empty($recs)) $recs[] = ['type'=>'good', 'title'=>'Performance globale satisfaisante', 'text'=>'L\'ensemble des indicateurs est dans les normes. Maintenir le cap et poursuivre les efforts d\'amélioration continue.'];
        @endphp

        @foreach($recs as $ri => $rec)
        <div class="alert-box {{ $rec['type']==='good' ? 'alert-good' : 'alert-warn' }} mt2">
            <strong>{{ $ri+1 }}. {{ $rec['title'] }}</strong><br>
            <span style="line-height:1.7">{{ $rec['text'] }}</span>
        </div>
        @endforeach

        <div class="analysis mt6">
            <div class="analysis-title">■ Note de Synthèse pour le Conseil d'Administration</div>
            <div class="analysis-text">
                Ce rapport couvre la période <strong>{{ $period_label }}</strong>.
                GSIT Mode &amp; Confection présente un chiffre d'affaires de <strong>{{ number_format($revenue,0,',',' ') }} FCFA</strong>
                pour un bénéfice net de <strong>{{ number_format($profit,0,',',' ') }} FCFA</strong> (marge <strong>{{ $margin }}%</strong>).
                L'activité commerciale totalise <strong>{{ $sales['total_orders']??0 }} commandes</strong>
                et la valeur du stock immobilisé s'élève à <strong>{{ number_format($stock['total_value']??0,0,',',' ') }} FCFA</strong>.
                Les membres du Conseil sont invités à valider les orientations stratégiques proposées ci-dessus et à prendre les décisions nécessaires
                pour assurer la croissance et la pérennité de l'entreprise.
            </div>
        </div>

        <div style="margin-top:8mm;display:table;width:100%">
            <div style="display:table-cell;text-align:left">
                <div style="border-top:1px solid #0a0a0a;width:50mm;padding-top:2mm;margin-top:10mm;font-size:7.5px;color:#888">
                    Directeur Général
                </div>
            </div>
            <div style="display:table-cell;text-align:center">
                <div style="border-top:1px solid #0a0a0a;width:50mm;padding-top:2mm;margin-top:10mm;font-size:7.5px;color:#888;margin:10mm auto 0">
                    Directeur Financier
                </div>
            </div>
            <div style="display:table-cell;text-align:right">
                <div style="border-top:1px solid #0a0a0a;width:50mm;padding-top:2mm;margin-top:10mm;font-size:7.5px;color:#888;float:right">
                    Président du Conseil
                </div>
            </div>
        </div>
    </div>

    <div class="page-footer">
        <div class="pf-left">GSIT Mode &amp; Confection — Document confidentiel</div>
        <div class="pf-center">Généré le {{ $generated_at }}</div>
        <div class="pf-right">Page 5 / 5</div>
    </div>
</div>

</body>
</html>
