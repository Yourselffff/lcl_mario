<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service locations (rentals) via l'API Toad.
 * Endpoints : GET /rentals/all, GET/PUT /rentals/{id}
 */
class ToadRentalService
{
    use HasToadToken;

    public function __construct()
    {
    }

    /**
     * Retourne une page de locations (avec pagination API).
     *
     * @param  int        $limit
     * @param  int        $offset
     * @return array|null Réponse paginée {content, totalElements, totalPages}, null si erreur
     */
    public function getAllRentals(int $limit = 10, int $offset = 0): ?array
    {
        $url = $this->getBaseUrl() . '/rentals/all';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Locations', ['url' => $url, 'limit' => $limit, 'offset' => $offset]);

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get($url, ['limit' => $limit, 'offset' => $offset]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Locations API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Locations', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère toutes les locations en deux appels (d'abord le total, puis tout).
     * Utilisé uniquement quand un filtre par statut est actif (filtrage côté PHP).
     *
     * @return array|null Tableau plat de toutes les locations, null si erreur
     */
    public function fetchAllRentals(): ?array
    {
        $url = $this->getBaseUrl() . '/rentals/all';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            // 1er appel : récupère le total via limit=1 pour éviter de tout charger inutilement
            $first = Http::withHeaders($headers)->timeout(15)->get($url, ['limit' => 1, 'offset' => 0]);
            if (!$first->successful()) {
                return null;
            }

            $total = (int) ($first->json()['totalElements'] ?? 0);
            if ($total === 0) {
                return [];
            }

            // 2e appel : récupère toutes les locations pour le filtrage PHP
            $response = Http::withHeaders($headers)->timeout(30)->get($url, ['limit' => $total, 'offset' => 0]);

            return $response->successful() ? ($response->json()['content'] ?? []) : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API fetchAllRentals', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  int        $id
     * @return array|null Détail de la location, null si introuvable
     */
    public function getRentalById(int $id): ?array
    {
        $url = $this->getBaseUrl() . '/rentals/' . $id;

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

            Log::warning('Rental by ID API KO', ['id' => $id, 'status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Rental by ID', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mise à jour complète d'une location (PUT).
     * L'API requiert tous les champs même pour une simple mise à jour de statut.
     *
     * @param  int        $id
     * @param  array      $data Champs : rentalId, rentalDate, returnDate, inventoryId, customerId, staffId, statusId
     * @return array|null Location mise à jour, null si échec
     */
    public function updateRental(int $id, array $data): ?array
    {
        $url = $this->getBaseUrl() . '/rentals/' . $id;

        try {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Update rental KO', ['id' => $id, 'status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur update rental', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
