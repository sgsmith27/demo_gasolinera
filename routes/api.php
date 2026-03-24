<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\FuelDeliveryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\FuelPriceController;
use App\Http\Controllers\Api\FuelController;
use App\Http\Controllers\Api\ExpenseController;


Route::post('/sales', [SaleController::class, 'store']);
Route::post('/fuel-deliveries', [FuelDeliveryController::class, 'store']);
Route::get('/reports/daily-close', [ReportController::class, 'dailyClose']);
Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary']);
Route::post('/fuel-prices', [FuelPriceController::class, 'store']);
Route::get('/fuels/{fuel}/current-price', [FuelController::class, 'currentPrice']);
Route::get('/reports/cashier-close', [ReportController::class, 'cashierClose']);
Route::get('/reports/cashier-close-range', [ReportController::class, 'cashierCloseRange']);
Route::get('/expenses', [ExpenseController::class, 'index']);
Route::post('/expenses', [ExpenseController::class, 'store']);
Route::get('/fuels-with-prices', [FuelController::class, 'fuelsWithPrices']);
Route::get('/sales-list', [SaleController::class, 'list']);
Route::get('/fuel-deliveries-list', [FuelDeliveryController::class, 'list']);