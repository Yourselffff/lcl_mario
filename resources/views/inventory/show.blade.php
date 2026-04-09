{{-- DVDs d'un film spécifique. La disponibilité est vérifiée à la demande via AJAX (GET /inventory/{id}/check-availability). --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">DVDs du film : {{ $film['title'] }}</h5>
                    <div>
                        <a href="{{ route('inventory.create', ['filmId' => $film['filmId']]) }}" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-plus-circle"></i> Ajouter un DVD
                        </a>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Informations du film -->
                    <div class="alert alert-info mb-4">
                        <h6><strong>{{ $film['title'] }}</strong></h6>
                        @if($film['releaseYear'])
                            <p class="mb-0"><strong>Année :</strong> {{ $film['releaseYear'] }}</p>
                        @endif
                        @if($film['description'])
                            <p class="mb-0"><strong>Description :</strong> {{ $film['description'] }}</p>
                        @endif
                        <p class="mb-0"><strong>Nombre total de DVDs :</strong> {{ count($inventories) }}</p>
                    </div>

                    <!-- Tableau des DVDs -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 30px;">
                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                    </th>
                                    <th>ID Inventaire</th>
                                    <th>Magasin</th>
                                    <th>Disponibilité</th>
                                    <th>Dernière mise à jour</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $inventory)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="inventory-checkbox" value="{{ $inventory['inventoryId'] }}" onchange="updateDeleteButton()">
                                        </td>
                                        <td>{{ $inventory['inventoryId'] }}</td>
                                        <td>Store #{{ $inventory['storeId'] }}</td>
                                        <td class="inventory-availability" data-inventory-id="{{ $inventory['inventoryId'] }}">
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-hourglass-split"></i> Chargement...
                                            </span>
                                        </td>
                                        <td>{{ $inventory['lastUpdate'] ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('inventory.edit', $inventory['inventoryId']) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" onclick="deleteSelected()" disabled>
                            <i class="bi bi-trash"></i> Supprimer la sélection
                        </button>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle"></i>
                            Total : <strong>{{ count($inventories) }}</strong> DVD(s)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Charger la disponibilité de tous les DVDs au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        loadAllAvailabilities();
    });

    function loadAllAvailabilities() {
        const cells = document.querySelectorAll('.inventory-availability[data-inventory-id]');

        cells.forEach(cell => {
            const inventoryId = cell.getAttribute('data-inventory-id');

            fetch(`/inventory/${inventoryId}/check-availability`)
                .then(response => response.json())
                .then(data => {
                    if (data.isAvailable === true) {
                        cell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Disponible</span>';
                    } else if (data.isAvailable === false) {
                        cell.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Loué</span>';
                    } else {
                        cell.innerHTML = '<span class="badge bg-secondary"><i class="bi bi-question-circle"></i> Inconnu</span>';
                    }
                })
                .catch(error => {
                    console.error('Error loading availability for inventory ' + inventoryId + ':', error);
                    cell.innerHTML = '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Erreur</span>';
                });
        });
    }

    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.inventory-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateDeleteButton();
    }

    function updateDeleteButton() {
        const checkboxes = document.querySelectorAll('.inventory-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        deleteBtn.disabled = checkboxes.length === 0;
    }

    function deleteSelected() {
        const checkboxes = document.querySelectorAll('.inventory-checkbox:checked');
        let inventoryIds = [];

        checkboxes.forEach(checkbox => {
            inventoryIds.push(parseInt(checkbox.value));
        });

        if (inventoryIds.length === 0) {
            alert('Aucun élément sélectionné');
            return;
        }

        const message = 'Êtes-vous sûr de vouloir supprimer ' + inventoryIds.length + ' exemplaire(s) ?';
        if (confirm(message)) {
            // Désactiver le bouton pendant la suppression
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Suppression...';

            // Appel AJAX pour supprimer
            fetch('/inventory/delete-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    inventoryIds: inventoryIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Recharger la page pour voir les changements
                    window.location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="bi bi-trash"></i> Supprimer la sélection';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression des DVDs');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="bi bi-trash"></i> Supprimer la sélection';
            });
        }
    }
</script>

@endsection