<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service CRUD inventaires via l'API Toad.
 * Gère les DVDs (inventaires), les stores et la disponibilité.
 */
class ToadInventoryService
{
    use HasToadToken;

    public function __construct()
    {
    }

    /**
     * @return array|null Liste complète des inventaires, null si erreur
     */
    public function getAllInventories(): ?array
    {
        $url = $this->getBaseUrl() . '/inventories';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Inventories', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)->timeout(30)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Inventories API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Inventories', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  int        $id
     * @return array|null Détail de l'inventaire, null si introuvable
     */
    public function getInventoryById(int $id): ?array
    {
        $url = $this->getBaseUrl() . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Get Inventory', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)->timeout(30)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Get Inventory API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Get Inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @return array|null Liste des magasins, null si erreur
     */
    public function getAllStores(): ?array
    {
        $url = $this->getBaseUrl() . '/stores';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Stores', ['url' => $url]);

            $response = Http::withHeaders($headers)->timeout(30)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Stores API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Stores', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère tous les films avec mise en cache 5 minutes.
     * Évite des appels répétés à l'API sur les pages qui affichent un select de films.
     *
     * @return array|null
     */
    public function getAllFilms(): ?array
    {
        return Cache::remember('toad_films_list', 300, function () {
            $url = $this->getBaseUrl() . '/films?page=0&size=10000';

            try {
                $headers = ['Accept' => 'application/json'];
                $token   = $this->getUserToken();
                if ($token) {
                    $headers['Authorization'] = "Bearer {$token}";
                }

                Log::info('Appel API Films (mise en cache)', ['url' => $url]);

                $response = Http::withHeaders($headers)->timeout(30)->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    // Format paginé Spring Boot : extraire uniquement le tableau content
                    if (isset($data['content']) && is_array($data['content'])) {
                        Log::info('Films récupérés (paginé)', ['total' => count($data['content'])]);
                        return $data['content'];
                    }

                    Log::info('Films récupérés', ['total' => count($data)]);
                    return $data;
                }

                Log::warning('Films API KO', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            } catch (\Throwable $e) {
                Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * @param  array      $data Champs requis : filmId, storeId
     * @return array|null Inventaire créé, null si échec
     */
    public function createInventory(array $data): ?array
    {
        $url = $this->getBaseUrl() . '/inventories';

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Create Inventory', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)->timeout(30)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Create Inventory API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Create Inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mise à jour complète de l'inventaire (PUT).
     *
     * @param  int        $id
     * @param  array      $data Champs requis : filmId, storeId
     * @return array|null Inventaire mis à jour, null si échec
     */
    public function updateInventory(int $id, array $data): ?array
    {
        $url = $this->getBaseUrl() . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Update Inventory', ['url' => $url, 'id' => $id, 'data' => $data]);

            $response = Http::withHeaders($headers)->timeout(30)->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Update Inventory API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Update Inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Vérifie si un DVD est disponible à la location (non actuellement loué).
     *
     * @param  int       $inventoryId
     * @return bool|null true = disponible, false = loué, null si erreur API
     */
    public function checkIfDVDIsAvailable(int $inventoryId): ?bool
    {
        $url = $this->getBaseUrl() . '/inventories/checkIfDVDIsAvailable/' . $inventoryId;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Check DVD Available', ['url' => $url, 'inventoryId' => $inventoryId]);

            $response = Http::withHeaders($headers)->timeout(30)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Check DVD Available API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Check DVD Available', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  int  $id
     * @return bool true si supprimé, false sinon
     */
    public function deleteInventory(int $id): bool
    {
        $url = $this->getBaseUrl() . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Delete Inventory', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)->timeout(30)->delete($url);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Delete Inventory API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur API Delete Inventory', ['msg' => $e->getMessage()]);
            return false;
        }
    }
}
