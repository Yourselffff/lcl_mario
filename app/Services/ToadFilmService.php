<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service CRUD films via l'API Toad.
 * Endpoints : GET/POST/PUT/DELETE /films, GET /actors, GET /directors
 */
class ToadFilmService
{
    use HasToadToken;

    public function __construct()
    {
    }

    /**
     * Retourne une page de films.
     * Gère deux formats de réponse API : objet paginé {content:[]} ou tableau simple [].
     *
     * @param  int        $limit
     * @param  int        $offset
     * @return array|null
     */
    public function getAllFilms(int $limit = 10, int $offset = 0): ?array
    {
        $url = $this->getBaseUrl() . '/films';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Films', ['url' => $url, 'limit' => $limit, 'offset' => $offset]);

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get($url, ['limit' => $limit, 'offset' => $offset]);

            if ($response->successful()) {
                $body = $response->json();

                // Format paginé Spring Boot : { content: [...], totalElements: N }
                if (isset($body['content'])) {
                    return $body['content'];
                }

                // Tableau simple : si l'API a ignoré les params, on pagine en PHP
                if (is_array($body)) {
                    return count($body) > $limit
                        ? array_slice($body, $offset, $limit)
                        : $body;
                }

                return null;
            }

            Log::warning('Films API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @return int Nombre total de films, 0 en cas d'erreur
     */
    public function getFilmsCount(): int
    {
        $url = $this->getBaseUrl() . '/films/count';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            return $response->successful() ? (int) $response->body() : 0;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films Count', ['msg' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * @param  int        $id
     * @return array|null Détail complet du film, null si introuvable
     */
    public function getFilmById(int $id): ?array
    {
        $url = $this->getBaseUrl() . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  array      $filmData Champs en camelCase (filmId, title, releaseYear…)
     * @return array|null Film créé avec son filmId, null si échec
     */
    public function createFilm(array $filmData): ?array
    {
        $url = $this->getBaseUrl() . '/films';

        try {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Création film via API', ['url' => $url, 'data' => $filmData]);

            $response = Http::withHeaders($headers)->timeout(10)->post($url, $filmData);

            if ($response->successful()) {
                Log::info('Film créé avec succès', ['status' => $response->status()]);
                return $response->json();
            }

            Log::warning('Création film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mise à jour complète du film (PUT).
     *
     * @param  int        $id
     * @param  array      $filmData Toutes les propriétés du film
     * @return array|null Film mis à jour, null si échec
     */
    public function updateFilm(int $id, array $filmData): ?array
    {
        $url = $this->getBaseUrl() . '/films/' . $id;

        try {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Modification film via API', ['url' => $url, 'id' => $id, 'data' => $filmData]);

            $response = Http::withHeaders($headers)->timeout(10)->put($url, $filmData);

            if ($response->successful()) {
                Log::info('Film modifié avec succès', ['status' => $response->status()]);
                return $response->json();
            }

            Log::warning('Modification film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur modification film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  int  $id
     * @return bool true si supprimé, false sinon
     */
    public function deleteFilm(int $id): bool
    {
        $url = $this->getBaseUrl() . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Suppression film via API', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)->timeout(10)->delete($url);

            if ($response->successful()) {
                Log::info('Film supprimé avec succès', ['status' => $response->status()]);
                return true;
            }

            Log::warning('Suppression film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression film', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Récupère tous les acteurs (utilisé pour le formulaire film).
     *
     * @return array|null
     */
    public function getAllActors(): ?array
    {
        $url = $this->getBaseUrl() . '/actors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Actors API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Actors', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère tous les réalisateurs (utilisé pour le formulaire film).
     *
     * @return array|null
     */
    public function getAllDirectors(): ?array
    {
        $url = $this->getBaseUrl() . '/directors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Directors API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Directors', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
