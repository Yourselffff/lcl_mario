<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Point d'entrée principal pour enregistrer et démarrer les services de l'application.
 * register() : liaison des interfaces / singletons dans le conteneur IoC.
 * boot()     : code exécuté après que tous les services sont enregistrés.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Aucun service personnalisé à enregistrer pour ce projet.
    }

    public function boot(): void
    {
        // Aucun démarrage spécifique requis.
    }
}
