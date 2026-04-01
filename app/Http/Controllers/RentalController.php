<?php

namespace App\Http\Controllers;

use App\Services\ToadRentalService;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    private ToadRentalService $rentalService;

    public function __construct(ToadRentalService $rentalService)
    {
        $this->middleware('auth');
        $this->rentalService = $rentalService;
    }

    public function index()
    {
        return view('rentals.index', [
            'allowedLimits' => [10, 20, 50],
        ]);
    }

    public function getData(Request $request)
    {
        $validated = $request->validate([
            'page'   => 'integer|min:1',
            'limit'  => 'integer|in:10,20,50',
            'status' => 'nullable|integer|in:1,2,3',
        ]);

        $page   = $validated['page'] ?? 1;
        $limit  = $validated['limit'] ?? 10;
        $status = isset($validated['status']) ? (int) $validated['status'] : null;

        // Filtrage par statut : on récupère tout et on filtre en PHP
        if ($status !== null) {
            $all = $this->rentalService->fetchAllRentals();

            if ($all === null) {
                return response()->json(['error' => 'Impossible de récupérer les locations'], 500);
            }

            $filtered = array_values(array_filter($all, fn($r) => ($r['statusId'] ?? null) === $status));
            $total      = count($filtered);
            $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;
            $offset     = ($page - 1) * $limit;
            $rentals    = array_slice($filtered, $offset, $limit);

            return response()->json([
                'rentals'      => $rentals,
                'totalRentals' => $total,
                'totalPages'   => $totalPages,
                'currentPage'  => $page,
            ]);
        }

        // Pas de filtre : pagination normale via l'API
        $offset = ($page - 1) * $limit;
        $data   = $this->rentalService->getAllRentals($limit, $offset);

        if (!$data) {
            return response()->json(['error' => 'Impossible de récupérer les locations'], 500);
        }

        return response()->json([
            'rentals'      => $data['content'] ?? [],
            'totalRentals' => $data['totalElements'] ?? 0,
            'totalPages'   => $data['totalPages'] ?? 1,
            'currentPage'  => $page,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'statusId' => 'required|integer|in:1,2,3',
        ]);

        // Récupère le rental complet
        $rental = $this->rentalService->getRentalById($id);

        if (!$rental) {
            return response()->json(['error' => 'Location introuvable'], 404);
        }

        // Remplace uniquement le statusId et renvoie tout
        $data = [
            'rentalId'    => $rental['rentalId']    ?? $id,
            'rentalDate'  => $rental['rentalDate']  ?? null,
            'returnDate'  => $rental['returnDate']  ?? null,
            'inventoryId' => $rental['inventoryId'] ?? null,
            'customerId'  => $rental['customerId']  ?? null,
            'staffId'     => $rental['staffId']     ?? 1,
            'statusId'    => $validated['statusId'],
        ];

        $updated = $this->rentalService->updateRental($id, $data);

        if (!$updated) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du statut'], 500);
        }

        return response()->json(['success' => true, 'statusId' => $validated['statusId']]);
    }
}
