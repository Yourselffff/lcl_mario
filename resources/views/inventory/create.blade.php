{{-- Formulaire de création d'un DVD (inventaire). Accepte filmId en query string pour pré-sélectionner un film. --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Créer un nouveau DVD</h5>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('inventory.store') }}">
                        @csrf

                        <!-- Sélection du film -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Sélection du film</h6>

                            @if(empty($films))
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Aucun film disponible. Veuillez d'abord créer un film dans la section "Gestion du catalogue de films".
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> {{ count($films) }} film(s) disponible(s)
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="filmId" class="form-label fw-bold">Film <span class="text-danger">*</span></label>
                                <select class="form-select @error('filmId') is-invalid @enderror"
                                        id="filmId"
                                        name="filmId"
                                        required>
                                    <option value="">-- Sélectionnez un film --</option>
                                    @foreach($films as $film)
                                        <option value="{{ $film['filmId'] }}"
                                                {{ (old('filmId') == $film['filmId'] || (isset($preselectedFilmId) && $preselectedFilmId == $film['filmId'])) ? 'selected' : '' }}>
                                            {{ $film['title'] }}
                                            @if(isset($film['releaseYear']))
                                                ({{ $film['releaseYear'] }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('filmId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Sélectionnez le film pour lequel vous souhaitez créer un nouveau DVD
                                </div>
                            </div>
                        </div>

                        <!-- Sélection du magasin -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Lieu de stockage</h6>

                            <div class="mb-3">
                                <label for="storeId" class="form-label fw-bold">Magasin <span class="text-danger">*</span></label>
                                <select class="form-select @error('storeId') is-invalid @enderror"
                                        id="storeId"
                                        name="storeId"
                                        required>
                                    <option value="">-- Sélectionnez un magasin --</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store['storeId'] }}"
                                                {{ old('storeId') == $store['storeId'] ? 'selected' : '' }}>
                                            Store #{{ $store['storeId'] }}
                                            @if(isset($store['address']))
                                                - {{ $store['address']['address'] ?? '' }}
                                                @if(isset($store['address']['city']))
                                                    , {{ $store['address']['city'] }}
                                                @endif
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('storeId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Sélectionnez le magasin où stocker ce nouveau DVD
                                </div>
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Note :</strong> Vous créez un nouvel exemplaire (DVD) d'un film existant. Si le film n'existe pas encore dans la liste, vous devez d'abord le créer dans la section "Gestion du catalogue de films".
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Créer le DVD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection