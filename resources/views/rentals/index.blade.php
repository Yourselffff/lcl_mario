
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestion des locations</h5>
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

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">Total : <strong id="totalRentals">-</strong> location(s)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="statusFilter" class="mb-0">Statut :</label>
                            <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Toutes</option>
                                <option value="3">En cours</option>
                                <option value="2">Dans le panier</option>
                                <option value="1">Terminées</option>
                            </select>
                            <label for="limit" class="mb-0 ms-2">Afficher :</label>
                            <select id="limit" class="form-select form-select-sm" style="width: auto;">
                                @foreach ($allowedLimits as $l)
                                    <option value="{{ $l }}">{{ $l }}</option>
                                @endforeach
                            </select>
                            <span class="text-muted">par page</span>
                        </div>
                    </div>

                    <div id="loading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>

                    <div id="noRentals" class="alert alert-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucune location disponible ou erreur lors de la récupération des données de l'API.
                    </div>

                    <div id="rentalsContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Client ID</th>
                                        <th>Film</th>
                                        <th>Inventaire ID</th>
                                        <th>Date de location</th>
                                        <th>Date de retour</th>
                                        <th>Statut</th>
                                        <th>Modifier statut</th>
                                    </tr>
                                </thead>
                                <tbody id="rentalsTableBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="paginationContainer" class="mt-3 d-flex justify-content-between align-items-center">
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle"></i>
                                Page <strong id="currentPageInfo">1</strong> sur <strong id="totalPagesInfo">1</strong>
                            </p>
                            <nav aria-label="Pagination des locations">
                                <ul class="pagination mb-0" id="paginationList">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const RentalsPagination = {
        currentPage: 1,
        limit: 10,
        totalPages: 1,
        status: '',
        csrfToken: '{{ csrf_token() }}',
        dataUrl: '{{ route("rentals.data") }}',

        init() {
            this.limit = 10;
            document.getElementById('limit').value = this.limit;
            this.loadRentals();

            document.getElementById('limit').addEventListener('change', (e) => {
                this.limit = parseInt(e.target.value);
                this.currentPage = 1;
                this.loadRentals();
            });

            document.getElementById('statusFilter').addEventListener('change', (e) => {
                this.status = e.target.value;
                this.currentPage = 1;
                this.loadRentals();
            });
        },

        async loadRentals() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('rentalsContainer').style.display = 'none';
            document.getElementById('noRentals').style.display = 'none';

            try {
                const body = {
                    page: this.currentPage,
                    limit: this.limit,
                };
                if (this.status !== '') {
                    body.status = parseInt(this.status);
                }

                const response = await fetch(this.dataUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                });

                const data = await response.json();
                document.getElementById('loading').style.display = 'none';

                if (!data.rentals || data.rentals.length === 0) {
                    document.getElementById('noRentals').style.display = 'block';
                    return;
                }

                this.totalPages = data.totalPages;
                this.renderRentals(data.rentals);
                this.renderPagination(data);
                document.getElementById('rentalsContainer').style.display = 'block';

            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('noRentals').style.display = 'block';
            }
        },

        renderRentals(rentals) {
            const tbody = document.getElementById('rentalsTableBody');
            tbody.innerHTML = '';
            const now = new Date();

            rentals.forEach(rental => {
                const rentalId    = rental.rentalId ?? 'N/A';
                const customerId  = rental.customerId ?? 'N/A';
                const filmTitle   = rental.filmTitle ?? 'N/A';
                const inventoryId = rental.inventoryId ?? 'N/A';
                const rentalDate  = rental.rentalDate ? this.formatDate(rental.rentalDate) : 'N/A';
                const returnDate  = rental.returnDate ? this.formatDate(rental.returnDate) : '<span class="text-muted fst-italic">Non retourné</span>';

                // statusId retourné par l'API, sinon déduit depuis returnDate
                const statusId = rental.statusId ?? rental.status_id ?? null;
                let currentStatusId;
                let statusBadge;
                if (statusId !== null) {
                    currentStatusId = statusId;
                    if (statusId === 1) {
                        statusBadge = '<span class="badge bg-secondary">Terminée</span>';
                    } else if (statusId === 2) {
                        statusBadge = '<span class="badge bg-warning text-dark">Dans le panier</span>';
                    } else {
                        statusBadge = '<span class="badge bg-success">En cours</span>';
                    }
                } else if (!rental.returnDate) {
                    currentStatusId = 3;
                    statusBadge = '<span class="badge bg-success">En cours</span>';
                } else if (new Date(rental.returnDate) <= now) {
                    currentStatusId = 1;
                    statusBadge = '<span class="badge bg-secondary">Terminée</span>';
                } else {
                    currentStatusId = 3;
                    statusBadge = '<span class="badge bg-success">En cours</span>';
                }

                const row = document.createElement('tr');
                row.dataset.rentalId = rentalId;
                row.innerHTML = `
                    <td>${rentalId}</td>
                    <td>${customerId}</td>
                    <td><strong>${this.escapeHtml(filmTitle)}</strong></td>
                    <td>${inventoryId}</td>
                    <td>${rentalDate}</td>
                    <td>${returnDate}</td>
                    <td class="status-badge-cell">${statusBadge}</td>
                    <td>
                        <div class="d-flex gap-2 align-items-center">
                            <select class="form-select form-select-sm status-select" style="width: 140px;" data-rental-id="${rentalId}">
                                <option value="3" ${currentStatusId === 3 ? 'selected' : ''}>En cours</option>
                                <option value="2" ${currentStatusId === 2 ? 'selected' : ''}>Dans le panier</option>
                                <option value="1" ${currentStatusId === 1 ? 'selected' : ''}>Terminée</option>
                            </select>
                            <button class="btn btn-warning btn-sm text-white status-save-btn" data-rental-id="${rentalId}">
                                <i class="bi bi-pencil"></i> Modifier
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Attache les événements sur les boutons de sauvegarde
            tbody.querySelectorAll('.status-save-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = btn.dataset.rentalId;
                    const select = tbody.querySelector(`.status-select[data-rental-id="${id}"]`);
                    this.saveStatus(id, parseInt(select.value), btn);
                });
            });
        },

        async saveStatus(rentalId, statusId, btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const response = await fetch(`/rentals/${rentalId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ statusId })
                });

                const data = await response.json();

                if (data.success) {
                    // Met à jour le badge de la ligne
                    const row = document.querySelector(`tr[data-rental-id="${rentalId}"]`);
                    const cell = row.querySelector('.status-badge-cell');
                    const badges = {
                        1: '<span class="badge bg-secondary">Terminée</span>',
                        2: '<span class="badge bg-warning text-dark">Dans le panier</span>',
                        3: '<span class="badge bg-success">En cours</span>',
                    };
                    cell.innerHTML = badges[statusId] ?? '';
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Modifié';
                    btn.className = 'btn btn-success btn-sm text-white status-save-btn';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-pencil"></i> Modifier';
                        btn.className = 'btn btn-warning btn-sm text-white status-save-btn';
                    }, 1500);
                } else {
                    btn.innerHTML = '<i class="bi bi-pencil"></i> Modifier';
                    alert('Erreur : ' + (data.error ?? 'Impossible de mettre à jour'));
                }
            } catch (err) {
                console.error(err);
                btn.innerHTML = '<i class="bi bi-pencil"></i> Modifier';
                alert('Erreur réseau');
            }

            btn.disabled = false;
        },

        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const d = new Date(dateStr);
            return d.toLocaleDateString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        },

        renderPagination(data) {
            document.getElementById('totalRentals').textContent = data.totalRentals;
            document.getElementById('currentPageInfo').textContent = data.currentPage;
            document.getElementById('totalPagesInfo').textContent = data.totalPages;

            if (data.totalPages <= 1) {
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }
            document.getElementById('paginationContainer').style.display = 'flex';

            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            paginationList.appendChild(this.createPageItem('<<', 1, data.currentPage === 1));
            paginationList.appendChild(this.createPageItem('<', Math.max(1, data.currentPage - 1), data.currentPage === 1));

            const start = Math.max(1, data.currentPage - 2);
            const end   = Math.min(data.totalPages, data.currentPage + 2);

            if (start > 1) {
                paginationList.appendChild(this.createPageItem('1', 1, false));
                if (start > 2) paginationList.appendChild(this.createPageItem('...', null, true));
            }

            for (let i = start; i <= end; i++) {
                paginationList.appendChild(this.createPageItem(i.toString(), i, false, i === data.currentPage));
            }

            if (end < data.totalPages) {
                if (end < data.totalPages - 1) paginationList.appendChild(this.createPageItem('...', null, true));
                paginationList.appendChild(this.createPageItem(data.totalPages.toString(), data.totalPages, false));
            }

            paginationList.appendChild(this.createPageItem('>', Math.min(data.totalPages, data.currentPage + 1), data.currentPage === data.totalPages));
            paginationList.appendChild(this.createPageItem('>>', data.totalPages, data.currentPage === data.totalPages));
        },

        createPageItem(text, page, disabled, active = false) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');

            if (disabled || page === null) {
                li.innerHTML = `<span class="page-link">${text}</span>`;
            } else {
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = text;
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.currentPage = page;
                    this.loadRentals();
                });
                li.appendChild(a);
            }

            return li;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    document.addEventListener('DOMContentLoaded', () => RentalsPagination.init());
</script>
@endsection
