<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — e-Bank
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/user',   [UserController::class, 'me']);

    Route::prefix('account')->group(function () {
        Route::get('/',        [AccountController::class, 'show']);
        Route::post('/credit', [AccountController::class, 'credit']);
        Route::post('/debit',  [AccountController::class, 'debit']);
    });
});
