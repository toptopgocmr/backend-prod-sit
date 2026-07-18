<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GSIT') — Gestion Interne</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:  { DEFAULT: '#E8820C', 50: '#FEF3E2', 100: '#FDE0B4', 500: '#E8820C', 600: '#C46E08', 700: '#9E5806' },
                        dark:     { DEFAULT: '#1A1A2E', 800: '#12121F', 900: '#0D0D18' },
                        surface:  { DEFAULT: '#F8F7F5', card: '#FFFFFF' },
                    },
                    fontFamily: {
                        sans:    ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F8F7F5; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #E8820C40; border-radius: 2px; }
        .nav-link { transition: all 0.15s ease; }
        .nav-link.active { background: linear-gradient(135deg, #E8820C15, #E8820C08); border-left: 3px solid #E8820C; color: #E8820C; }
        .nav-link:not(.active):hover { background: #ffffff10; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,26,46,0.12); }
        .badge-status { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.4} }
        .pulse-dot { animation: pulse-dot 2s infinite; }

        /* Fond décoratif "ciseaux" (motif tissé/couture) sur toutes les pages admin */
        .app-content {
            position: relative;
            background-color: #F8F7F5;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cg fill='none' stroke='%231A1A2E' stroke-opacity='0.05' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cg transform='translate(20 20) rotate(15)'%3E%3Cpath d='M6 6 L34 34 M34 6 L6 34'/%3E%3Ccircle cx='6' cy='6' r='6.5'/%3E%3Ccircle cx='34' cy='6' r='6.5'/%3E%3C/g%3E%3Cg transform='translate(100 90) rotate(-25)'%3E%3Cpath d='M6 6 L34 34 M34 6 L6 34'/%3E%3Ccircle cx='6' cy='6' r='6.5'/%3E%3Ccircle cx='34' cy='6' r='6.5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }
    </style>
    @stack('styles')
</head>
<body class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

    {{-- ── Sidebar ──────────────────────────────────────────────── --}}
    <aside class="flex flex-col bg-dark text-white transition-all duration-300 shrink-0 z-30"
           :class="sidebarOpen ? 'w-64' : 'w-16'"
           style="height:100vh;overflow-y:auto;">

        {{-- Logo GSIT --}}
        <div class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shrink-0 overflow-hidden">
                <img src="{{ asset('images/logo-gsit.jpg') }}" alt="GSIT" class="w-full h-full object-contain">
            </div>
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <p class="font-display font-bold text-base leading-tight tracking-wide">GSIT</p>
                <p class="text-primary text-xs font-semibold tracking-widest uppercase">Gestion Interne</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-4 px-2 space-y-0.5 scrollbar-thin overflow-y-auto">
            @php
            $u = auth()->user();

            // ── Définition des menus par rôle ──────────────────────────────
            $navGroups = [];

            // Tableau de bord : tout le monde
            $navGroups['Principal'] = [
                ['route'=>'dashboard', 'icon'=>'layout-dashboard', 'label'=>'Tableau de bord'],
            ];

            // Ventes : admin + cashier
            if ($u->isAdmin() || $u->isCashier()) {
                $navGroups['Ventes'] = [
                    ['route'=>'orders.index',       'icon'=>'shopping-bag', 'label'=>'Ventes'],
                    ['route'=>'custom-orders.index','icon'=>'scissors',      'label'=>'Sur mesure'],
                    ['route'=>'clients.index',      'icon'=>'users',         'label'=>'Clients'],
                ];
            }
            // Sur mesure seul : couturier
            if ($u->isCouturier()) {
                $navGroups['Commandes'] = [
                    ['route'=>'custom-orders.index','icon'=>'scissors','label'=>'Sur mesure'],
                ];
            }

            // Stock : admin + stock_manager
            if ($u->isAdmin() || $u->isStockManager()) {
                $navGroups['Stock'] = [
                    ['route'=>'products.index',       'icon'=>'package','label'=>'Produits'],
                    ['route'=>'categories.index',     'icon'=>'tag',    'label'=>'Catégories'],
                    ['route'=>'stock.index',          'icon'=>'layers', 'label'=>'Stock'],
                    ['route'=>'purchase-orders.index','icon'=>'truck',  'label'=>'Achats'],
                ];
            }

            // Finance : admin + cashier
            if ($u->isAdmin() || $u->isCashier()) {
                $navGroups['Finance'] = [
                    ['route'=>'finance.index',  'icon'=>'bar-chart-2','label'=>'Finance'],
                    ['route'=>'expenses.index', 'icon'=>'receipt',    'label'=>'Dépenses'],
                    ['route'=>'salaries.index', 'icon'=>'wallet',     'label'=>'Salaires'],
                ];
            }

            // Opérations
            $opsItems = [];
            if ($u->isAdmin() || $u->isCouturier()) {
                $opsItems[] = ['route'=>'atelier.index',    'icon'=>'shirt',    'label'=>'Atelier'];
            }
            if ($u->isAdmin() || $u->isDelivery()) {
                $opsItems[] = ['route'=>'deliveries.index', 'icon'=>'map-pin',  'label'=>'Livraisons'];
            }
            if (!empty($opsItems)) $navGroups['Opérations'] = $opsItems;

            // Maintenance : admin seulement
            if ($u->isAdmin()) {
                $navGroups['Maintenance'] = [
                    ['route'=>'equipment.index',  'icon'=>'tool',  'label'=>'Équipements'],
                    ['route'=>'maintenance.index','icon'=>'wrench','label'=>'Interventions'],
                ];
            }

            // Rapports : admin + cashier
            if ($u->isAdmin() || $u->isCashier()) {
                $navGroups['Rapports'] = [
                    ['route'=>'reports.index','icon'=>'file-bar-chart','label'=>'Rapports'],
                ];
            }
            @endphp

            @foreach($navGroups as $groupName => $items)
                <div x-show="sidebarOpen" class="px-2 pt-4 pb-1">
                    <p class="text-xs font-semibold text-white/30 uppercase tracking-widest">{{ $groupName }}</p>
                </div>
                @foreach($items as $item)
                    <a href="{{ route($item['route']) }}"
                       class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs(rtrim($item['route'],'.index').'*') ? 'active' : 'text-white/70' }}"
                       title="{{ $item['label'] }}">
                        <i data-lucide="{{ $item['icon'] }}" class="shrink-0" style="width:18px;height:18px"></i>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap font-medium">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach

            @if(auth()->user()->isAdmin())
                <div x-show="sidebarOpen" class="px-2 pt-4 pb-1">
                    <p class="text-xs font-semibold text-white/30 uppercase tracking-widest">Admin</p>
                </div>
                <a href="{{ route('users.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('users*') ? 'active' : 'text-white/70' }}">
                    <i data-lucide="shield" style="width:18px;height:18px" class="shrink-0"></i>
                    <span x-show="sidebarOpen" class="font-medium">Utilisateurs</span>
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('settings*') ? 'active' : 'text-white/70' }}">
                    <i data-lucide="settings" style="width:18px;height:18px" class="shrink-0"></i>
                    <span x-show="sidebarOpen" class="font-medium">Paramètres</span>
                </a>
            @endif
        </nav>

        {{-- User --}}
        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3">
                <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full shrink-0">
                <div x-show="sidebarOpen" class="min-w-0">
                    <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/50">{{ auth()->user()->getRoleLabel() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-white/40 hover:text-white transition-colors">
                        <i data-lucide="log-out" style="width:16px;height:16px"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main ──────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-3 flex items-center gap-4 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="flex-1">
                <h1 class="text-base font-bold text-dark font-display">@yield('title', 'Tableau de bord')</h1>
                @hasSection('breadcrumb')
                    <div class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">@yield('breadcrumb')</div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button class="relative text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-primary text-white text-xs rounded-full flex items-center justify-center font-bold">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </button>
                <div class="hidden sm:block text-xs text-gray-400 text-right">
                    <p class="font-medium text-gray-600" id="clock"></p>
                    <p>{{ now()->translatedFormat('l d F Y') }}</p>
                </div>
            </div>
        </header>

        {{-- Flash --}}
        @if(session('success'))
            <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
                 class="mx-6 mt-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
                {{ session('success') }}
                <button @click="show=false" class="ml-auto text-green-500"><i data-lucide="x" style="width:14px;height:14px"></i></button>
            </div>
        @endif
        @if(session('error'))
            <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
                 class="mx-6 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                {{ session('error') }}
                <button @click="show=false" class="ml-auto text-red-500"><i data-lucide="x" style="width:14px;height:14px"></i></button>
            </div>
        @endif

        <main class="flex-1 overflow-y-auto p-6 app-content">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        function updateClock() {
            const el = document.getElementById('clock');
            if (el) el.textContent = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
        }
        updateClock();
        setInterval(updateClock, 30000);
    </script>
    @stack('scripts')
</body>
</html>
