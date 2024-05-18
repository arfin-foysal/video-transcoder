<?php

use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/video/{id}', [VideoController::class, 'getVideosById']);
Route::post('/transcode', [VideoController::class, 'transcodeVideo']);
