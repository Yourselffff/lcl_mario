@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du film</h5>
                    <a href="{{ route('films.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3>{{ $film['title'] ?? 'Sans titre' }}</h3>
                            <p class="text-muted">{{ $film['description'] ?? 'Aucune description disponible.' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if(isset($film['rating']))
                                <span class="badge bg-info fs-5">{{ $film['rating'] }}</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Année de sortie</dt>
                        <dd class="col-sm-9">{{ $film['releaseYear'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Langue</dt>
                        <dd class="col-sm-9">ID {{ $film['languageId'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Durée</dt>
                        <dd class="col-sm-9">{{ $film['length'] ?? 'N/A' }} minutes</dd>

                        <dt class="col-sm-3">Coût de remplacement</dt>
                        <dd class="col-sm-9">{{ $film['replacementCost'] ?? 'N/A' }} €</dd>

                        <dt class="col-sm-3">Note</dt>
                        <dd class="col-sm-9">{{ $film['rating'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Caractéristiques spéciales</dt>
                        <dd class="col-sm-9">{{ $film['specialFeatures'] ?? 'Aucune' }}</dd>

                        <dt class="col-sm-3">Acteurs</dt>
                        <dd class="col-sm-9">
                            @if(isset($film['actors']) && count($film['actors']) > 0)
                                @foreach($film['actors'] as $actor)
                                    <span class="badge bg-primary me-1">{{ $actor['firstName'] ?? '' }} {{ $actor['lastName'] ?? '' }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Aucun acteur</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Réalisateurs</dt>
                        <dd class="col-sm-9">
                            @if(isset($film['directors']) && count($film['directors']) > 0)
                                @foreach($film['directors'] as $director)
                                    <span class="badge bg-success me-1">{{ $director['firstName'] ?? '' }} {{ $director['lastName'] ?? '' }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Aucun réalisateur</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Dernière mise à jour</dt>
                        <dd class="col-sm-9">{{ $film['lastUpdate'] ?? 'N/A' }}</dd>
                    </dl>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('films.edit', $film['filmId'] ?? $film['id']) }}" class="btn btn-warning text-white">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <form action="{{ route('films.destroy', $film['filmId'] ?? $film['id']) }}"
                              method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?')">
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