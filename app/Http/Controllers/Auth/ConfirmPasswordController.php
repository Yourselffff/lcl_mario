<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

/**
 * Gère la confirmation de mot de passe avant des actions sensibles.
 * Utilise le trait ConfirmsPasswords fourni par Laravel.
 */
class ConfirmPasswordController extends Controller
{
    use ConfirmsPasswords;

    /** Redirection après confirmation réussie. */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('auth');
    }
}
