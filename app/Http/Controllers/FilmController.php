<?php

namespace App\Http\Controllers;

use App\Services\ToadFilmService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    private ToadFilmService $filmService;

    public function __construct(ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->filmService = $filmService;
    }

    public function index()
    {
        $films = $this->filmService->getAllFilms();

        return view('films.index', [
            'films' => $films ?? []
        ]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        return view('films.show', [
            'film' => $film
        ]);
    }

    public function create()
    {
        $actors = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.create', [
            'actors' => $actors,
            'directors' => $directors
        ]);
    }

    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1888|max:' . (date('Y') + 1),
            'language_id' => 'required|integer',
            'description' => 'nullable|string',
            'length' => 'nullable|integer|min:1',
            'replacement_cost' => 'nullable|numeric|min:0',
            'rating' => 'nullable|string',
            'original_language_id' => 'nullable|integer',
            'special_features' => 'nullable|array',
            'actors' => 'nullable|array',
            'directors' => 'nullable|array',
        ]);

        // Préparer les données au format attendu par l'API (camelCase)
        $filmData = [
            'filmId' => null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'releaseYear' => (int)$validated['release_year'],
            'languageId' => (int)$validated['language_id'],
            'originalLanguageId' => isset($validated['original_language_id']) ? (int)$validated['original_language_id'] : null,
            'rentalDuration' => 3, // Valeur par défaut
            'rentalRate' => 4.99, // Valeur par défaut
            'length' => isset($validated['length']) ? (int)$validated['length'] : null,
            'replacementCost' => isset($validated['replacement_cost']) ? (float)$validated['replacement_cost'] : 19.99,
            'rating' => $validated['rating'] ?? null,
            'specialFeatures' => !empty($validated['special_features']) ? implode(',', $validated['special_features']) : null,
            // 'actors' => $validated['actors'] ?? [],
            // 'directors' => $validated['directors'] ?? [],
            'lastUpdate' => now()->format('Y-m-d\TH:i:s'),
        ];

        // Appel à l'API
        $result = $this->filmService->createFilm($filmData);

        if ($result) {
            return redirect()->route('films.index')
                ->with('success', 'Le film a été créé avec succès !');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Une erreur est survenue lors de la création du film.');
    }

    public function edit($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        $actors = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.edit', [
            'film' => $film,
            'actors' => $actors,
            'directors' => $directors
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validation des données
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1888|max:' . (date('Y') + 1),
            'language_id' => 'required|integer',
            'description' => 'nullable|string',
            'length' => 'nullable|integer|min:1',
            'replacement_cost' => 'nullable|numeric|min:0',
            'rating' => 'nullable|string',
            'original_language_id' => 'nullable|integer',
            'special_features' => 'nullable|array',
            'actors' => 'nullable|array',
            'directors' => 'nullable|array',
        ]);

        // Récupérer le film existant
        $existingFilm = $this->filmService->getFilmById($id);
        if (!$existingFilm) {
            abort(404, 'Film non trouvé');
        }

        // Préparer les données au format attendu par l'API (camelCase)
        $filmData = [
            'filmId' => (int)$id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'releaseYear' => (int)$validated['release_year'],
            'languageId' => (int)$validated['language_id'],
            'originalLanguageId' => isset($validated['original_language_id']) ? (int)$validated['original_language_id'] : null,
            'rentalDuration' => $existingFilm['rentalDuration'] ?? 3,
            'rentalRate' => $existingFilm['rentalRate'] ?? 4.99,
            'length' => isset($validated['length']) ? (int)$validated['length'] : null,
            'replacementCost' => isset($validated['replacement_cost']) ? (float)$validated['replacement_cost'] : 19.99,
            'rating' => $validated['rating'] ?? null,
            'specialFeatures' => !empty($validated['special_features']) ? implode(',', $validated['special_features']) : null,
            // 'actors' => $validated['actors'] ?? [],
            // 'directors' => $validated['directors'] ?? [],
            'lastUpdate' => now()->format('Y-m-d\TH:i:s'),
        ];

        // Appel à l'API
        $result = $this->filmService->updateFilm($id, $filmData);

        if ($result) {
            return redirect()->route('films.index')
                ->with('success', 'Le film a été modifié avec succès !');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Une erreur est survenue lors de la modification du film.');
    }

    public function destroy($id)
    {
        $result = $this->filmService->deleteFilm($id);

        if ($result) {
            return redirect()->route('films.index')
                ->with('success', 'Le film a été supprimé avec succès !');
        }

        return redirect()->back()
            ->with('error', 'Une erreur est survenue lors de la suppression du film.');
    }
}