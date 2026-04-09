{{-- =============================================================================
     Liste paginée des films (chargement AJAX).
     La page HTML est rendue vide par le serveur ; le JavaScript (FilmsPagination)
     appelle l'endpoint POST /films/data pour récupérer les données et remplir le tableau.
     ============================================================================= --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestion du catalogue de films</h5>
                    <a href="{{ route('films.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Ajouter un film
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

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">Total : <strong id="totalFilms">-</strong> film(s)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="limit" class="mb-0">Afficher :</label>
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

                    <div id="noFilms" class="alert alert-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucun film disponible ou erreur lors de la récupération des données de l'API.
                    </div>

                    <div id="filmsContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Annee</th>
                                        <th>Duree</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="filmsTableBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="paginationContainer" class="mt-3 d-flex justify-content-between align-items-center">
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle"></i>
                                Page <strong id="currentPageInfo">1</strong> sur <strong id="totalPagesInfo">1</strong>
                            </p>
                            <nav aria-label="Pagination des films">
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
    /**
     * Objet JavaScript gérant la pagination AJAX de la liste des films.
     * Principe : on envoie une requête POST à /films/data avec la page et la limite,
     * le serveur retourne un JSON, et on met à jour le DOM sans rechargement de page.
     */
    const FilmsPagination = {
        currentPage: 1,
        limit: 10,
        totalPages: 1,
        csrfToken: '{{ csrf_token() }}',   // Token CSRF requis par Laravel pour les requêtes POST
        dataUrl: '{{ route("films.data") }}',
        showUrl: '{{ url("films") }}',
        editUrl: '{{ url("films") }}',
        deleteUrl: '{{ url("films") }}',

        init() {
            this.limit = 10;
            document.getElementById('limit').value = this.limit;
            this.loadFilms();
            document.getElementById('limit').addEventListener('change', (e) => {
                this.limit = parseInt(e.target.value);
                this.currentPage = 1;
                this.loadFilms();
            });
        },

        /** Charge une page de films via fetch (requête AJAX POST vers /films/data). */
        async loadFilms() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('filmsContainer').style.display = 'none';
            document.getElementById('noFilms').style.display = 'none';

            try {
                const response = await fetch(this.dataUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        page: this.currentPage,
                        limit: this.limit
                    })
                });

                const data = await response.json();
                document.getElementById('loading').style.display = 'none';

                if (!data.films || data.films.length === 0) {
                    document.getElementById('noFilms').style.display = 'block';
                    return;
                }

                this.totalPages = data.totalPages;
                this.renderFilms(data.films);
                this.renderPagination(data);
                document.getElementById('filmsContainer').style.display = 'block';

            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('noFilms').style.display = 'block';
            }
        },

        /** Génère les lignes du tableau HTML à partir du tableau de films reçu. */
        renderFilms(films) {
            const tbody = document.getElementById('filmsTableBody');
            tbody.innerHTML = '';

            films.forEach(film => {
                const filmId = film.filmId || film.id || 'N/A';
                const title = film.title || 'Sans titre';
                const description = film.description ? (film.description.length > 80 ? film.description.substring(0, 80) + '...' : film.description) : 'Aucune description';
                const releaseYear = film.releaseYear || 'N/A';
                const length = film.length ? film.length + ' min' : 'N/A min';
                const rating = film.rating ? `<span class="badge bg-info">${film.rating}</span>` : '<span class="badge bg-secondary">N/A</span>';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${filmId}</td>
                    <td><strong>${this.escapeHtml(title)}</strong></td>
                    <td>${this.escapeHtml(description)}</td>
                    <td>${releaseYear}</td>
                    <td>${length}</td>
                    <td>${rating}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="${this.showUrl}/${filmId}" class="btn btn-info btn-sm" title="Voir les details">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <a href="${this.editUrl}/${filmId}/edit" class="btn btn-warning btn-sm text-white" title="Modifier le film">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <form action="${this.deleteUrl}/${filmId}" method="POST" style="display: inline;" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce film ?')">
                                <input type="hidden" name="_token" value="${this.csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer le film">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        },

        /** Construit les boutons de pagination (précédent, numéros, suivant). */
        renderPagination(data) {
            document.getElementById('totalFilms').textContent = data.totalFilms;
            document.getElementById('currentPageInfo').textContent = data.currentPage;
            document.getElementById('totalPagesInfo').textContent = data.totalPages;

            if (data.totalPages <= 1) {
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }
            document.getElementById('paginationContainer').style.display = 'flex';

            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            // Premiere page
            paginationList.appendChild(this.createPageItem('<<', 1, data.currentPage === 1));

            // Page precedente
            paginationList.appendChild(this.createPageItem('<', Math.max(1, data.currentPage - 1), data.currentPage === 1));

            // Numeros de pages
            const start = Math.max(1, data.currentPage - 2);
            const end = Math.min(data.totalPages, data.currentPage + 2);

            if (start > 1) {
                paginationList.appendChild(this.createPageItem('1', 1, false));
                if (start > 2) {
                    paginationList.appendChild(this.createPageItem('...', null, true));
                }
            }

            for (let i = start; i <= end; i++) {
                paginationList.appendChild(this.createPageItem(i.toString(), i, false, i === data.currentPage));
            }

            if (end < data.totalPages) {
                if (end < data.totalPages - 1) {
                    paginationList.appendChild(this.createPageItem('...', null, true));
                }
                paginationList.appendChild(this.createPageItem(data.totalPages.toString(), data.totalPages, false));
            }

            // Page suivante
            paginationList.appendChild(this.createPageItem('>', Math.min(data.totalPages, data.currentPage + 1), data.currentPage === data.totalPages));

            // Derniere page
            paginationList.appendChild(this.createPageItem('>>', data.totalPages, data.currentPage === data.totalPages));
        },

        /** Crée un élément <li> de pagination Bootstrap. */
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
                    this.loadFilms();
                });
                li.appendChild(a);
            }

            return li;
        },

        /** Échappe les caractères HTML pour éviter les injections XSS dans le tableau. */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    document.addEventListener('DOMContentLoaded', () => FilmsPagination.init());
</script>
@endsection
