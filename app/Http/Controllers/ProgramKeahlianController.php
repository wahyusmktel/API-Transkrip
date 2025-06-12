<?php

namespace App\Http\Controllers;

use App\Models\ProgramKeahlian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramKeahlianController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Data ditemukan',
            'data' => ProgramKeahlian::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_program' => 'required|string|unique:program_keahlians,kode_program',
            'nama_program' => 'required|string',
            'nama_konsentrasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $program = ProgramKeahlian::create($validator->validated());

            return response()->json([
                'message' => 'Program berhasil ditambahkan',
                'data' => $program
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $program = ProgramKeahlian::find($id);

        if (!$program) {
            return response()->json(['message' => 'Program tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_program' => 'required|string|unique:program_keahlians,kode_program,' . $id,
            'nama_program' => 'required|string',
            'nama_konsentrasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $program->update($validator->validated());

            return response()->json([
                'message' => 'Program berhasil diperbarui',
                'data' => $program
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui data'], 500);
        }
    }

    public function destroy($id)
    {
        $program = ProgramKeahlian::find($id);

        if (!$program) {
            return response()->json(['message' => 'Program tidak ditemukan'], 404);
        }

        try {
            $program->delete();
            return response()->json(['message' => 'Program berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus data'], 500);
        }
    }
}
