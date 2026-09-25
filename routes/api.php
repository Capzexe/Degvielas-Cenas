<?php

use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\Api\PriceHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/stations/cheapest', [StationController::class, 'cheapest']);
Route::get('/prices/history', [PriceHistoryController::class, 'index']);
