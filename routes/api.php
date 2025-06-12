<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\Api\SchoolConfigController;
use App\Http\Controllers\TranscriptConfigController;
use App\Http\Controllers\ProgramKeahlianController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::put('/', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);
    Route::post('/photo', [ProfileController::class, 'uploadPhoto']);
});

Route::middleware('auth:sanctum')->prefix('school-config')->group(function () {
    Route::get('/', [SchoolConfigController::class, 'show']);
    Route::post('/', [SchoolConfigController::class, 'storeOrUpdate']);
    Route::post('/upload', [SchoolConfigController::class, 'uploadFile']);
});

Route::middleware('auth:sanctum')->prefix('transcript-config')->group(function () {
    Route::get('/', [TranscriptConfigController::class, 'show']);
    Route::post('/', [TranscriptConfigController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('program-keahlian')->group(function () {
    Route::get('/', [ProgramKeahlianController::class, 'index']);
    Route::post('/', [ProgramKeahlianController::class, 'store']);
    Route::put('/{id}', [ProgramKeahlianController::class, 'update']);
    Route::delete('/{id}', [ProgramKeahlianController::class, 'destroy']);
});