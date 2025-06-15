<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MataPelajaranImport;

class MataPelajaranController extends Controller
{
    // public function index()
    // {
    //     try {
    //         return response()->json(MataPelajaran::all(), 200);
    //     } catch (\Throwable $e) {
    //         return response()->json(['message' => 'Gagal mengambil data'], 500);
    //     }
    // }

    public function index()
    {
        try {
            $data = MataPelajaran::select('id', 'nama_mata_pelajaran', 'kelompok')->get();
            return response()->json(['data' => $data], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_mata_pelajaran' => 'required|string|max:255',
                'kelompok' => 'required|in:Umum,Produktif,Adaptif',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $mataPelajaran = MataPelajaran::create($validator->validated());

            return response()->json(['message' => 'Berhasil menambahkan', 'data' => $mataPelajaran], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menyimpan data'], 500);
        }
    }

    public function show($id)
    {
        try {
            $mapel = MataPelajaran::findOrFail($id);
            return response()->json($mapel);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $mapel = MataPelajaran::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_mata_pelajaran' => 'required|string|max:255',
                'kelompok' => 'required|in:Umum,Produktif,Adaptif',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $mapel->update($validator->validated());

            return response()->json(['message' => 'Berhasil diupdate', 'data' => $mapel]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal mengupdate data'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mapel = MataPelajaran::findOrFail($id);
            $mapel->delete();

            return response()->json(['message' => 'Berhasil dihapus']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus data'], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            Excel::import(new MataPelajaranImport, $request->file('file'));

            // Ambil data terbaru berdasarkan created_at DESC, atau seluruh data jika aman
            $imported = MataPelajaran::latest()->take(10)->get(); // <= bisa disesuaikan

            return response()->json([
                'message' => 'Data mata pelajaran berhasil diimport.',
                'data' => $imported
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimport data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
