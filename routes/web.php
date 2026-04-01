<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\CustomerController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Routes films protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::post('/films/data', [FilmController::class, 'getData'])->name('films.data');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{id}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{id}', [FilmController::class, 'update'])->name('films.update');
    Route::get('/films/{id}', [FilmController::class, 'show'])->name('films.show');
    Route::delete('/films/{id}', [FilmController::class, 'destroy'])->name('films.destroy');
});

// Routes clients protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers/data', [CustomerController::class, 'getData'])->name('customers.data');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

// Routes locations protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::post('/rentals/data', [RentalController::class, 'getData'])->name('rentals.data');
    Route::post('/rentals/{id}/status', [RentalController::class, 'updateStatus'])->name('rentals.updateStatus');
});

// Routes inventaire protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/film/{filmId}', [InventoryController::class, 'showFilmInventories'])->name('inventory.film.show');
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{id}/check-availability', [InventoryController::class, 'checkDVDAvailability'])->name('inventory.check.availability');
    Route::post('/inventory/delete-multiple', [InventoryController::class, 'deleteMultiple'])->name('inventory.delete.multiple');
});
