<?php

namespace App\Http\Controllers;

use App\Services\ToadFilmService;
use Illuminate\Http\Request;

/**
 * Contrôleur CRUD films.
 * Délègue toutes les opérations à ToadFilmService (appels API Toad).
 */
class FilmController extends Controller
{
    private ToadFilmService $filmService;

    public function __construct(ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->filmService = $filmService;
    }

    /** Affiche la liste paginée des films (chargement AJAX via getData). */
    public function index()
    {
        return view('films.index', [
            'allowedLimits' => [10, 20, 50],
        ]);
    }

    /**
     * Endpoint AJAX appelé par la vue pour récupérer une page de films.
     *
     * @return \Illuminate\Http\JsonResponse {films, currentPage, limit, totalFilms, totalPages}
     */
    public function getData(Request $request)
    {
        $allowedLimits = [10, 20, 50];
        $limit         = (int) $request->input('limit', 10);

        if (!in_array($limit, $allowedLimits)) {
            $limit = 10;
        }

        $page   = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $limit;

        $totalFilms = $this->filmService->getFilmsCount();
        $totalPages = (int) ceil($totalFilms / $limit);
        $films      = $this->filmService->getAllFilms($limit, $offset);

        return response()->json([
            'films'       => $films ?? [],
            'currentPage' => $page,
            'limit'       => $limit,
            'totalFilms'  => $totalFilms,
            'totalPages'  => $totalPages,
        ]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        return view('films.show', ['film' => $film]);
    }

    public function create()
    {
        $actors    = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.create', compact('actors', 'directors'));
    }

    /**
     * Valide et crée un film via l'API.
     * Les données sont converties du format snake_case (HTML) → camelCase (API Java).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'release_year'        => 'required|integer|min:1888|max:' . (date('Y') + 1),
            'language_id'         => 'required|integer',
            'description'         => 'nullable|string',
            'length'              => 'nullable|integer|min:1',
            'replacement_cost'    => 'nullable|numeric|min:0',
            'rating'              => 'nullable|string',
            'original_language_id'=> 'nullable|integer',
            'special_features'    => 'nullable|array',
            'actors'              => 'nullable|array',
            'directors'           => 'nullable|array',
        ]);

        $filmData = [
            'filmId'             => null,
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'releaseYear'        => (int) $validated['release_year'],
            'languageId'         => (int) $validated['language_id'],
            'originalLanguageId' => isset($validated['original_language_id']) ? (int) $validated['original_language_id'] : null,
            'rentalDuration'     => 3,    // valeur par défaut
            'rentalRate'         => 4.99, // valeur par défaut
            'length'             => isset($validated['length']) ? (int) $validated['length'] : null,
            'replacementCost'    => isset($validated['replacement_cost']) ? (float) $validated['replacement_cost'] : 19.99,
            'rating'             => $validated['rating'] ?? null,
            'specialFeatures'    => !empty($validated['special_features']) ? implode(',', $validated['special_features']) : null,
            // 'actors' => $validated['actors'] ?? [],      // désactivé : non supporté par l'API
            // 'directors' => $validated['directors'] ?? [], // désactivé : non supporté par l'API
            'lastUpdate'         => now()->format('Y-m-d\TH:i:s'),
        ];

        $result = $this->filmService->createFilm($filmData);

        if ($result) {
            return redirect()->route('films.index')->with('success', 'Le film a été créé avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Une erreur est survenue lors de la création du film.');
    }

    public function edit($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        $actors    = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.edit', compact('film', 'actors', 'directors'));
    }

    /**
     * Valide et met à jour un film via l'API.
     * Récupère les données existantes (rentalDuration, rentalRate) pour ne pas les écraser.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'release_year'        => 'required|integer|min:1888|max:' . (date('Y') + 1),
            'language_id'         => 'required|integer',
            'description'         => 'nullable|string',
            'length'              => 'nullable|integer|min:1',
            'replacement_cost'    => 'nullable|numeric|min:0',
            'rating'              => 'nullable|string',
            'original_language_id'=> 'nullable|integer',
            'special_features'    => 'nullable|array',
            'actors'              => 'nullable|array',
            'directors'           => 'nullable|array',
        ]);

        $existingFilm = $this->filmService->getFilmById($id);
        if (!$existingFilm) {
            abort(404, 'Film non trouvé');
        }

        $filmData = [
            'filmId'             => (int) $id,
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'releaseYear'        => (int) $validated['release_year'],
            'languageId'         => (int) $validated['language_id'],
            'originalLanguageId' => isset($validated['original_language_id']) ? (int) $validated['original_language_id'] : null,
            'rentalDuration'     => $existingFilm['rentalDuration'] ?? 3,
            'rentalRate'         => $existingFilm['rentalRate'] ?? 4.99,
            'length'             => isset($validated['length']) ? (int) $validated['length'] : null,
            'replacementCost'    => isset($validated['replacement_cost']) ? (float) $validated['replacement_cost'] : 19.99,
            'rating'             => $validated['rating'] ?? null,
            'specialFeatures'    => !empty($validated['special_features']) ? implode(',', $validated['special_features']) : null,
            // 'actors' => $validated['actors'] ?? [],
            // 'directors' => $validated['directors'] ?? [],
            'lastUpdate'         => now()->format('Y-m-d\TH:i:s'),
        ];

        $result = $this->filmService->updateFilm($id, $filmData);

        if ($result) {
            return redirect()->route('films.index')->with('success', 'Le film a été modifié avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Une erreur est survenue lors de la modification du film.');
    }

    public function destroy($id)
    {
        $result = $this->filmService->deleteFilm($id);

        if ($result) {
            return redirect()->route('films.index')->with('success', 'Le film a été supprimé avec succès !');
        }

        return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression du film.');
    }
}
