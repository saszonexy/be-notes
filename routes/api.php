<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ProfileController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('update-photo', [ProfileController::class, 'updatePhoto']);
    Route::post('/update-name', [AuthController::class, 'updateName']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('notes', [NoteController::class, 'index']);
    Route::get('notes/{id}', [NoteController::class, 'show']);
    Route::post('notes', [NoteController::class, 'store']);
    Route::put('notes/{id}', [NoteController::class, 'update']);
    Route::delete('notes/{id}', [NoteController::class, 'destroy']);
});
