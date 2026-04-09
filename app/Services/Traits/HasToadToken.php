<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait partagé par tous les services Toad.
 * Centralise la résolution de l'URL de base et du token d'authentification.
 */
trait HasToadToken
{
    /**
     * Retourne l'URL de base de l'API selon la source active en session (local/remote).
     */
    protected function getBaseUrl(): string
    {
        $source = session('toad_source', 'local');
        $configKey = $source === 'remote' ? 'services.toad.url_remote' : 'services.toad.url';
        return rtrim((string) config($configKey, 'http://localhost:8180'), '/');
    }

    /**
     * Retourne le token JWT à inclure dans Authorization: Bearer.
     * Priorité : token statique (.env) > token de session utilisateur.
     */
    protected function getUserToken(): ?string
    {
        $staticToken = config('services.toad.token');
        if (!empty($staticToken)) {
            return $staticToken;
        }

        $userData = session('toad_user');
        return $userData['token'] ?? null;
    }

    /**
     * Génère un JWT HS256 en PHP natif (sans librairie externe).
     * Utilisé si aucun token statique n'est configuré et que l'API requiert un JWT signé.
     */
    protected function generateJwtToken(): ?string
    {
        $secret = config('services.toad.jwt_secret');

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

    /**
     * Encode en Base64 URL-safe (remplace +/ par -_ et supprime les =).
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
