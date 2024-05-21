<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/video', function () {
    return view('hls');
});

Route::get('/upload', function () {
    return view('upload');
});

Route::post('/upload-video', [VideoController::class, 'transcodeVideo'])->name('upload.video');
