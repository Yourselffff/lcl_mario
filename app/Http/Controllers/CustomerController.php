<?php

namespace App\Http\Controllers;

use App\Services\ToadCustomerService;
use Illuminate\Http\Request;

/**
 * Contrôleur CRUD clients.
 * Délègue toutes les opérations à ToadCustomerService (appels API Toad).
 */
class CustomerController extends Controller
{
    private ToadCustomerService $customerService;

    public function __construct(ToadCustomerService $customerService)
    {
        $this->middleware('auth');
        $this->customerService = $customerService;
    }

    /** Affiche la liste paginée des clients (chargement AJAX via getData). */
    public function index()
    {
        return view('customers.index', [
            'allowedLimits' => [10, 20, 50],
        ]);
    }

    /**
     * Endpoint AJAX appelé par la vue pour récupérer une page de clients.
     *
     * @return \Illuminate\Http\JsonResponse {customers, totalCustomers, totalPages, currentPage}
     */
    public function getData(Request $request)
    {
        $validated = $request->validate([
            'page'  => 'integer|min:1',
            'limit' => 'integer|in:10,20,50',
        ]);

        $page   = $validated['page'] ?? 1;
        $limit  = $validated['limit'] ?? 10;
        $offset = ($page - 1) * $limit;

        $total     = $this->customerService->getCustomersCount();
        $customers = $this->customerService->getAllCustomers($limit, $offset);

        if ($customers === null) {
            return response()->json(['error' => 'Impossible de récupérer les clients'], 500);
        }

        return response()->json([
            'customers'      => $customers,
            'totalCustomers' => $total,
            'totalPages'     => $total > 0 ? (int) ceil($total / $limit) : 1,
            'currentPage'    => $page,
        ]);
    }

    public function create()
    {
        return view('customers.create');
    }

    /**
     * Valide et crée un client via l'API.
     * Les noms sont convertis en majuscules pour correspondre au format de la BDD.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'storeId'   => 'required|integer',
            'firstName' => 'required|string|max:45',
            'lastName'  => 'required|string|max:45',
            'email'     => 'required|email|max:50',
            'password'  => 'required|string|min:4',
            'addressId' => 'required|integer',
            'active'    => 'boolean',
        ]);

        $data = [
            'storeId'    => (int) $validated['storeId'],
            'firstName'  => strtoupper($validated['firstName']),
            'lastName'   => strtoupper($validated['lastName']),
            'email'      => $validated['email'],
            'password'   => $validated['password'],
            'addressId'  => (int) $validated['addressId'],
            'active'     => $request->boolean('active', true),
            'createDate' => now()->format('Y-m-d\TH:i:s'),
        ];

        $customer = $this->customerService->createCustomer($data);

        if (!$customer) {
            return back()->withInput()->with('error', 'Erreur lors de la création du client.');
        }

        return redirect()->route('customers.index')->with('success', 'Client créé avec succès.');
    }

    public function show(int $id)
    {
        $customer = $this->customerService->getCustomerById($id);

        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'Client introuvable.');
        }

        return view('customers.show', compact('customer'));
    }

    public function edit(int $id)
    {
        $customer = $this->customerService->getCustomerById($id);

        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'Client introuvable.');
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Met à jour un client. Le mot de passe est optionnel (omis si champ vide).
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'storeId'    => 'required|integer',
            'firstName'  => 'required|string|max:45',
            'lastName'   => 'required|string|max:45',
            'email'      => 'required|email|max:50',
            'password'   => 'nullable|string|min:4',
            'addressId'  => 'required|integer',
            'active'     => 'boolean',
            'createDate' => 'required|string',
        ]);

        $data = [
            'storeId'    => (int) $validated['storeId'],
            'firstName'  => strtoupper($validated['firstName']),
            'lastName'   => strtoupper($validated['lastName']),
            'email'      => $validated['email'],
            'addressId'  => (int) $validated['addressId'],
            'active'     => $request->boolean('active', true),
            'createDate' => $validated['createDate'],
        ];

        // N'envoie le mot de passe que s'il a été renseigné dans le formulaire
        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $customer = $this->customerService->updateCustomer($id, $data);

        if (!$customer) {
            return back()->withInput()->with('error', 'Erreur lors de la modification du client.');
        }

        return redirect()->route('customers.show', $id)->with('success', 'Client modifié avec succès.');
    }

    public function destroy(int $id)
    {
        $ok = $this->customerService->deleteCustomer($id);

        if (!$ok) {
            return redirect()->route('customers.index')->with('error', 'Erreur lors de la suppression du client.');
        }

        return redirect()->route('customers.index')->with('success', 'Client supprimé avec succès.');
    }
}
