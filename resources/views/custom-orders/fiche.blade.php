<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fiche Atelier — {{ $customOrder->reference }}</title>
<style>
:root {
    --purple: #7C3AED;
    --purple-light: #EDE9FE;
    --purple-mid: #C4B5FD;
    --dark: #1A1A2E;
    --gray: #6B7280;
    --gray-light: #F9FAFB;
    --border: #E5E7EB;
    --green: #16a34a;
    --red: #dc2626;
    --orange: #ea580c;
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 13px;
    color: var(--dark);
    background: #F3F4F6;
}

/* ── Barre d'actions ── */
.toolbar {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 32px;
    background: var(--dark);
    color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.toolbar .ref { font-family: monospace; font-size: 13px; font-weight: 700; color: var(--purple-mid); }
.toolbar .sub { font-size: 11px; color: #9CA3AF; margin-top: 1px; }
.toolbar .actions { display: flex; gap: 8px; align-items: center; }
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; border: none; text-decoration: none; transition: opacity 0.15s;
}
.btn:hover { opacity: 0.85; }
.btn-print  { background: var(--purple); color: #fff; }
.btn-dl     { background: #374151; color: #fff; }
.btn-edit   { background: #059669; color: #fff; }
.btn-back   { background: transparent; color: #9CA3AF; border: 1px solid #374151; }
.btn-save   { background: #059669; color: #fff; }
.btn-save:hover { background: #047857; }

/* ── Page A4 ── */
.page {
    max-width: 900px;
    margin: 24px auto;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
}

/* ── En-tête ── */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 28px 36px 24px;
    background: linear-gradient(135deg, var(--dark) 0%, #2D1B69 100%);
    color: #fff;
}
.logo-area { display: flex; align-items: center; gap: 14px; }
.logo-area img { width: 52px; height: 52px; border-radius: 10px; object-fit: contain; background: #fff; padding: 4px; }
.logo-area .company { font-size: 22px; font-weight: 800; letter-spacing: 1px; }
.logo-area .tagline { font-size: 9px; color: var(--purple-mid); font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-top: 2px; }
.header-right { text-align: right; }
.header-right .doc-type { font-size: 11px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 2px; }
.header-right .ref-num { font-size: 20px; font-weight: 800; color: #A78BFA; font-family: monospace; margin: 4px 0; }
.header-right .date { font-size: 11px; color: #9CA3AF; }
.status-pill {
    display: inline-block; margin-top: 8px;
    padding: 4px 14px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
    background: rgba(167,139,250,0.2); color: #C4B5FD; border: 1px solid rgba(167,139,250,0.4);
}
@if($customOrder->delivery_date)
.deadline { margin-top: 6px; font-size: 11px; color: #FCA5A5; font-weight: 600; }
@endif

/* ── Sections ── */
.section {
    padding: 20px 36px;
    border-bottom: 1px solid var(--border);
}
.section:last-child { border-bottom: none; }
.section-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 14px;
}
.section-header .s-icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: var(--purple-light); color: var(--purple);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700;
}
.section-header .s-title {
    font-size: 11px; font-weight: 700; color: var(--purple);
    text-transform: uppercase; letter-spacing: 1.5px;
}

/* ── Grille info ── */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.info-grid .info-block { padding: 0 24px 0 0; }
.info-grid .info-block:last-child { padding: 0 0 0 24px; border-left: 1px solid var(--border); }
.field { margin-bottom: 10px; }
.field .lbl { font-size: 10px; color: var(--gray); margin-bottom: 2px; font-weight: 500; }
.field .val { font-size: 13px; font-weight: 600; color: var(--dark); }
.field .val.accent { font-size: 16px; color: var(--purple); font-weight: 700; }

/* ── Mesures ── */
.measures-group { margin-bottom: 16px; }
.measures-group-title {
    font-size: 10px; font-weight: 700; color: var(--purple);
    text-transform: uppercase; letter-spacing: 1px;
    padding: 4px 10px; background: var(--purple-light); border-radius: 6px;
    display: inline-block; margin-bottom: 10px;
}
.measures-table { width: 100%; border-collapse: collapse; }
.measures-table tr:nth-child(even) td { background: var(--gray-light); }
.measures-table td {
    padding: 5px 10px; font-size: 12px;
    border-bottom: 1px solid var(--border);
}
.measures-table td:first-child { color: var(--gray); width: 30%; }
.measures-table td:nth-child(2) { font-weight: 600; color: var(--dark); width: 20%; }
.measures-table td:nth-child(3) { color: var(--gray); width: 30%; }
.measures-table td:last-child { font-weight: 600; color: var(--dark); width: 20%; }
/* champs éditables */
.m-input {
    width: 70px; padding: 3px 6px; border: 1px solid var(--purple-mid);
    border-radius: 5px; font-size: 12px; font-weight: 600; text-align: center;
    color: var(--dark); background: #FAFAFA; outline: none;
}
.m-input:focus { border-color: var(--purple); background: var(--purple-light); }
.m-unit { font-size: 10px; color: var(--gray); margin-left: 4px; }

/* vue impression — input → valeur statique */
@media print {
    body { background: #fff; }
    .toolbar { display: none !important; }
    .page { margin: 0; border-radius: 0; box-shadow: none; max-width: 100%; }
    .m-input {
        border: none; border-bottom: 1px solid #ccc;
        background: transparent; border-radius: 0;
        width: 60px;
    }
}

/* ── Coûts ── */
.costs-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin-bottom: 16px; }
.cost-card {
    padding: 12px 16px; border-radius: 10px;
    background: var(--gray-light); border: 1px solid var(--border);
}
.cost-card.total-card { background: var(--purple-light); border-color: var(--purple-mid); }
.cost-card .c-lbl { font-size: 10px; color: var(--gray); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.cost-card .c-val { font-size: 18px; font-weight: 800; color: var(--dark); margin-top: 4px; }
.cost-card.total-card .c-val { color: var(--purple); font-size: 22px; }

.payment-info { display: flex; gap: 24px; align-items: center; padding: 12px 16px; background: var(--gray-light); border-radius: 8px; }
.payment-info .p-item .p-lbl { font-size: 10px; color: var(--gray); }
.payment-info .p-item .p-val { font-size: 14px; font-weight: 700; }
.pill {
    padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;
}
.pill-paid    { background: #DCFCE7; color: #15803D; }
.pill-partial { background: #FEF9C3; color: #A16207; }
.pill-unpaid  { background: #FEE2E2; color: #B91C1C; }

/* ── Signatures ── */
.sign-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
.sign-box { border: 1.5px dashed #D1D5DB; border-radius: 8px; padding: 14px 16px; min-height: 80px; }
.sign-box .s-lbl { font-size: 10px; color: var(--gray); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
.sign-box .s-name { font-size: 12px; font-weight: 600; color: var(--dark); margin-top: 4px; }

/* ── Pied ── */
.footer {
    padding: 10px 36px;
    background: var(--gray-light);
    border-top: 1px solid var(--border);
    display: flex; justify-content: space-between;
    font-size: 10px; color: #9CA3AF;
}

/* ── Notes box ── */
.notes-box { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #78350F; }

/* ── Tissu pill ── */
.tissu-info { display: flex; gap: 16px; flex-wrap: wrap; }
.tissu-badge { display: flex; flex-direction: column; padding: 8px 16px; background: var(--purple-light); border-radius: 8px; }
.tissu-badge .tb-lbl { font-size: 9px; color: var(--purple); font-weight: 600; text-transform: uppercase; }
.tissu-badge .tb-val { font-size: 13px; font-weight: 700; color: var(--dark); margin-top: 2px; }

/* ── Model photo ── */
.model-photo { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 2px solid var(--purple-mid); float: right; margin: 0 0 8px 16px; }

@media print {
    body { background: #fff; }
    .toolbar, .no-print { display: none !important; }
    .page { margin: 0; border-radius: 0; box-shadow: none; max-width: 100%; }
    .m-input { border: none; border-bottom: 1px solid #ccc; background: transparent; border-radius: 0; width: 60px; }
}
</style>
</head>
<body>

{{-- ── Barre d'outils ── --}}
<div class="toolbar">
    <div>
        <div class="ref">{{ $customOrder->reference }}</div>
        <div class="sub">Fiche Atelier — {{ $customOrder->client->full_name }}</div>
    </div>
    <div class="actions">
        <a href="{{ route('custom-orders.edit', $customOrder) }}" class="btn btn-edit">✏ Modifier la commande</a>
        <button form="measures-form" type="submit" class="btn btn-save">💾 Enregistrer les mesures</button>
        <button onclick="window.print()" class="btn btn-print">🖨 Imprimer</button>
        <a href="{{ route('custom-orders.fiche', array_merge([$customOrder->id], ['download'=>1])) }}" class="btn btn-dl">⬇ PDF</a>
        <a href="{{ route('custom-orders.show', $customOrder) }}" class="btn btn-back">← Retour</a>
    </div>
</div>

{{-- ── Page ── --}}
<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div class="logo-area">
            <img src="{{ asset('images/logo-gsit.jpg') }}" alt="GSIT">
            <div>
                <div class="company">GSIT</div>
                <div class="tagline">Haute Couture</div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">Fiche Atelier</div>
            <div class="ref-num">{{ $customOrder->reference }}</div>
            <div class="date">Créée le {{ $customOrder->created_at->format('d/m/Y à H:i') }}</div>
            <div class="status-pill">{{ $customOrder->getStatusLabel() }}</div>
            @if($customOrder->delivery_date)
            <div class="deadline">📅 Livraison : {{ $customOrder->delivery_date->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    {{-- Client + Modèle --}}
    <div class="section">
        <div class="info-grid">
            <div class="info-block">
                <div class="section-header">
                    <div class="s-icon">👤</div>
                    <div class="s-title">Client</div>
                </div>
                <div class="field"><div class="lbl">Nom complet</div><div class="val accent">{{ $customOrder->client->full_name }}</div></div>
                <div class="field"><div class="lbl">Téléphone</div><div class="val">{{ $customOrder->client->phone }}</div></div>
                <div class="field"><div class="lbl">Genre</div><div class="val">{{ ucfirst($customOrder->gender) }}</div></div>
                @if($customOrder->couturier)
                <div class="field"><div class="lbl">Couturier assigné</div><div class="val">{{ $customOrder->couturier->name }}</div></div>
                @endif
            </div>
            <div class="info-block">
                <div class="section-header">
                    <div class="s-icon">👗</div>
                    <div class="s-title">Modèle &amp; vêtement</div>
                </div>
                @if($customOrder->model_photo)
                <img src="{{ asset('storage/'.$customOrder->model_photo) }}" class="model-photo" alt="Photo modèle">
                @endif
                <div class="field"><div class="lbl">Type de vêtement</div><div class="val">{{ ucfirst($customOrder->garment_type) }}</div></div>
                @if($customOrder->model_name)
                <div class="field"><div class="lbl">Nom du modèle</div><div class="val">{{ $customOrder->model_name }}</div></div>
                @endif
                @if($customOrder->model_description)
                <div class="field"><div class="lbl">Description</div><div class="val" style="font-size:12px;font-weight:400;color:#374151;">{{ $customOrder->model_description }}</div></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tissu --}}
    @if($customOrder->fabric || $customOrder->fabric_color || $customOrder->fabric_meters)
    <div class="section">
        <div class="section-header">
            <div class="s-icon">🧵</div>
            <div class="s-title">Tissu</div>
        </div>
        <div class="tissu-info">
            @if($customOrder->fabric)
            <div class="tissu-badge"><span class="tb-lbl">Tissu</span><span class="tb-val">{{ $customOrder->fabric->name }}</span></div>
            @endif
            @if($customOrder->fabric_meters)
            <div class="tissu-badge"><span class="tb-lbl">Mètres</span><span class="tb-val">{{ $customOrder->fabric_meters }} m</span></div>
            @endif
            @if($customOrder->fabric_color)
            <div class="tissu-badge"><span class="tb-lbl">Couleur</span><span class="tb-val">{{ $customOrder->fabric_color }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Flash mesures sauvegardées --}}
    @if(session('measures_saved'))
    <div style="position:fixed;top:70px;right:24px;z-index:200;background:#059669;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.2);">
        ✓ Mesures enregistrées avec succès
    </div>
    @endif

    {{-- Mesures --}}
    <form id="measures-form" method="POST" action="{{ route('custom-orders.saveMeasures', $customOrder) }}">
        @csrf
    <div class="section">
        <div class="section-header">
            <div class="s-icon">📏</div>
            <div class="s-title">Mesures (cm)</div>
        </div>

        @php
        $savedValues = $customOrder->measurement?->values ?? [];
        // Helper pour récupérer une valeur sauvegardée
        $val = fn($key) => $savedValues[$key] ?? null;
        @endphp

        @if($customOrder->gender === 'femme')
        {{-- FEMME --}}
        <div class="measures-group">
            <div class="measures-group-title">Haut — robe, chemisier, veste</div>
            <table class="measures-table">
                @php $fields = [
                    'f_longueur_epaule'=>'Longueur épaule','f_tour_poitrine'=>'Tour de poitrine',
                    'f_tour_taille'=>'Tour de taille','f_petites_hanches'=>'Tour petites hanches',
                    'f_grandes_hanches'=>'Tour grandes hanches','f_hauteur_saillant'=>'Hauteur saillant',
                    'f_ecart_saillants'=>'Écart des saillants','f_hauteur_buste_devant'=>'Hauteur buste devant',
                    'f_hauteur_buste_dos'=>'Hauteur buste dos','f_longueur_cote_buste'=>'Longueur côté buste',
                    'f_tour_manche'=>'Tour de manche','f_longueur_manche'=>'Longueur de manche',
                    'f_carrure_devant'=>'Carrure devant','f_carrure_dos'=>'Carrure dos',
                    'f_longueur_veste'=>'Longueur veste','f_tour_encolure'=>'Tour encolure',
                    'f_hauteur_fessier'=>'Hauteur fessier',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>
        <div class="measures-group">
            <div class="measures-group-title">Bas — pantalon</div>
            <table class="measures-table">
                @php $fields = [
                    'f_tour_ceinture'=>'Tour de ceinture','f_longueur_assise'=>"Longueur d'assise",
                    'f_tour_bassin'=>'Tour de bassin','f_fourche_devant'=>'Fourche devant',
                    'f_hauteur_bassin'=>'Hauteur bassin','f_fourche_dos'=>'Fourche dos',
                    'f_entrejambe'=>'Entrejambe','f_tour_cuisse'=>'Tour de cuisse',
                    'f_longueur_pantalon'=>'Longueur pantalon','f_largeur_bas'=>'Largeur du bas',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>
        <div class="measures-group">
            <div class="measures-group-title">Longueurs robes &amp; jupes</div>
            <table class="measures-table">
                @php $fields = [
                    'f_robe_longue'=>'Robe longue','f_robe_avant_genoux'=>'Robe avant genoux',
                    'f_robe_apres_genoux'=>'Robe après genoux','f_robe_trois_quarts'=>'Robe trois quarts',
                    'f_jupe_longue'=>'Jupe longue','f_jupe_genoux'=>'Jupe genoux',
                    'f_jupe_trois_quarts'=>'Jupe trois quarts',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>

        @elseif($customOrder->gender === 'homme')
        {{-- HOMME --}}
        <div class="measures-group">
            <div class="measures-group-title">Haut — chemise, veste, boubou</div>
            <table class="measures-table">
                @php $fields = [
                    'h_epaule'=>'Épaule','h_manche_longue'=>'Manche longue',
                    'h_manche_courte'=>'Manche courte','h_tour_poitrine'=>'Tour de poitrine',
                    'h_tour_taille_ventre'=>'Tour taille / ventre','h_carrure_dos'=>'Carrure dos',
                    'h_longueur_chemise'=>'Longueur chemise','h_longueur_veste'=>'Longueur veste',
                    'h_contour_manche'=>'Contour manche','h_tour_col'=>'Tour de col',
                    'h_longueur_devant'=>'Longueur devant',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>
        <div class="measures-group">
            <div class="measures-group-title">Bas — pantalon</div>
            <table class="measures-table">
                @php $fields = [
                    'h_tour_ceinture'=>'Tour de ceinture','h_tour_bassin'=>'Tour de bassin',
                    'h_tour_cuisse'=>'Tour de cuisse','h_largeur_genoux'=>'Largeur genoux',
                    'h_tour_mollet'=>'Tour de mollet','h_diametre_bas'=>'Diamètre du bas',
                    'h_longueur_pantalon'=>'Longueur pantalon','h_longueur_culotte'=>'Longueur culotte',
                    'h_pisset'=>'Pisset (braguette)',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>

        @else
        {{-- ENFANT --}}
        <div class="measures-group">
            <div class="measures-group-title">Mesures enfant</div>
            <table class="measures-table">
                @php $fields = [
                    'e_tour_poitrine'=>'Tour de poitrine','e_tour_taille'=>'Tour de taille',
                    'e_tour_hanches'=>'Tour de hanches','e_epaule'=>'Épaule',
                    'e_longueur_manche'=>'Longueur manche','e_longueur_veste'=>'Longueur veste/robe',
                    'e_tour_ceinture'=>'Tour de ceinture','e_longueur_pantalon'=>'Longueur pantalon',
                    'e_entrejambe'=>'Entrejambe','e_tour_cuisse'=>'Tour de cuisse',
                ];
                $pairs = array_chunk(array_keys($fields), 2, true);
                @endphp
                @foreach($pairs as $pair)
                <tr>
                    @foreach($pair as $key)
                    <td>{{ $fields[$key] }}</td>
                    <td>
                        <input type="number" name="measures[{{ $key }}]"
                               class="m-input" min="1" max="999" step="0.5"
                               value="{{ $val($key) }}" placeholder="—">
                        <span class="m-unit">cm</span>
                    </td>
                    @endforeach
                    @if(count($pair) === 1)<td colspan="2"></td>@endif
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        {{-- Bouton save en bas aussi --}}
        <div style="padding:16px 0 4px; display:flex; justify-content:flex-end;" class="no-print">
            <button type="submit" class="btn btn-save" style="font-size:13px;padding:10px 24px;">
                💾 Enregistrer les mesures
            </button>
        </div>
    </div>
    </form>

    {{-- Accessoires --}}
    @if($customOrder->accessories && count($customOrder->accessories) > 0)
    <div class="section">
        <div class="section-header">
            <div class="s-icon">🧷</div>
            <div class="s-title">Accessoires</div>
        </div>
        <table class="measures-table">
            <tr style="background:var(--purple-light);">
                <td style="font-weight:700;color:var(--purple);">Désignation</td>
                <td style="font-weight:700;color:var(--purple);">Quantité</td>
            </tr>
            @foreach($customOrder->accessories as $acc)
            <tr>
                <td>{{ $acc['name'] ?? '—' }}</td>
                <td>{{ $acc['qty'] ?? 1 }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Notes --}}
    @if($customOrder->notes)
    <div class="section">
        <div class="section-header">
            <div class="s-icon">📝</div>
            <div class="s-title">Notes atelier</div>
        </div>
        <div class="notes-box">{{ $customOrder->notes }}</div>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="section">
        <div class="section-header">
            <div class="s-icon">✍</div>
            <div class="s-title">Signatures</div>
        </div>
        <div class="sign-grid">
            <div class="sign-box">
                <div class="s-lbl">Signature client</div>
                <div class="s-name">{{ $customOrder->client->full_name }}</div>
            </div>
            <div class="sign-box">
                <div class="s-lbl">Signature couturier</div>
                @if($customOrder->couturier)
                <div class="s-name">{{ $customOrder->couturier->name }}</div>
                @endif
            </div>
            <div class="sign-box">
                <div class="s-lbl">Visa responsable</div>
            </div>
        </div>
    </div>

    {{-- Pied --}}
    <div class="footer">
        <span>GSIT Haute Couture — Fiche Atelier &copy; {{ date('Y') }}</span>
        <span>{{ $customOrder->reference }} · Imprimé le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
</body>
</html>