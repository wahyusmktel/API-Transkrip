<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MasterKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MasterKelasController extends Controller
{
    public function index()
    {
        return response()->json(
            MasterKelas::with('program')->get()
        );
    }

    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'nama_kelas' => 'required|string|max:100',
                'tingkat' => 'required|string|max:10',
                'id_program' => 'required|uuid',
                'wali_kelas' => 'required|string|max:100',
                'jumlah_siswa' => 'required|integer|min:0',
                'tahun_ajaran' => 'required|string|max:20',
            ]);

            if ($validated->fails()) {
                return response()->json(['message' => 'Validasi gagal', 'errors' => $validated->errors()], 422);
            }

            $kelas = MasterKelas::create($request->all());
            return response()->json(['message' => 'Berhasil disimpan', 'data' => $kelas]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data'], 500);
        }
    }

    public function show($id)
    {
        $kelas = MasterKelas::findOrFail($id);
        return response()->json($kelas);
    }

    public function update(Request $request, $id)
    {
        try {
            $kelas = MasterKelas::findOrFail($id);
            $kelas->update($request->all());
            return response()->json(['message' => 'Berhasil diperbarui', 'data' => $kelas]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui data'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kelas = MasterKelas::findOrFail($id);
            $kelas->delete();
            return response()->json(['message' => 'Berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus data'], 500);
        }
    }
}
