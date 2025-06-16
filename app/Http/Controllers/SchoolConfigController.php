<?php

namespace App\Http\Controllers;

use App\Models\SchoolConfig;
use Illuminate\Http\Request;

class SchoolConfigController extends Controller
{
    public function show()
    {
        try {
            $data = SchoolConfig::first();
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil konfigurasi sekolah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
