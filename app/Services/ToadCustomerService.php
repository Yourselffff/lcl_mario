<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service CRUD clients via l'API Toad.
 * Endpoints : GET/POST/PUT/DELETE /customers
 */
class ToadCustomerService
{
    use HasToadToken;

    public function __construct()
    {
    }

    /**
     * Retourne une page de clients.
     * L'API ne supporte pas la pagination native → on sliceen PHP côté serveur.
     *
     * @param  int        $limit  Nombre de résultats par page
     * @param  int        $offset Index de départ
     * @return array|null
     */
    public function getAllCustomers(int $limit = 10, int $offset = 0): ?array
    {
        $url = $this->getBaseUrl() . '/customers';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if ($response->successful()) {
                return array_slice($response->json(), $offset, $limit);
            }

            Log::warning('Customers API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customers', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Retourne le nombre total de clients (utilisé pour le calcul de la pagination).
     *
     * @return int 0 en cas d'erreur
     */
    public function getCustomersCount(): int
    {
        $url = $this->getBaseUrl() . '/customers';

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if ($response->successful()) {
                return count($response->json());
            }

            return 0;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customers Count', ['msg' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * @param  int        $id
     * @return array|null Données du client, null si introuvable
     */
    public function getCustomerById(int $id): ?array
    {
        $url = $this->getBaseUrl() . '/customers/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  array      $data Champs du client (storeId, firstName, lastName, email…)
     * @return array|null Client créé avec son ID, null si échec
     */
    public function createCustomer(array $data): ?array
    {
        $url = $this->getBaseUrl() . '/customers';

        try {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Création customer KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mise à jour complète du client (PUT remplace toutes les propriétés).
     *
     * @param  int        $id
     * @param  array      $data
     * @return array|null Client mis à jour, null si échec
     */
    public function updateCustomer(int $id, array $data): ?array
    {
        $url = $this->getBaseUrl() . '/customers/' . $id;

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

            Log::warning('Modification customer KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur modification customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  int  $id
     * @return bool true si supprimé (204 inclus), false sinon
     */
    public function deleteCustomer(int $id): bool
    {
        $url = $this->getBaseUrl() . '/customers/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token   = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->delete($url);

            // 204 No Content est une réponse valide pour une suppression réussie
            return $response->successful() || $response->status() === 204;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression customer', ['msg' => $e->getMessage()]);
            return false;
        }
    }
}
