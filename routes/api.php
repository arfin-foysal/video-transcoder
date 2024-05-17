<?php

use App\Http\Controllers\TranscodedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/video', [TranscodedController::class, 'Store']);
Route::get('/video/{id}', [TranscodedController::class, 'show']);