<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HousingUnitController;
use App\Http\Controllers\MaintenanceTicketController;
use App\Http\Controllers\PlacementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $errors = [];

    try {
        DB::connection()->getPdo();
    } catch (\Throwable $e) {
        $errors[] = 'database';
        Log::error('Health check: database connection failed', ['exception' => $e->getMessage()]);
    }

    try {
        Cache::has('health_check');
    } catch (\Throwable $e) {
        $errors[] = 'cache';
        Log::error('Health check: cache connection failed', ['exception' => $e->getMessage()]);
    }

    if ($errors) {
        return response()->json([
            'status' => 'unhealthy',
            'failures' => $errors,
        ], 503);
    }

    return response()->json(['status' => 'healthy'], 200);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('housing-units', HousingUnitController::class);
    Route::apiResource('maintenance-tickets', MaintenanceTicketController::class);
    Route::apiResource('placements', PlacementController::class);
});
