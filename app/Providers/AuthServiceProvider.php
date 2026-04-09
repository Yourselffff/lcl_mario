<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\ToadUserProvider;

/**
 * Enregistre le provider d'authentification personnalisé 'toad'.
 * Configuré comme driver dans config/auth.php (guards.web.provider = 'toad').
 */
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Déclare le driver 'toad' auprès de Laravel
        Auth::provider('toad', function ($app, array $config) {
            return new ToadUserProvider();
        });
    }
}
