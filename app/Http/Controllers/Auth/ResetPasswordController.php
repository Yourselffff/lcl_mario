<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * Gère la réinitialisation du mot de passe depuis le lien reçu par email.
 * Utilise le trait ResetsPasswords fourni par Laravel.
 */
class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /** Redirection après réinitialisation réussie. */
    protected $redirectTo = '/home';
}
