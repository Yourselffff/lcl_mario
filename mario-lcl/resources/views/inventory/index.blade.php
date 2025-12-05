@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestion de Stock</h5>
                    <a href="{{ route('inventory.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle"></i> Créer un DVD
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (empty($groupedInventories))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Aucun inventaire disponible ou erreur lors de la récupération des données de l'API.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Film</th>
                                        <th>Titre du Film</th>
                                        <th>Année</th>
                                        <th>Nombre d'exemplaires</th>
                                        <th>Nombre de magasins</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupedInventories as $index => $film)
                                        <tr class="clickable-row" data-href="{{ route('inventory.film.show', $film['filmId']) }}" style="cursor: pointer;">
                                            <td>{{ $film['filmId'] }}</td>
                                            <td><strong>{{ $film['title'] }}</strong></td>
                                            <td>{{ $film['releaseYear'] ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $film['totalCopies'] }} DVD(s)</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ count($film['stores']) }} magasin(s)</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('inventory.film.show', $film['filmId']) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> Voir les DVDs
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <script>
                            // Rendre les lignes cliquables
                            document.addEventListener('DOMContentLoaded', function() {
                                const rows = document.querySelectorAll('.clickable-row');
                                rows.forEach(row => {
                                    row.addEventListener('click', function(e) {
                                        // Ne pas naviguer si on clique sur le bouton
                                        if (e.target.closest('a')) {
                                            return;
                                        }
                                        window.location = this.dataset.href;
                                    });
                                });
                            });
                        </script>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Total : <strong>{{ count($groupedInventories) }}</strong> film(s) différent(s) en stock
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection