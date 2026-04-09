{{-- Formulaire de création d'un client. Données envoyées via POST /customers vers CustomerController::store(). --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Ajouter un nouveau client</h4>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf

                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary"><i class="bi bi-person"></i> Informations personnelles</h5>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstName') is-invalid @enderror"
                                       id="firstName" name="firstName" value="{{ old('firstName') }}" required>
                                @error('firstName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastName') is-invalid @enderror"
                                       id="lastName" name="lastName" value="{{ old('lastName') }}" required>
                                @error('lastName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 mt-4">
                            <h5 class="border-bottom pb-2 text-primary"><i class="bi bi-gear"></i> Informations du compte</h5>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="storeId" class="form-label">ID Magasin <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('storeId') is-invalid @enderror"
                                       id="storeId" name="storeId" value="{{ old('storeId', 1) }}" min="1" required>
                                @error('storeId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="addressId" class="form-label">ID Adresse <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('addressId') is-invalid @enderror"
                                       id="addressId" name="addressId" value="{{ old('addressId', 1) }}" min="1" required>
                                @error('addressId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="active" name="active"
                                           value="1" {{ old('active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Client actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Enregistrer le client
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
