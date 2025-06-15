<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\Api\SchoolConfigController;
use App\Http\Controllers\TranscriptConfigController;
use App\Http\Controllers\ProgramKeahlianController;
use App\Http\Controllers\Api\MasterKelasController;
use App\Http\Controllers\Api\MataPelajaranController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\TranskripNilaiController;

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

Route::prefix('kelas')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MasterKelasController::class, 'index']);
    Route::post('/', [MasterKelasController::class, 'store']);
    Route::get('/{id}', [MasterKelasController::class, 'show']);
    Route::put('/{id}', [MasterKelasController::class, 'update']);
    Route::delete('/{id}', [MasterKelasController::class, 'destroy']);
});

Route::prefix('mata-pelajaran')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MataPelajaranController::class, 'index']);
    Route::post('/', [MataPelajaranController::class, 'store']);
    Route::get('/{id}', [MataPelajaranController::class, 'show']);
    Route::put('/{id}', [MataPelajaranController::class, 'update']);
    Route::delete('/{id}', [MataPelajaranController::class, 'destroy']);
    Route::post('/import', [MataPelajaranController::class, 'import']);
    Route::get('/template/mata-pelajaran', function () {
        return response()->download(storage_path('app/templates/template_mata_pelajaran.xlsx'));
    });
});

Route::prefix('siswa')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SiswaController::class, 'index']);
    Route::post('/', [SiswaController::class, 'store']);
    Route::get('/{id}', [SiswaController::class, 'show']);
    Route::put('/{id}', [SiswaController::class, 'update']);
    Route::delete('/{id}', [SiswaController::class, 'destroy']);
    Route::post('/import', [SiswaController::class, 'import']);
});

Route::prefix('transkrip-nilai')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TranskripNilaiController::class, 'index']);
    Route::post('/', [TranskripNilaiController::class, 'store']);
    Route::put('/{id}', [TranskripNilaiController::class, 'update']);
    Route::delete('/{id}', [TranskripNilaiController::class, 'destroy']);
    Route::get('/siswa-belum-nilai', [TranskripNilaiController::class, 'siswaBelumAdaNilai']);
});
