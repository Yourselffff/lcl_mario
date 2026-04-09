<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

/**
 * Gère la vérification de l'adresse email après inscription.
 * Utilise le trait VerifiesEmails fourni par Laravel.
 */
class VerificationController extends Controller
{
    use VerifiesEmails;

    /** Redirection après vérification réussie. */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('auth');
        // 'signed' : vérifie que l'URL de vérification n'a pas été falsifiée
        $this->middleware('signed')->only('verify');
        // 'throttle' : limite à 6 tentatives par minute pour éviter les abus
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
