<?php

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

Route::post('register', [\App\Http\Controllers\API\RegisterController::class, 'register']);
Route::post('login', [\App\Http\Controllers\API\RegisterController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::prefix('/note')->group(function () {
        Route::get('/{id}', [\App\Http\Controllers\API\NoteController::class, 'watch'])->where('id', '[0-9]+');
        Route::post('/create', [\App\Http\Controllers\API\NoteController::class, 'store']);
        Route::post('/update/{id}', [\App\Http\Controllers\API\NoteController::class, 'update']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\API\NoteController::class, 'delete']);

        Route::post('/tags/add/{id}', [\App\Http\Controllers\API\NoteController::class, 'addTags']);

        Route::get('/search/', [\App\Http\Controllers\API\NoteController::class, 'search']);
    });
});
