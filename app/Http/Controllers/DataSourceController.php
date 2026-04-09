<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Permet de basculer la source de données entre l'API locale et l'API distante.
 * La source active est stockée en session sous la clé 'toad_source'.
 */
class DataSourceController extends Controller
{
    /**
     * Change la source de données active pour la session courante.
     * Valeurs acceptées : 'local' (localhost) ou 'remote' (rftg.mtb111.com).
     */
    public function switch(Request $request)
    {
        $source = $request->input('source');

        if (!in_array($source, ['local', 'remote'])) {
            return back()->with('error', 'Source de données invalide.');
        }

        session(['toad_source' => $source]);

        $label = $source === 'remote' ? 'API distante (rftg.mtb111.com)' : 'API locale (localhost)';

        return back()->with('success', "Source de données changée : {$label}");
    }
}
