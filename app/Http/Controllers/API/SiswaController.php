<?php

// app/Http/Controllers/Api/SiswaController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    // public function index()
    // {
    //     try {
    //         $siswas = MasterSiswa::latest()->get();
    //         return response()->json(['data' => $siswas]);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Gagal mengambil data', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function index()
    {
        try {
            $siswas = MasterSiswa::with(['programKeahlian', 'kelas'])->latest()->get();

            return response()->json(['data' => $siswas]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'nisn' => 'required|string|max:20|unique:master_siswas',
                'nomor_ijazah' => 'nullable|string|max:100',
                'program_keahlian_id' => 'required|exists:program_keahlians,id',
                'kelas_id' => 'required|exists:master_kelas,id',
                'no_transkrip' => 'nullable|string|max:100',
            ])->validate();

            $siswa = MasterSiswa::create($validated);
            return response()->json(['message' => 'Data siswa berhasil ditambahkan', 'data' => $siswa]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambah data', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $siswa = MasterSiswa::findOrFail($id);
            return response()->json(['data' => $siswa]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Data tidak ditemukan', 'error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $siswa = MasterSiswa::findOrFail($id);

            $validated = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'nisn' => 'required|string|max:20|unique:master_siswas,nisn,' . $id,
                'nomor_ijazah' => 'nullable|string|max:100',
                'program_keahlian_id' => 'required|exists:program_keahlians,id',
                'kelas_id' => 'required|exists:master_kelas,id',
                'no_transkrip' => 'nullable|string|max:100',
            ])->validate();

            $siswa->update($validated);

            return response()->json(['message' => 'Data siswa berhasil diperbarui', 'data' => $siswa]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $siswa = MasterSiswa::findOrFail($id);
            $siswa->delete();
            return response()->json(['message' => 'Data siswa berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus data', 'error' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
            return response()->json(['message' => 'Import berhasil.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }
}
