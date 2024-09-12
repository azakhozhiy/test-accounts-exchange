<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/v1')->name('v1.')->group(function (): void {

    Route::prefix('/auth')->name('auth.')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('me', [AuthController::class, 'me'])->name('me');
    });

    Route::middleware('api')->group(function () {
        Route::prefix('/accounts')->name('accounts.')->group(function (): void {
            Route::get('/', [AccountController::class, 'getList'])->name('list');
        });


        Route::prefix('/orders')->name('orders.')->group(function (): void {
            Route::get('/', [OrderController::class, 'getList'])->name('list');
            Route::post('/', [OrderController::class, 'create'])->name('create');

            Route::prefix('/{uuid}')->name('one.')->group(function (): void {
                Route::post('/accept', [OrderController::class, 'accept'])->name('accept');
                Route::post('/cancel', [OrderController::class, 'cancel'])->name('cancel');
            });
        });
    });
});
