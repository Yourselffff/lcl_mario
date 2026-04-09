<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;

/*
 * Point d'entrée de l'application Laravel.
 * Configure les routes, les middlewares, la gestion des exceptions
 * et enregistre les service providers personnalisés.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',       // Routes HTTP (navigateur)
        commands: __DIR__ . '/../routes/console.php', // Commandes Artisan
        health: '/up',                               // Endpoint de santé (monitoring)
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares globaux ou groupes personnalisés à définir ici si besoin.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gestion globale des exceptions (logs, pages d'erreur personnalisées…).
    })
    ->withProviders([
        AppServiceProvider::class,
        AuthServiceProvider::class, // Enregistre le driver d'auth 'toad' (ToadUserProvider)
    ])
    ->create();
