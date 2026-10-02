<?php

use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\RefreshFuelPricesController;
use Illuminate\Support\Facades\Route;

Route::get('/stations/cheapest', [StationController::class, 'cheapest']);
Route::get('/prices/history', [PriceHistoryController::class, 'index']);
Route::post('/prices/refresh', RefreshFuelPricesController::class)->middleware('throttle:1,10');
