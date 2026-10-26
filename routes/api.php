<?php

use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\StationController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-api')->group(function (): void {
    Route::get('/stations/cheapest', [StationController::class, 'cheapest']);
    Route::get('/prices/history', [PriceHistoryController::class, 'index'])->middleware('throttle:price-history');
});
