@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Modifier le film : {{ $film['title'] }}</h4>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="filmForm" method="POST" action="{{ route('films.update', $film['filmId']) }}">
                        @csrf
                        @method('PUT')

                        <!-- Section: Informations principales -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-warning"><i class="bi bi-info-circle"></i> Informations principales</h5>
                        </div>

                        <div class="row">
                            <!-- Titre -->
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $film['title'] ?? '') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Année de sortie -->
                            <div class="col-md-3 mb-3">
                                <label for="release_year" class="form-label">Année de sortie <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('release_year') is-invalid @enderror"
                                       id="release_year" name="release_year" value="{{ old('release_year', $film['releaseYear'] ?? '') }}"
                                       min="1888" max="{{ date('Y') + 1 }}" required>
                                @error('release_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Langue -->
                            <div class="col-md-3 mb-3">
                                <label for="language_id" class="form-label">Langue <span class="text-danger">*</span></label>
                                <select class="form-select @error('language_id') is-invalid @enderror"
                                        id="language_id" name="language_id" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="1" {{ old('language_id', $film['languageId'] ?? null) == 1 ? 'selected' : '' }}>Français</option>
                                    <option value="2" {{ old('language_id', $film['languageId'] ?? null) == 2 ? 'selected' : '' }}>Anglais</option>
                                    <option value="3" {{ old('language_id', $film['languageId'] ?? null) == 3 ? 'selected' : '' }}>Espagnol</option>
                                    <option value="4" {{ old('language_id', $film['languageId'] ?? null) == 4 ? 'selected' : '' }}>Allemand</option>
                                    <option value="5" {{ old('language_id', $film['languageId'] ?? null) == 5 ? 'selected' : '' }}>Italien</option>
                                </select>
                                @error('language_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description', $film['description'] ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Section: Détails techniques -->
                        <div class="mb-4 mt-4">
                            <h5 class="border-bottom pb-2 text-warning"><i class="bi bi-gear"></i> Détails techniques</h5>
                        </div>

                        <div class="row">
                            <!-- Durée -->
                            <div class="col-md-3 mb-3">
                                <label for="length" class="form-label">Durée (minutes)</label>
                                <input type="number" class="form-control @error('length') is-invalid @enderror"
                                       id="length" name="length" value="{{ old('length', $film['length'] ?? '') }}" min="1" max="999">
                                @error('length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Coût de remplacement -->
                            <div class="col-md-3 mb-3">
                                <label for="replacement_cost" class="form-label">Coût de remplacement (€)</label>
                                <input type="number" step="0.01" class="form-control @error('replacement_cost') is-invalid @enderror"
                                       id="replacement_cost" name="replacement_cost" value="{{ old('replacement_cost', $film['replacementCost'] ?? '') }}"
                                       min="0" max="99.99">
                                @error('replacement_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="col-md-3 mb-3">
                                <label for="rating" class="form-label">Classification</label>
                                <select class="form-select @error('rating') is-invalid @enderror"
                                        id="rating" name="rating">
                                    <option value="">Sélectionner...</option>
                                    <option value="G" {{ old('rating', $film['rating'] ?? '') == 'G' ? 'selected' : '' }}>G - Tous publics</option>
                                    <option value="PG" {{ old('rating', $film['rating'] ?? '') == 'PG' ? 'selected' : '' }}>PG - Accord parental souhaité</option>
                                    <option value="PG-13" {{ old('rating', $film['rating'] ?? '') == 'PG-13' ? 'selected' : '' }}>PG-13 - Déconseillé -13 ans</option>
                                    <option value="R" {{ old('rating', $film['rating'] ?? '') == 'R' ? 'selected' : '' }}>R - Interdit -17 ans</option>
                                    <option value="NC-17" {{ old('rating', $film['rating'] ?? '') == 'NC-17' ? 'selected' : '' }}>NC-17 - Interdit -18 ans</option>
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Langue originale -->
                            <div class="col-md-3 mb-3">
                                <label for="original_language_id" class="form-label">Langue originale</label>
                                <select class="form-select @error('original_language_id') is-invalid @enderror"
                                        id="original_language_id" name="original_language_id">
                                    <option value="">Sélectionner...</option>
                                    <option value="1" {{ old('original_language_id', $film['originalLanguageId'] ?? null) == 1 ? 'selected' : '' }}>Français</option>
                                    <option value="2" {{ old('original_language_id', $film['originalLanguageId'] ?? null) == 2 ? 'selected' : '' }}>Anglais</option>
                                    <option value="3" {{ old('original_language_id', $film['originalLanguageId'] ?? null) == 3 ? 'selected' : '' }}>Espagnol</option>
                                    <option value="4" {{ old('original_language_id', $film['originalLanguageId'] ?? null) == 4 ? 'selected' : '' }}>Allemand</option>
                                    <option value="5" {{ old('original_language_id', $film['originalLanguageId'] ?? null) == 5 ? 'selected' : '' }}>Italien</option>
                                </select>
                                @error('original_language_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section: Caractéristiques spéciales -->
                        <div class="mb-4 mt-4">
                            <h5 class="border-bottom pb-2 text-warning"><i class="bi bi-star"></i> Caractéristiques spéciales</h5>
                        </div>

                        @php
                            $specialFeatures = isset($film['specialFeatures']) ? explode(',', $film['specialFeatures']) : [];
                            if (old('special_features')) {
                                $specialFeatures = old('special_features');
                            }
                        @endphp
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="special_features[]"
                                               value="Trailers" id="trailers"
                                               {{ in_array('Trailers', $specialFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="trailers">
                                            Bandes-annonces
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="special_features[]"
                                               value="Commentaries" id="commentaries"
                                               {{ in_array('Commentaries', $specialFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="commentaries">
                                            Commentaires
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="special_features[]"
                                               value="Deleted Scenes" id="deleted_scenes"
                                               {{ in_array('Deleted Scenes', $specialFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="deleted_scenes">
                                            Scènes supprimées
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="special_features[]"
                                               value="Behind the Scenes" id="behind_scenes"
                                               {{ in_array('Behind the Scenes', $specialFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="behind_scenes">
                                            Making-of
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Distribution (optionnel) -->
                        <div class="mb-4 mt-4">
                            <h5 class="border-bottom pb-2 text-warning"><i class="bi bi-people"></i> Distribution <small class="text-muted">(optionnel)</small></h5>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> Note : La sélection des acteurs et réalisateurs est optionnelle. Ces informations ne seront pas encore enregistrées dans l'API.
                        </div>

                        @php
                            $filmActorIds = [];
                            $filmDirectorIds = [];
                            if (isset($film['actors']) && is_array($film['actors'])) {
                                $filmActorIds = array_column($film['actors'], 'actorId');
                            }
                            if (isset($film['directors']) && is_array($film['directors'])) {
                                $filmDirectorIds = array_column($film['directors'], 'directorId');
                            }
                        @endphp

                        <div class="row">
                            <!-- Acteurs -->
                            <div class="col-md-6 mb-3">
                                <label for="actors" class="form-label">Acteurs <small class="text-muted">(optionnel)</small></label>
                                <select class="form-select" id="actors" name="actors[]" multiple size="8">
                                    @if(!empty($actors) && is_array($actors))
                                        @foreach($actors as $actor)
                                            <option value="{{ $actor['actorId'] ?? $actor['id'] ?? '' }}"
                                                {{ in_array($actor['actorId'] ?? $actor['id'], $filmActorIds) ? 'selected' : '' }}>
                                                {{ $actor['firstName'] ?? '' }} {{ $actor['lastName'] ?? '' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option disabled>Aucun acteur disponible</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs acteurs</small>
                            </div>

                            <!-- Réalisateurs -->
                            <div class="col-md-6 mb-3">
                                <label for="directors" class="form-label">Réalisateurs <small class="text-muted">(optionnel)</small></label>
                                <select class="form-select" id="directors" name="directors[]" multiple size="8">
                                    @if(!empty($directors) && is_array($directors))
                                        @foreach($directors as $director)
                                            <option value="{{ $director['directorId'] ?? $director['id'] ?? '' }}"
                                                {{ in_array($director['directorId'] ?? $director['id'], $filmDirectorIds) ? 'selected' : '' }}>
                                                {{ $director['firstName'] ?? '' }} {{ $director['lastName'] ?? '' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option disabled>Aucun réalisateur disponible</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs réalisateurs</small>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('films.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg text-white">
                                    <i class="bi bi-save"></i> Mettre à jour le film
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
