<?php

namespace App\Http\Controllers;

use App\Models\TranscriptConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TranscriptConfigController extends Controller
{
    public function show()
    {
        $config = TranscriptConfig::first();
        return response()->json($config);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_kelulusan' => 'required|date',
            'tanggal_transkrip' => 'required|date',
            'skala_penilaian' => 'required|in:0-100,0-10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $config = TranscriptConfig::firstOrNew();
            $config->fill($request->only([
                'tanggal_kelulusan',
                'tanggal_transkrip',
                'skala_penilaian',
            ]));
            $config->save();

            return response()->json([
                'message' => 'Konfigurasi transkrip berhasil disimpan',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan konfigurasi',
            ], 500);
        }
    }
}
