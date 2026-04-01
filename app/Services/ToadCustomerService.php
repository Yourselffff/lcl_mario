<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadCustomerService
{
    use HasToadToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllCustomers(int $limit = 10, int $offset = 0): ?array
    {
        $url = $this->baseUrl . '/customers';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if ($response->successful()) {
                $customers = $response->json();
                return array_slice($customers, $offset, $limit);
            }

            Log::warning('Customers API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customers', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getCustomersCount(): int
    {
        $url = $this->baseUrl . '/customers';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
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

    public function getCustomerById(int $id): ?array
    {
        $url = $this->baseUrl . '/customers/' . $id;

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

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createCustomer(array $data): ?array
    {
        $url = $this->baseUrl . '/customers';

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

    public function updateCustomer(int $id, array $data): ?array
    {
        $url = $this->baseUrl . '/customers/' . $id;

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

    public function deleteCustomer(int $id): bool
    {
        $url = $this->baseUrl . '/customers/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->delete($url);

            return $response->successful() || $response->status() === 204;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression customer', ['msg' => $e->getMessage()]);
            return false;
        }
    }

}
