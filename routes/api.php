<?php

use App\Http\Controllers\HelperController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
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
Route::put('/login', [UsersController::class, 'login']);

Route::prefix('users')->group(function() {
    Route::middleware(['api-auth', 'admin-auth'])->put('/create', [UsersController::class, 'create']);

    Route::middleware(['api-auth', 'admin-auth'])->get('/view', [UsersController::class, 'view']);
    Route::middleware(['api-auth', 'admin-auth'])->put('/view', [UsersController::class, 'viewDetails']);

    Route::middleware(['api-auth'])->get('/profile', [UsersController::class, 'profile']);

    Route::middleware(['api-auth', 'admin-auth'])->put('/edit', [UsersController::class, 'edit']);

    Route::middleware(['api-auth'])->get('/recover', [UsersController::class, 'recover']);
    Route::middleware(['api-auth'])->put('/changePassword', [UsersController::class, 'changePassword']);
});
