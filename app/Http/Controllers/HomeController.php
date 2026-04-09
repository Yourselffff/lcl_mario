<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Contrôleur du tableau de bord principal.
 * Accessible uniquement aux utilisateurs connectés.
 */
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('home');
    }
}
