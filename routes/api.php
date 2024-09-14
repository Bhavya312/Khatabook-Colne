<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function() {
    Route::post('login', [\App\Http\Controllers\LoginController::class, 'login']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('refresh', [\App\Http\Controllers\LoginController::class, 'refresh']);
    Route::post('logout', [\App\Http\Controllers\LoginController::class, 'logout']);
    Route::get('user-details', [\App\Http\Controllers\LoginController::class, 'userDetails']);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
});
