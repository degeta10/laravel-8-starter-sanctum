<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('token/refresh', [AuthController::class, 'refreshToken']);
});

Route::group([
    'prefix' => 'auth',
    'middleware' => 'auth:api'
], function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::patch('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
});
