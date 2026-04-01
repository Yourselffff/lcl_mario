<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadStaffService
{
    use HasToadToken;

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    /**
     * Retourne le tableau de la réponse si succès, ou ['_error' => true, 'status' => ..., 'message' => ...] si échec.
     */
    public function createStaff(array $data): array
    {
        $url = $this->baseUrl . '/staffs';

        $payload = [
            'firstName'  => $data['first_name'],
            'lastName'   => $data['last_name'],
            'addressId'  => $data['address_id'] ?? 1,
            'email'      => $data['email'],
            'storeId'    => $data['store_id'] ?? 1,
            'active'     => true,
            'username'   => $data['username'],
            'password'   => $data['password'],
            'lastUpdate' => now()->toIso8601String(),
        ];

        try {
            Log::info('Appel API createStaff', ['url' => $url, 'payload' => $payload]);

            $response = Http::post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erreur createStaff', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            $body = $response->json();
            return [
                '_error'  => true,
                'status'  => $response->status(),
                'message' => $body['message'] ?? $body['error'] ?? $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Exception createStaff', ['message' => $e->getMessage()]);
            return ['_error' => true, 'status' => 0, 'message' => $e->getMessage()];
        }
    }
}
