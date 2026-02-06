<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\InventoryController;

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
