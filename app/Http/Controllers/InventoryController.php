<?php

namespace App\Http\Controllers;

use App\Services\ToadInventoryService;
use Illuminate\Support\Facades\Cache;

/**
 * Contrôleur inventaire (DVDs).
 * Gère l'affichage groupé par film, le CRUD des entrées inventaire
 * et la vérification de disponibilité d'un DVD.
 */
class InventoryController extends Controller
{
    private ToadInventoryService $inventoryService;

    public function __construct(ToadInventoryService $inventoryService)
    {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
    }

    /**
     * Affiche tous les inventaires groupés par film.
     * Met en cache la liste 5 minutes pour limiter les appels à l'API.
     */
    public function index()
    {
        $inventories = $this->inventoryService->getAllInventories();

        if ($inventories) {
            Cache::put('all_inventories', $inventories, 300);
        }

        // Regroupement par filmId : agrège les copies et les stores par film
        $groupedInventories = [];

        if ($inventories) {
            foreach ($inventories as $inventory) {
                $filmId = $inventory['filmId'];

                if (!isset($groupedInventories[$filmId])) {
                    $groupedInventories[$filmId] = [
                        'filmId'           => $filmId,
                        'title'            => $inventory['film']['title'] ?? 'Sans titre',
                        'description'      => $inventory['film']['description'] ?? null,
                        'releaseYear'      => $inventory['film']['releaseYear'] ?? null,
                        'totalCopies'      => 0,
                        'stores'           => [],
                        'inventoryIds'     => [],
                        'inventoryDetails' => [],
                    ];
                }

                $groupedInventories[$filmId]['totalCopies']++;
                $groupedInventories[$filmId]['inventoryIds'][] = $inventory['inventoryId'];

                $groupedInventories[$filmId]['inventoryDetails'][] = [
                    'inventoryId' => $inventory['inventoryId'],
                    'storeId'     => $inventory['storeId'],
                    'lastUpdate'  => $inventory['lastUpdate'] ?? null,
                ];

                // Ajoute le storeId à la liste si pas déjà présent
                $storeId = $inventory['storeId'];
                if (!in_array($storeId, $groupedInventories[$filmId]['stores'])) {
                    $groupedInventories[$filmId]['stores'][] = $storeId;
                }
            }
        }

        return view('inventory.index', [
            'groupedInventories' => array_values($groupedInventories),
        ]);
    }

    /**
     * Affiche le formulaire de création d'un DVD.
     * Accepte un paramètre filmId en query string pour pré-sélectionner un film.
     */
    public function create(\Illuminate\Http\Request $request)
    {
        $films  = $this->inventoryService->getAllFilms();
        $stores = $this->inventoryService->getAllStores();

        $preselectedFilmId = $request->query('filmId');

        return view('inventory.create', [
            'films'             => $films ?? [],
            'stores'            => $stores ?? [],
            'preselectedFilmId' => $preselectedFilmId,
        ]);
    }

