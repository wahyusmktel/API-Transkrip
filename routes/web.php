<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranskripNilaiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/transkrip-nilai/{siswa}/pdf', [TranskripNilaiController::class, 'generatePdf']);