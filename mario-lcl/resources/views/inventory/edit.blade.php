@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Modifier le lieu de stockage</h5>
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

                    <form method="POST" action="{{ route('inventory.update', $inventory['inventoryId']) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="filmId" value="{{ $inventory['filmId'] ?? ($inventory['film']['filmId'] ?? '') }}">

                        <!-- Informations du Film -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Informations du DVD</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ID Inventaire</label>
                                    <p class="form-control-plaintext">{{ $inventory['inventoryId'] }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Film</label>
                                    <p class="form-control-plaintext">{{ $inventory['film']['title'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Année de sortie</label>
                                    <p class="form-control-plaintext">{{ $inventory['film']['releaseYear'] ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Magasin actuel</label>
                                    <p class="form-control-plaintext">Store #{{ $inventory['storeId'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sélection du nouveau magasin -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Modifier le lieu de stockage</h6>

                            <div class="mb-3">
                                <label for="storeId" class="form-label fw-bold">Nouveau magasin <span class="text-danger">*</span></label>
                                <select class="form-select @error('storeId') is-invalid @enderror"
                                        id="storeId"
                                        name="storeId"
                                        required>
                                    <option value="">-- Sélectionnez un magasin --</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store['storeId'] }}"
                                                {{ $store['storeId'] == $inventory['storeId'] ? 'selected' : '' }}>
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
                                    Sélectionnez le nouveau magasin où stocker ce DVD
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
