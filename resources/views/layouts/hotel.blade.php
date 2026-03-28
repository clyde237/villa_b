<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Villa Boutanga PMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-accent/30 font-body lg:flex lg:h-screen lg:overflow-hidden">

    <div id="mobile-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden" onclick="closeMobileSidebar()"></div>

    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-72 max-w-[85vw] bg-primary lg:static lg:flex lg:w-48 lg:max-w-none lg:flex-shrink-0 lg:flex-col lg:h-full">
        <div class="flex h-full w-full flex-col">
            <div class="px-4 py-5 border-b border-surface-dark">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                        <img src="{{ asset('images/logo.png') }}"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                            class="bg-white w-full h-full object-cover">
                        <div class="w-full h-full bg-secondary rounded-full items-center justify-center hidden">
                            <span class="text-primary font-heading font-bold text-sm">VB</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-white font-heading font-semibold text-sm leading-tight">Villa Boutanga</p>
                        <p class="text-secondary text-xs">PMS v1.0</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
                <div>
                    <p class="text-secondary/40 text-[10px] font-semibold uppercase tracking-widest mb-2 px-2">Hôtel</p>
                    <ul class="space-y-0.5">
                        <x-sidebar-link route="dashboard" icon="grid">Tableau de bord</x-sidebar-link>
                        <x-sidebar-link route="rooms.index" icon="door">Chambres</x-sidebar-link>
                        <li>
                            <a href="{{ route('bookings.index') }}"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-xs font-medium transition-all
                                {{ request()->routeIs('bookings.*') || request()->routeIs('groups.*')
                                    ? 'bg-[#4a2a14] text-white'
                                    : 'text-[#c4a882] hover:bg-[#4a2a14] hover:text-white' }}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                Réservations
                            </a>

                            @if(request()->routeIs('bookings.*') || request()->routeIs('groups.*'))
                            <ul class="mt-0.5 ml-4 space-y-0.5 border-l border-secondary/20 pl-3">
                                <li>
                                    <a href="{{ route('bookings.index') }}"
                                        class="flex items-center gap-2 py-1.5 text-xs font-medium transition-all
                                        {{ request()->routeIs('bookings.*')
                                            ? 'text-white'
                                            : 'text-[#c4a882] hover:text-white' }}">
                                        <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                        Individuelles
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('groups.index') }}"
                                        class="flex items-center gap-2 py-1.5 text-xs font-medium transition-all
                                        {{ request()->routeIs('groups.*')
                                            ? 'text-white'
                                            : 'text-[#c4a882] hover:text-white' }}">
                                        <i data-lucide="users" class="w-3 h-3 flex-shrink-0"></i>
                                        Groupes
                                    </a>
                                </li>
                            </ul>
                            @endif
                        </li>
                        <x-sidebar-link route="housekeeping.index" icon="sparkles">Housekeeping</x-sidebar-link>
                    </ul>
                </div>

                <div>
                    <p class="text-secondary/40 text-[10px] font-semibold uppercase tracking-widest mb-2 px-2">Restaurant</p>
                    <ul class="space-y-0.5">
                        <x-sidebar-link route="#" icon="receipt">Commandes</x-sidebar-link>
                        <x-sidebar-link route="#" icon="book">Menu</x-sidebar-link>
                    </ul>
                </div>

                <div>
                    <p class="text-secondary/40 text-[10px] font-semibold uppercase tracking-widest mb-2 px-2">Gestion</p>
                    <ul class="space-y-0.5">
                        <x-sidebar-link route="customers.index" icon="users">Clients</x-sidebar-link>
                        <x-sidebar-link route="#" icon="box">Inventaires</x-sidebar-link>
                        <x-sidebar-link route="#" icon="user-cog">Utilisateurs</x-sidebar-link>
                    </ul>
                </div>
            </nav>

            <div class="px-3 py-4 border-t border-surface-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center flex-shrink-0">
                            <span class="text-primary font-semibold text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-xs font-medium truncate">{{ Auth::user()->name }}</p>
                            <p class="text-secondary/60 text-[10px] capitalize">{{ Auth::user()->role ?? 'Admin' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-secondary/40 hover:text-secondary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col lg:overflow-hidden">
        <header class="bg-accent/30 border-b border-secondary/20 px-4 py-3 lg:px-8 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="openMobileSidebar()" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/20 bg-white text-primary lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <p class="text-primary font-medium text-sm">@yield('title', 'Tableau de bord')</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:flex items-center gap-1.5 text-xs text-green-600 font-medium">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    En ligne
                </span>
                <span class="text-xs text-primary/50">
                    {{ ucfirst(\Carbon\Carbon::now()->locale('fr')->isoFormat('ddd. D MMM')) }}
                </span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto px-4 py-4 lg:px-8 lg:py-6">
            @yield('content')
        </main>
    </div>

    <x-access-denied-popup />

    <script>
    window.openMobileSidebar = function() {
        document.getElementById('mobile-sidebar').classList.remove('hidden');
        document.getElementById('mobile-sidebar-backdrop').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeMobileSidebar = function() {
        document.getElementById('mobile-sidebar').classList.add('hidden');
        document.getElementById('mobile-sidebar-backdrop').classList.add('hidden');
        document.body.style.overflow = '';
    };
    </script>

</body>

</html>
