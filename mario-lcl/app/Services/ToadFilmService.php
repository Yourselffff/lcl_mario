<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadFilmService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllFilms(): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            $headers = ['Accept' => 'application/json'];
            
            // Récupère le token JWT depuis la session
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Films', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Films API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getFilmById(int $id): ?array
    {
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createFilm(array $filmData): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Création film via API', ['url' => $url, 'data' => $filmData]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $filmData);

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

    public function updateFilm(int $id, array $filmData): ?array
    {
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Modification film via API', ['url' => $url, 'id' => $id, 'data' => $filmData]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->put($url, $filmData);

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

    public function deleteFilm(int $id): bool
    {
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Suppression film via API', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->delete($url);

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
     * Récupère tous les acteurs
     */
    public function getAllActors(): ?array
    {
        $url = $this->baseUrl . '/actors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

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
     * Récupère tous les réalisateurs
     */
    public function getAllDirectors(): ?array
    {
        $url = $this->baseUrl . '/directors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

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

    /**
     * Récupère le token JWT depuis la session utilisateur
     */
    private function getUserToken(): ?string
    {
        $userData = session('toad_user');
        Log::info('Récupération token utilisateur', ['userData' => $userData]);

        return $userData['token'] ?? null;
    }
}