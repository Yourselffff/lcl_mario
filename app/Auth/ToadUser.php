<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;

/**
 * Représente un utilisateur authentifié via l'API Toad.
 * Étend GenericUser pour désactiver le "remember token" (non supporté sans BDD locale).
 */
class ToadUser extends GenericUser
{
    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // non supporté
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
