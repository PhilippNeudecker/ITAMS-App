<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\ManufacturerController;
use App\Http\Controllers\Web\TagController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AuthController;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Geschützte Routen ─────────────────────────────────────────────────────────
// Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('assets.index'));

    // Assets
    Route::prefix('assets')->name('assets.')->group(function () {
        // Tags
        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/',        [TagController::class, 'index'])->name('index');
            Route::get('/create',  [TagController::class, 'create'])->name('create');
            Route::post('/',       [TagController::class, 'store'])->name('store');
            Route::get('/{tag}',   [TagController::class, 'show'])->name('show');
            Route::get('/{tag}/edit',  [TagController::class, 'edit'])->name('edit');
            Route::patch('/{tag}',     [TagController::class, 'update'])->name('update');
            Route::delete('/{tag}',    [TagController::class, 'destroy'])->name('destroy');
        });

        Route::get('/',        [AssetController::class, 'index'])->name('index');
        Route::get('/create',  [AssetController::class, 'create'])->name('create');
        Route::post('/',       [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::patch('/{asset}',    [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}',   [AssetController::class, 'destroy'])->name('destroy');
    });

    // Kategorien
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/',              [CategoryController::class, 'index'])->name('index');
        Route::get('/create',        [CategoryController::class, 'create'])->name('create');
        Route::post('/',             [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}',    [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit',   [CategoryController::class, 'edit'])->name('edit');
        Route::patch('/{category}',      [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}',     [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Standorte
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/',              [LocationController::class, 'index'])->name('index');
        Route::get('/create',        [LocationController::class, 'create'])->name('create');
        Route::post('/',             [LocationController::class, 'store'])->name('store');
        Route::get('/{location}',    [LocationController::class, 'show'])->name('show');
        Route::get('/{location}/edit',   [LocationController::class, 'edit'])->name('edit');
        Route::patch('/{location}',      [LocationController::class, 'update'])->name('update');
        Route::delete('/{location}',     [LocationController::class, 'destroy'])->name('destroy');
    });

    // Hersteller
    Route::prefix('manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/',                  [ManufacturerController::class, 'index'])->name('index');
        Route::get('/create',            [ManufacturerController::class, 'create'])->name('create');
        Route::post('/',                 [ManufacturerController::class, 'store'])->name('store');
        Route::get('/{manufacturer}',    [ManufacturerController::class, 'show'])->name('show');
        Route::get('/{manufacturer}/edit',   [ManufacturerController::class, 'edit'])->name('edit');
        Route::patch('/{manufacturer}',      [ManufacturerController::class, 'update'])->name('update');
        Route::delete('/{manufacturer}',     [ManufacturerController::class, 'destroy'])->name('destroy');
    });

    // Mitarbeiter (read-only)
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/',             [EmployeeController::class, 'index'])->name('index');
        Route::get('/{employee}',   [EmployeeController::class, 'show'])->name('show');
    });

    // Einstellungen
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
    });
// });
