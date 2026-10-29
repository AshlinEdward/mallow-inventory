<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\LowStockController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/customers/orders', [CustomerOrderController::class, 'index']);
Route::get('/products/low-stock', [LowStockController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/customers', [
    \App\Http\Controllers\Api\CustomerOrderController::class,
    'customers',
]);