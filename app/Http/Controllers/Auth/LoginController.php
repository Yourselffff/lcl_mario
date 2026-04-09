<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ToadAuthService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Auth\ToadUser;

/**
 * Gère la connexion des utilisateurs via l'API Toad.
 * Remplace le flux d'authentification Eloquent par défaut de Laravel.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';
    protected $toadAuth;

    public function __construct(ToadAuthService $toadAuth)
    {
        $this->middleware('guest')->except('logout');
        $this->toadAuth = $toadAuth;
    }

    /**
     * Authentifie l'utilisateur via l'API Toad puis crée la session Laravel.
     *
     * @throws ValidationException Si les identifiants sont refusés par l'API
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $resp = $this->toadAuth->verify(
            $request->input('email'),
            $request->input('password')
        );

        if (!$resp) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        // L'API peut retourner { token, staff: {...} } ou directement les données du staff
        $staff = $resp['staff'] ?? $resp;

        $userData = [
            'id'    => $staff['staffId'] ?? $staff['id'] ?? $staff['email'],
            'email' => $staff['email'] ?? null,
            'name'  => $staff['name']
                       ?? trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''))
                       ?: ($staff['email'] ?? 'Utilisateur'),
            'token' => $resp['token'] ?? $resp['access_token'] ?? null,
            'staff' => $staff,
        ];

        // Stocke les données en session pour le ToadUserProvider
        $request->session()->put('toad_user', $userData);

        // Crée l'objet utilisateur en mémoire et ouvre la session Laravel
        $user = new ToadUser($userData);
        Auth::login($user, false); // remember=false : pas de remember token

        return $this->sendLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);
    }
}
