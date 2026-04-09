{{-- Fiche détail d'un client. Données passées par CustomerController::show() via la variable $customer. --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du client</h5>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h3>{{ $customer['firstName'] ?? '' }} {{ $customer['lastName'] ?? '' }}</h3>
                            <p class="text-muted">{{ $customer['email'] ?? '' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($customer['active'] ?? false)
                                <span class="badge bg-success fs-6">Actif</span>
                            @else
                                <span class="badge bg-secondary fs-6">Inactif</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <dl class="row">
                        <dt class="col-sm-3">ID Client</dt>
                        <dd class="col-sm-9">{{ $customer['customerId'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Prénom</dt>
                        <dd class="col-sm-9">{{ $customer['firstName'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Nom</dt>
                        <dd class="col-sm-9">{{ $customer['lastName'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $customer['email'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">ID Magasin</dt>
                        <dd class="col-sm-9">{{ $customer['storeId'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">ID Adresse</dt>
                        <dd class="col-sm-9">{{ $customer['addressId'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Date de création</dt>
                        <dd class="col-sm-9">{{ $customer['createDate'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Dernière mise à jour</dt>
                        <dd class="col-sm-9">{{ $customer['lastUpdate'] ?? 'N/A' }}</dd>

                        @if(!empty($customer['rentals']))
                        <dt class="col-sm-3">Locations</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-info">{{ count($customer['rentals']) }} location(s)</span>
                        </dd>
                        @endif
                    </dl>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.edit', $customer['customerId']) }}" class="btn btn-warning text-white">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <form action="{{ route('customers.destroy', $customer['customerId']) }}"
                              method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
