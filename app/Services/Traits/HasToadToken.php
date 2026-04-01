<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasToadToken
{
    /**
     * Retourne le token JWT à envoyer dans Authorization: Bearer.
     * Priorité : TOAD_API_TOKEN statique → token session utilisateur.
     */
    protected function getUserToken(): ?string
    {
        // 1. Token statique depuis .env (TOAD_API_TOKEN)
        $staticToken = config('services.toad.token');
        if (!empty($staticToken)) {
            return $staticToken;
        }

        // 2. Token de session utilisateur (connexion via l'interface)
        $userData = session('toad_user');
        return $userData['token'] ?? null;
    }

    /**
     * Génère un JWT HS256 en pur PHP (sans librairie externe).
     */
    protected function generateJwtToken(): ?string
    {
        $secret = config('services.toad.jwt_secret');

        // Fallback vers le token statique si pas de secret configuré
        if (empty($secret)) {
            Log::warning('ToadToken: jwt_secret absent, utilisation du token statique');
            return config('services.toad.token');
        }

        $ttl = $this->getJwtTtl();
        $now = time();

        $header  = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => config('services.toad.jwt_iss', 'mario-app'),
            'aud' => config('services.toad.jwt_aud', 'toad-api'),
            'iat' => $now,
            'exp' => $now + $ttl,
        ]));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
        );

        $token = "{$header}.{$payload}.{$signature}";
        Log::info('ToadToken: nouveau JWT généré', ['exp' => date('Y-m-d H:i:s', $now + $ttl)]);

        return $token;
    }

    private function getJwtTtl(): int
    {
        return (int) config('services.toad.jwt_ttl', 3600);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