    /**
     * Crée un DVD (inventaire) et invalide le cache après création.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'filmId'  => 'required|integer',
            'storeId' => 'required|integer',
        ]);

        $result = $this->inventoryService->createInventory([
            'filmId'  => $validated['filmId'],
            'storeId' => $validated['storeId'],
        ]);

        if ($result) {
            Cache::forget('all_inventories');

            // Redirige vers la page du film si le filmId est fourni
            if ($request->has('filmId')) {
                return redirect()->route('inventory.film.show', $validated['filmId'])
                    ->with('success', 'Le DVD a été créé avec succès');
            }

            return redirect()->route('inventory.index')->with('success', 'Le DVD a été créé avec succès');
        }

        return redirect()->route('inventory.index')->with('error', 'Erreur lors de la création du DVD');
    }

    public function edit($id)
    {
        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            return redirect()->route('inventory.index')->with('error', 'Inventaire non trouvé');
        }

        $stores = $this->inventoryService->getAllStores();

        return view('inventory.edit', [
            'inventory' => $inventory,
            'stores'    => $stores ?? [],
        ]);
    }

    /**
     * Met à jour le store d'un inventaire.
     * L'API requiert filmId ET storeId même si seul le store change.
     */
    public function update($id, \Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'storeId' => 'required|integer',
            'filmId'  => 'required|integer',
        ]);

        $inventory = $this->inventoryService->getInventoryById($id);
        if (!$inventory) {
            return redirect()->route('inventory.index')->with('error', 'Inventaire non trouvé');
        }

        $result = $this->inventoryService->updateInventory($id, [
            'filmId'  => $validated['filmId'],
            'storeId' => $validated['storeId'],
        ]);

        if ($result) {
            Cache::forget('all_inventories');
            return redirect()->route('inventory.index')->with('success', 'Le lieu de stockage a été modifié avec succès');
        }

        return redirect()->route('inventory.index')->with('error', 'Erreur lors de la modification du lieu de stockage');
    }

    /**
     * Affiche tous les DVDs d'un film spécifique.
     * Utilise le cache si disponible pour éviter un appel API redondant.
     */
    public function showFilmInventories($filmId)
    {
        $inventories = Cache::get('all_inventories');

        if (!$inventories) {
            $inventories = $this->inventoryService->getAllInventories();
            if ($inventories) {
                Cache::put('all_inventories', $inventories, 300);
            }
        }

        if (!$inventories) {
            return redirect()->route('inventory.index')->with('error', 'Erreur lors de la récupération des données');
        }

        $filmInventories = array_filter($inventories, fn($inv) => $inv['filmId'] == $filmId);

        if (empty($filmInventories)) {
            return redirect()->route('inventory.index')->with('error', 'Aucun DVD trouvé pour ce film');
        }

        $firstInventory = reset($filmInventories);
        $filmInfo = [
            'filmId'      => $filmId,
            'title'       => $firstInventory['film']['title'] ?? 'Sans titre',
            'description' => $firstInventory['film']['description'] ?? null,
            'releaseYear' => $firstInventory['film']['releaseYear'] ?? null,
        ];

        $inventoryDetails = [];
        foreach ($filmInventories as $inventory) {
            $inventoryDetails[] = [
                'inventoryId' => $inventory['inventoryId'],
                'storeId'     => $inventory['storeId'],
                'lastUpdate'  => $inventory['lastUpdate'] ?? null,
            ];
        }

        return view('inventory.show', [
            'film'        => $filmInfo,
            'inventories' => $inventoryDetails,
        ]);
    }

    /**
     * Endpoint AJAX : vérifie si un DVD est disponible à la location.
     *
     * @return \Illuminate\Http\JsonResponse {isAvailable: bool}
     */
    public function checkDVDAvailability($id)
    {
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);

        if ($isAvailable === null) {
            return response()->json(['error' => 'Unable to check availability'], 500);
        }

        return response()->json(['isAvailable' => $isAvailable]);
    }

    /**
     * Supprime plusieurs DVDs en une seule requête (sélection multiple).
     *
     * @return \Illuminate\Http\JsonResponse Résumé {success, message}
     */
    public function deleteMultiple(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'inventoryIds'   => 'required|array',
            'inventoryIds.*' => 'required|integer',
        ]);

        $successCount = 0;
        $failedCount  = 0;

        foreach ($validated['inventoryIds'] as $id) {
            $this->inventoryService->deleteInventory($id) ? $successCount++ : $failedCount++;
        }

        if ($successCount > 0) {
            Cache::forget('all_inventories');
        }

        if ($failedCount === 0) {
            return response()->json(['success' => true, 'message' => $successCount . ' DVD(s) supprimé(s) avec succès']);
        } elseif ($successCount === 0) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression des DVDs'], 500);
        }

        return response()->json(['success' => true, 'message' => $successCount . ' DVD(s) supprimé(s), ' . $failedCount . ' échec(s)']);
    }
}
