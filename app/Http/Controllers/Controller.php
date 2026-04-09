<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Contrôleur de base dont héritent tous les contrôleurs du projet.
 * Fournit les traits de validation des requêtes et d'autorisation des actions.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
