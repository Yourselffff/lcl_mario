{{-- =============================================================================
     Page d'accueil publique (accessible sans connexion).
     Présente les fonctionnalités de l'application et propose la connexion.
     ============================================================================= --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Section héro : titre et boutons d'action principaux --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 text-center">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="card-body py-5 text-white">
                    <i class="bi bi-collection-play-fill" style="font-size: 4rem;"></i>
                    <h1 class="mt-3 fw-bold">{{ config('app.name', 'Application Mario') }}</h1>
                    <p class="lead mb-4 opacity-75">Système de gestion de location de DVDs — Catalogue, Stock, Clients &amp; Locations</p>

                    {{-- @guest / @else / @endguest : affiche différents boutons selon l'état de connexion --}}
                    @guest
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">
                                    <i class="bi bi-person-plus me-2"></i>Créer un compte
                                </a>
                            @endif
                        </div>
                    @else
                        <a href="{{ route('home') }}" class="btn btn-light btn-lg px-4">
                            <i class="bi bi-speedometer2 me-2"></i>Accéder au Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    {{-- Présentation des 4 modules principaux de l'application --}}
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-3">
            <div class="card h-100 text-center shadow-sm border-0">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                            <i class="bi bi-film fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold">Catalogue Films</h5>
                    <p class="text-muted small mb-0">Parcourez et gérez l'ensemble du catalogue de films disponibles à la location.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 text-center shadow-sm border-0">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <span class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                            <i class="bi bi-box-seam fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold">Gestion du Stock</h5>
                    <p class="text-muted small mb-0">Suivez la disponibilité des DVDs et gérez l'inventaire physique en temps réel.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 text-center shadow-sm border-0">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                            <i class="bi bi-people fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold">Clients</h5>
                    <p class="text-muted small mb-0">Gérez la base de données clients et consultez leur historique de locations.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 text-center shadow-sm border-0">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <span class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold">Locations</h5>
                    <p class="text-muted small mb-0">Suivez les locations en cours, les retours et l'historique des transactions.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Accès rapide affiché uniquement pour les utilisateurs connectés (@auth) --}}
    @auth
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-lightning-charge me-2 text-primary"></i>Accès rapide</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <a href="{{ route('films.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-film me-2"></i>Films
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-box-seam me-2"></i>Inventaire
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-people me-2"></i>Clients
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="{{ route('rentals.index') }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-calendar-check me-2"></i>Locations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

    {{-- Message d'invitation pour les visiteurs non connectés --}}
    @guest
    <div class="row justify-content-center mt-2">
        <div class="col-md-10">
            <div class="alert alert-light border text-center mb-0">
                <i class="bi bi-lock me-2 text-muted"></i>
                <span class="text-muted">Connectez-vous pour accéder à la gestion du catalogue, du stock, des clients et des locations.</span>
            </div>
        </div>
    </div>
    @endguest

</div>
@endsection
