<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolConfig;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SchoolConfigController extends Controller
{
    public function show()
    {
        $config = SchoolConfig::first();

        if (!$config) {
            return response()->json([
                'message' => 'Data konfigurasi belum tersedia',
            ], 404);
        }

        return response()->json([
            'nama_sekolah' => $config->nama_sekolah,
            'npsn' => $config->npsn,
            'alamat' => $config->alamat,
            'kota' => $config->kota,
            'provinsi' => $config->provinsi,
            'koordinat' => $config->koordinat,
            'nama_kepala_sekolah' => $config->nama_kepala_sekolah,
            'nip_kepala_sekolah' => $config->nip_kepala_sekolah,
            'no_telp' => $config->no_telp,
            'kop_sekolah_url' => $config->kop_sekolah ? asset('storage/' . $config->kop_sekolah) : null,
            'watermark_url' => $config->watermark ? asset('storage/' . $config->watermark) : null,
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        try {
            $data = $request->only([
                'nama_sekolah', 'npsn', 'alamat', 'kota',
                'provinsi', 'koordinat', 'nama_kepala_sekolah',
                'nip_kepala_sekolah', 'no_telp'
            ]);

            if ($request->hasFile('kop_sekolah')) {
                $kopPath = $request->file('kop_sekolah')->store('uploads/school-config', 'public');
                $data['kop_sekolah'] = $kopPath;
            }

            if ($request->hasFile('watermark')) {
                $watermarkPath = $request->file('watermark')->store('uploads/school-config', 'public');
                $data['watermark'] = $watermarkPath;
            }

            $config = SchoolConfig::firstOrNew(); // bisa pakai id jika lebih kompleks
            $config->fill($data);
            $config->save();

            return response()->json([
                'message' => 'Konfigurasi sekolah berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan konfigurasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:kop,watermark',
            'file' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $type = $request->type;
            $filePath = $request->file('file')->store("uploads/school-config", 'public');

            $config = SchoolConfig::firstOrNew();
            $config->{$type === 'kop' ? 'kop_sekolah' : 'watermark'} = $filePath;
            $config->save();

            return response()->json([
                'message' => ucfirst($type) . ' berhasil diunggah',
                'url' => asset('storage/' . $filePath)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal upload file'], 500);
        }
    }
}