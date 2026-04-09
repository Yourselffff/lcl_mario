<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service création de staff via l'API Toad.
 * Utilisé lors de l'inscription d'un nouvel utilisateur.
 */
class ToadStaffService
{
    use HasToadToken;

    public function __construct()
    {
    }

    /**
     * Crée un nouveau membre du personnel via l'API Toad.
     *
     * @param  array $data Champs : first_name, last_name, email, username, password, address_id, store_id
     * @return array Données du staff créé, ou ['_error' => true, 'status' => int, 'message' => string] si échec
     */
    public function createStaff(array $data): array
    {
        $url = $this->getBaseUrl() . '/staffs';

        // Conversion du format snake_case (formulaire Laravel) → camelCase (API Java)
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

            // Retourne un tableau d'erreur structuré pour que l'appelant puisse l'afficher
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
