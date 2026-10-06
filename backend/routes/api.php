<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HousingUnitController;
use App\Http\Controllers\MaintenanceTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('housing-units', HousingUnitController::class);
    Route::apiResource('maintenance-tickets', MaintenanceTicketController::class);
});
