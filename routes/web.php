<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DataSourceController;

// Page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification générées automatiquement par Laravel
// (login, logout, register, forgot-password, reset-password)
Auth::routes();

// Tableau de bord (protégé par le middleware 'auth')
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Bascule entre l'API locale et l'API distante (accessible sans authentification)
Route::post('/data-source/switch', [DataSourceController::class, 'switch'])->name('data-source.switch');

// --- Films ---
// Toutes les routes films sont protégées par le middleware 'auth'
Route::middleware('auth')->group(function () {
    Route::get('/films',              [FilmController::class, 'index'])->name('films.index');
    Route::post('/films/data',        [FilmController::class, 'getData'])->name('films.data');    // AJAX pagination
    Route::get('/films/create',       [FilmController::class, 'create'])->name('films.create');
    Route::post('/films',             [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{id}/edit',    [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{id}',         [FilmController::class, 'update'])->name('films.update');
    Route::get('/films/{id}',         [FilmController::class, 'show'])->name('films.show');
    Route::delete('/films/{id}',      [FilmController::class, 'destroy'])->name('films.destroy');
});

// --- Clients ---
Route::middleware('auth')->group(function () {
    Route::get('/customers',              [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers/data',        [CustomerController::class, 'getData'])->name('customers.data');  // AJAX pagination
    Route::get('/customers/create',       [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers',             [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}/edit',    [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}',         [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/{id}',         [CustomerController::class, 'show'])->name('customers.show');
    Route::delete('/customers/{id}',      [CustomerController::class, 'destroy'])->name('customers.destroy');
});

// --- Locations ---
Route::middleware('auth')->group(function () {
    Route::get('/rentals',             [RentalController::class, 'index'])->name('rentals.index');
    Route::post('/rentals/data',       [RentalController::class, 'getData'])->name('rentals.data');              // AJAX pagination + filtre
    Route::post('/rentals/{id}/status',[RentalController::class, 'updateStatus'])->name('rentals.updateStatus'); // AJAX mise à jour statut
});

// --- Inventaire ---
Route::middleware('auth')->group(function () {
    Route::get('/inventory',                         [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create',                  [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory',                        [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/film/{filmId}',           [InventoryController::class, 'showFilmInventories'])->name('inventory.film.show');
    Route::get('/inventory/{id}/edit',               [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}',                    [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{id}/check-availability', [InventoryController::class, 'checkDVDAvailability'])->name('inventory.check.availability'); // AJAX
    Route::post('/inventory/delete-multiple',        [InventoryController::class, 'deleteMultiple'])->name('inventory.delete.multiple');          // AJAX suppression multiple
});
