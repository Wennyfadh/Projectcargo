<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExceptionController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::middleware(['web', 'auth:sanctum,web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Browser-friendly redirect when accessing GET /api/... directly in browser address bar
    Route::get('/shipments', function (Request $request) {
        if (! $request->expectsJson() && $request->acceptsHtml()) {
            return redirect()->route('shipments.index');
        }
        return app(ShipmentController::class)->index($request);
    });

    Route::get('/carriers', function (Request $request) {
        if (! $request->expectsJson() && $request->acceptsHtml()) {
            return redirect()->route('carriers.index');
        }
        return app(CarrierController::class)->index($request);
    });

    Route::apiResource('carriers', CarrierController::class)->except(['index']);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('shipments', ShipmentController::class)->except(['index']);
    Route::get('/shipments/{shipment}/tracking', [TrackingController::class, 'index'])->name('shipments.tracking.index');
    Route::post('/shipments/{shipment}/tracking', [TrackingController::class, 'store'])->name('shipments.tracking.store');
    Route::get('/shipments/{shipment}/exceptions', [ExceptionController::class, 'index'])->name('shipments.exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('shipments.exceptions.store');
    Route::patch('/exceptions/{exception}', [ExceptionController::class, 'update'])->name('exceptions.update');
});
