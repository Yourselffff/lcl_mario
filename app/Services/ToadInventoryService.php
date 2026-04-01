<?php


namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadInventoryService
{
    use HasToadToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllInventories(): ?array
    {
        $url = $this->baseUrl . '/inventories';

        try {
            $headers = ['Accept' => 'application/json'];

            // Récupère le token JWT depuis la session
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Inventories', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($url);

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
     * Récupère un inventaire par son ID
     */
    public function getInventoryById(int $id): ?array
    {
        $url = $this->baseUrl . '/inventories/' . $id;
        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Get Inventory', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($url);

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
     * Récupère tous les stores
     */
    public function getAllStores(): ?array
    {
        $url = $this->baseUrl . '/stores';
        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Stores', ['url' => $url]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($url);

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
     * Récupère tous les films (avec cache)
     */
    public function getAllFilms(): ?array
    {
        // Utiliser le cache pour éviter des appels API répétés (5 minutes)
        return Cache::remember('toad_films_list', 300, function () {
            // Demander une grande pagination pour récupérer tous les films
            $url = $this->baseUrl . '/films?page=0&size=10000';
            try {
                $headers = ['Accept' => 'application/json'];
                $token = $this->getUserToken();
                if ($token) {
                    $headers['Authorization'] = "Bearer {$token}";
                }

                Log::info('Appel API Films (mise en cache)', ['url' => $url]);

                $response = Http::withHeaders($headers)
                    ->timeout(30)
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    // Si l'API retourne un format paginé (avec content, totalElements, etc.)
                    if (isset($data['content']) && is_array($data['content'])) {
                        Log::info('Films récupérés (paginé)', ['total' => count($data['content'])]);
                        return $data['content'];
                    }

                    // Sinon retourner les données telles quelles
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
     * Crée un nouvel inventaire
     */
    public function createInventory(array $data): ?array
    {
        $url = $this->baseUrl . '/inventories';
        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Create Inventory', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, $data);

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
     * Met à jour un inventaire
     */
    public function updateInventory(int $id, array $data): ?array
    {
        $url = $this->baseUrl . '/inventories/' . $id;
        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Update Inventory', ['url' => $url, 'id' => $id, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->put($url, $data);

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
     * Vérifie si un DVD est disponible (non loué)
     */
    public function checkIfDVDIsAvailable(int $inventoryId): ?bool
    {
        $url = $this->baseUrl . '/inventories/checkIfDVDIsAvailable/' . $inventoryId;
        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Check DVD Available', ['url' => $url, 'inventoryId' => $inventoryId]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                // L'API retourne true si disponible, false si loué
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
     * Supprime un inventaire
     */
    public function deleteInventory(int $id): bool
    {
        $url = $this->baseUrl . '/inventories/' . $id;
        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Delete Inventory', ['url' => $url, 'id' => $id]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->delete($url);

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
