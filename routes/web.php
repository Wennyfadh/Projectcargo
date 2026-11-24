<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CurrencyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocaleController;
use Illuminate\Support\Facades\Route;

// Public Locale Switcher for 7 World Languages
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Real-Time Market, Weather, Rates & News Telemetry API for Running Ticker
Route::get('/api/market-telemetry', [DashboardController::class, 'marketTelemetry'])->name('market.telemetry');
Route::get('/api/currency/rates', [CurrencyController::class, 'rates'])->name('currency.rates');
Route::get('/api/currency/history', [CurrencyController::class, 'history'])->name('currency.history');
Route::get('/api/currency/convert', [CurrencyController::class, 'convert'])->name('currency.convert');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/shipments', [DashboardController::class, 'shipments'])->name('shipments.index');
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/tracking', [DashboardController::class, 'tracking'])->name('tracking.index');
    Route::get('/tracking/{shipment}', [DashboardController::class, 'trackingShow'])->name('tracking.show');
    Route::get('/tracking/{shipment}/data', [DashboardController::class, 'trackingData'])->name('tracking.data');
    Route::get('/exceptions', [DashboardController::class, 'exceptions'])->name('exceptions.index');
    Route::get('/carriers', [DashboardController::class, 'carriers'])->name('carriers.index');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports.index');
});
