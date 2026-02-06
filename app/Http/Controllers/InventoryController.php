<?php

namespace App\Http\Controllers;

use App\Services\ToadInventoryService;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    private ToadInventoryService $inventoryService;

    public function __construct(ToadInventoryService $inventoryService)
    {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $inventories = $this->inventoryService->getAllInventories();

        // Stocker les inventaires en cache pour 5 minutes pour éviter de refaire l'appel API
        if ($inventories) {
            Cache::put('all_inventories', $inventories, 300);
        }

        // Grouper les inventaires par film
        $groupedInventories = [];

        if ($inventories) {
            foreach ($inventories as $inventory) {
                $filmId = $inventory['filmId'];

                if (!isset($groupedInventories[$filmId])) {
                    $groupedInventories[$filmId] = [
                        'filmId' => $filmId,
                        'title' => $inventory['film']['title'] ?? 'Sans titre',
                        'description' => $inventory['film']['description'] ?? null,
                        'releaseYear' => $inventory['film']['releaseYear'] ?? null,
                        'totalCopies' => 0,
                        'stores' => [],
                        'inventoryIds' => [],
                        'inventoryDetails' => []
                    ];
                }

                $groupedInventories[$filmId]['totalCopies']++;
                $groupedInventories[$filmId]['inventoryIds'][] = $inventory['inventoryId'];

                // Ajouter les détails de chaque inventaire (sans érifier la disponibilité pour l'instant)
                $groupedInventories[$filmId]['inventoryDetails'][] = [
                    'inventoryId' => $inventory['inventoryId'],
                    'storeId' => $inventory['storeId'],
                    'lastUpdate' => $inventory['lastUpdate'] ?? null
                ];

                // Ajouter le store_id s'il n'est pas déjÃ  dans la liste
                $storeId = $inventory['storeId'];
                if (!in_array($storeId, $groupedInventories[$filmId]['stores'])) {
                    $groupedInventories[$filmId]['stores'][] = $storeId;
                }
            }
        }

        return view('inventory.index', [
            'groupedInventories' => array_values($groupedInventories)
        ]);
    }

    public function create(\Illuminate\Http\Request $request)
    {
        $films = $this->inventoryService->getAllFilms();
        $stores = $this->inventoryService->getAllStores();

        // Récupérer le filmId depuis la requÃªte (si fourni)
        $preselectedFilmId = $request->query('filmId');

        return view('inventory.create', [
            'films' => $films ?? [],
            'stores' => $stores ?? [],
            'preselectedFilmId' => $preselectedFilmId
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'filmId' => 'required|integer',
            'storeId' => 'required|integer'
        ]);

        $result = $this->inventoryService->createInventory([
            'filmId' => $validated['filmId'],
            'storeId' => $validated['storeId']
        ]);

        if ($result) {
            // Invalider le cache aprÃ¨s création
            Cache::forget('all_inventories');

            // Si on a un filmId, rediriger vers la page du film
            if ($request->has('filmId')) {
                return redirect()->route('inventory.film.show', $validated['filmId'])
                    ->with('success', 'Le DVD a été créé avec succÃ¨s');
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Le DVD a été créé avec succÃ¨s');
        }

        return redirect()->route('inventory.index')
            ->with('error', 'Erreur lors de la création du DVD');
    }

    public function edit($id)
    {
        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            return redirect()->route('inventory.index')
                ->with('error', 'Inventaire non trouvé');
        }

        $stores = $this->inventoryService->getAllStores();

        return view('inventory.edit', [
            'inventory' => $inventory,
            'stores' => $stores ?? []
        ]);
    }

    public function update($id, \Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'storeId' => 'required|integer',
            'filmId' => 'required|integer'
        ]);

        // Recharger l'inventaire pour s'assurer qu'il existe toujours
        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            return redirect()->route('inventory.index')
                ->with('error', 'Inventaire non trouvé');
        }

        // Envoyer filmId ET storeId à l'API (requis par l'API)
        $result = $this->inventoryService->updateInventory($id, [
            'filmId' => $validated['filmId'],
            'storeId' => $validated['storeId']
        ]);

        if ($result) {
            // Invalider le cache après modification
            Cache::forget('all_inventories');

            return redirect()->route('inventory.index')
                ->with('success', 'Le lieu de stockage a été modifié avec succès');
        }

        return redirect()->route('inventory.index')
            ->with('error', 'Erreur lors de la modification du lieu de stockage');
    }
    /**
     * Affiche tous les DVDs d'un film spécifique
     */
    public function showFilmInventories($filmId)
    {
        // Essayer de récupérer depuis le cache d'abord
        $inventories = Cache::get('all_inventories');

        // Si pas en cache, faire l'appel API
        if (!$inventories) {
            $inventories = $this->inventoryService->getAllInventories();

            if ($inventories) {
                Cache::put('all_inventories', $inventories, 300);
            }
        }

        if (!$inventories) {
            return redirect()->route('inventory.index')
                ->with('error', 'Erreur lors de la récupération des données');
        }

        // Filtrer les inventaires pour ce film
        $filmInventories = array_filter($inventories, function($inv) use ($filmId) {
            return $inv['filmId'] == $filmId;
        });

        if (empty($filmInventories)) {
            return redirect()->route('inventory.index')
                ->with('error', 'Aucun DVD trouvé pour ce film');
        }

        // Récupérer les infos du film depuis le premier inventaire
        $firstInventory = reset($filmInventories);
        $filmInfo = [
            'filmId' => $filmId,
            'title' => $firstInventory['film']['title'] ?? 'Sans titre',
            'description' => $firstInventory['film']['description'] ?? null,
            'releaseYear' => $firstInventory['film']['releaseYear'] ?? null,
        ];

        // Préparer les détails des inventaires
        $inventoryDetails = [];
        foreach ($filmInventories as $inventory) {
            $inventoryDetails[] = [
                'inventoryId' => $inventory['inventoryId'],
                'storeId' => $inventory['storeId'],
                'lastUpdate' => $inventory['lastUpdate'] ?? null
            ];
        }

        return view('inventory.show', [
            'film' => $filmInfo,
            'inventories' => $inventoryDetails
        ]);
    }

    /**
     * AJAX endpoint to check availability for a single DVD
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
     * Supprime plusieurs inventaires
     */
    public function deleteMultiple(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'inventoryIds' => 'required|array',
            'inventoryIds.*' => 'required|integer'
        ]);

        $inventoryIds = $validated['inventoryIds'];
        $successCount = 0;
        $failedCount = 0;

        // Supprimer chaque inventaire un par un
        foreach ($inventoryIds as $id) {
            $result = $this->inventoryService->deleteInventory($id);
            if ($result) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        // Invalider le cache aprÃ¨s suppression
        if ($successCount > 0) {
            Cache::forget('all_inventories');
        }

        // Préparer le message de retour
        if ($failedCount === 0) {
            return response()->json([
                'success' => true,
                'message' => $successCount . ' DVD(s) supprimé(s) avec succÃ¨s'
            ]);
        } elseif ($successCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des DVDs'
            ], 500);
        } else {
            return response()->json([
                'success' => true,
                'message' => $successCount . ' DVD(s) supprimé(s), ' . $failedCount . ' échec(s)'
            ]);
        }
    }
}

