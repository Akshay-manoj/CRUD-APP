<?php

use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('user', UserController::class)->except(['index']);
    Route::get('users', [UserController::class, 'index']);
});
