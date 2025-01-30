<?php

use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage/{path}', function ($path) {
    $file = Storage::disk('public')->get($path);
    return response($file)
        ->header('Content-Type', 'image/jpeg') // Adjust MIME type as needed
        ->header('Access-Control-Allow-Origin', 'http://localhost:3000');
})->where('path', '.*');

Route::get('storage/{filename}', [StorageController::class, 'getFile']);
