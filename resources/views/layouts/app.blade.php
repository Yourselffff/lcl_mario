{{-- =============================================================================
     Layout principal de l'application.
     Toutes les vues héritent de ce template via @extends('layouts.app').
     Le contenu de chaque page est injecté dans @yield('content').
     ============================================================================= --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Token CSRF injecté dans le meta : utilisé par les requêtes AJAX (fetch/axios) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    {{-- Compilation des assets CSS/JS via Vite (resources/sass/app.scss + resources/js/app.js) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">

        {{-- =====================================================================
             Barre de navigation principale
             ===================================================================== --}}
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    {{-- Liens de navigation (visibles uniquement si connecté) --}}
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('films.*') ? 'active' : '' }}" href="{{ route('films.index') }}">
                                    Gestion du catalogue de films
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                    Gestion de Stock
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    Clients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('rentals.*') ? 'active' : '' }}" href="{{ route('rentals.index') }}">
                                    Locations
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">

                        {{-- Sélecteur de source de données (API locale / distante) --}}
                        <li class="nav-item dropdown me-2">
                            @php $currentSource = session('toad_source', 'local'); @endphp
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                @if($currentSource === 'remote')
                                    <span class="badge bg-success">API distante</span>
                                @else
                                    <span class="badge bg-secondary">API locale</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Source de données</h6></li>
                                <li>
                                    {{-- Formulaire POST pour basculer vers l'API locale --}}
                                    <form action="{{ route('data-source.switch') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="source" value="local">
                                        <button type="submit" class="dropdown-item {{ $currentSource === 'local' ? 'active' : '' }}">
                                            <i class="bi bi-hdd me-2"></i>API locale <small class="text-muted">(localhost:8180)</small>
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    {{-- Formulaire POST pour basculer vers l'API distante --}}
                                    <form action="{{ route('data-source.switch') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="source" value="remote">
                                        <button type="submit" class="dropdown-item {{ $currentSource === 'remote' ? 'active' : '' }}">
                                            <i class="bi bi-cloud me-2"></i>API distante <small class="text-muted">(rftg.mtb111.com)</small>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>

                        {{-- Liens connexion / déconnexion --}}
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            {{-- Menu déroulant utilisateur connecté --}}
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    {{-- Déconnexion via formulaire POST (méthode sécurisée, pas un simple lien GET) --}}
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Zone de contenu principal : chaque vue injecte son HTML ici --}}
        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
