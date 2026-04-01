<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadRentalService
{
    use HasToadToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllRentals(int $limit = 10, int $offset = 0): ?array
    {
        $url = $this->baseUrl . '/rentals/all';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
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
     * Récupère toutes les locations en une seule fois (pour filtrage PHP).
     * Utilisé quand un filtre de statut est actif.
     */
    public function fetchAllRentals(): ?array
    {
        $url = $this->baseUrl . '/rentals/all';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            // Premier appel pour connaître le total
            $first = Http::withHeaders($headers)->timeout(15)->get($url, ['limit' => 1, 'offset' => 0]);
            if (!$first->successful()) {
                return null;
            }
            $total = (int) ($first->json()['totalElements'] ?? 0);
            if ($total === 0) {
                return [];
            }

            // Second appel avec le total complet
            $response = Http::withHeaders($headers)->timeout(30)->get($url, ['limit' => $total, 'offset' => 0]);
            if ($response->successful()) {
                return $response->json()['content'] ?? [];
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API fetchAllRentals', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getRentalById(int $id): ?array
    {
        $url = $this->baseUrl . '/rentals/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
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

    public function updateRental(int $id, array $data): ?array
    {
        $url = $this->baseUrl . '/rentals/' . $id;

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
