<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\StatusDefinitionController;
use App\Http\Controllers\Api\PropertyDefinitionController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  –  ITAMS
|--------------------------------------------------------------------------
| Auth: Laravel Sanctum (token-based)
| Login: LDAP-Bind via LdapAuthService  OR  local password fallback
*/

// ── Public ───────────────────────────────────────
Route::post('/auth/login',  [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ── Protected ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);

    // ── Assets ───────────────────────────────────
    Route::apiResource('assets', AssetController::class);
    Route::get ('assets/{asset}/history',      [AssetController::class, 'history']);
    Route::get ('assets/{asset}/assignments',  [AssetController::class, 'assignments']);
    Route::get ('assets/{asset}/transactions', [AssetController::class, 'transactions']);
    Route::post('assets/{asset}/assign',       [AssetController::class, 'assign']);
    Route::post('assets/{asset}/unassign',     [AssetController::class, 'unassign']);
    Route::post('assets/{asset}/properties',   [AssetController::class, 'syncProperties']);

    // ── Categories ───────────────────────────────
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/{category}/tree',               [CategoryController::class, 'tree']);
    Route::get('categories/{category}/property-definitions',[CategoryController::class, 'propertyDefinitions']);
    Route::put('categories/{category}/property-definitions',[CategoryController::class, 'syncPropertyDefinitions']);

    // ── Tags ─────────────────────────────────────
    Route::apiResource('tags', TagController::class);

    // ── Locations ────────────────────────────────
    Route::apiResource('locations', LocationController::class);
    Route::get('locations/{location}/tree', [LocationController::class, 'tree']);

    // ── Manufacturers ────────────────────────────
    Route::apiResource('manufacturers', ManufacturerController::class);

    // ── Lookups ──────────────────────────────────
    Route::apiResource('status-definitions',   StatusDefinitionController::class);
    Route::apiResource('property-definitions', PropertyDefinitionController::class);

    // ── Employees (read-only, AD-managed) ────────
    Route::get('employees',        [EmployeeController::class, 'index']);
    Route::get('employees/{employee}', [EmployeeController::class, 'show']);
});
