<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

/**
 * Gère l'envoi du lien de réinitialisation de mot de passe par email.
 * Utilise le trait SendsPasswordResetEmails fourni par Laravel.
 */
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
}
