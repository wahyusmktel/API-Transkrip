<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\TranskripNilaiController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/transkrip-nilai/{siswa}/pdf', [TranskripNilaiController::class, 'generatePdf']);
Route::get('/test-watermark', function () {
    $path = storage_path('app/public/uploads/school-config/Vin1KeMTHyYhoR4y5o8CZaG05Soh6jJnMzV7XQiB.png');

    if (!file_exists($path)) {
        return "File tidak ditemukan.";
    }

    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $content = file_get_contents($path);
    $base64 = 'data:image/' . $ext . ';base64,' . base64_encode($content);

    return <<<HTML
    <img src="$base64" style="max-width:500px;" />
    <p>Berhasil ditampilkan sebagai Base64!</p>
    HTML;
});
