<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Provider d'authentification personnalisé pour l'API Toad.
 * Les utilisateurs sont stockés en session (pas de BDD locale).
 * Enregistré sous le nom 'toad' dans AuthServiceProvider.
 */
class ToadUserProvider implements UserProvider
{
    /**
     * Recharge l'utilisateur depuis la session à chaque requête.
     * Laravel appelle cette méthode automatiquement via le cookie de session.
     */
    public function retrieveById($identifier)
    {
        $data = session('toad_user');
        $id   = $data['id'] ?? $data['email'] ?? null;

        if ($data && $id == $identifier) {
            return new ToadUser($data);
        }
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null; // remember token non supporté
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // non supporté
    }

    public function retrieveByCredentials(array $credentials)
    {
        return null; // validation faite via ToadAuthService::verify()
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false; // non utilisé
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // non utilisé, pas de stockage local des mots de passe
    }
}
