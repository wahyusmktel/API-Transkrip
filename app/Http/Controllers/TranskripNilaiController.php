<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TranskripNilai;
use Illuminate\Support\Facades\Validator;
use App\Models\MasterSiswa;

class TranskripNilaiController extends Controller
{
    // public function index()
    // {
    //     try {
    //         $data = TranskripNilai::with(['siswa:id,nama_lengkap,nisn', 'mapel:id,nama_mata_pelajaran'])->latest()->get();
    //         return response()->json(['data' => $data]);
    //     } catch (\Throwable $e) {
    //         return response()->json(['message' => 'Gagal mengambil data', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function index()
    {
        try {
            $data = TranskripNilai::with(['siswa:id,nama_lengkap,nisn,program_keahlian_id', 'siswa.programKeahlian:id,nama_program', 'mapel:id,nama_mata_pelajaran,kelompok'])
                ->get()
                ->groupBy('siswa_id')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'id' => $first->siswa_id,
                        'nama_siswa' => $first->siswa->nama_lengkap,
                        'nisn' => $first->siswa->nisn,
                        'program_keahlian' => $first->siswa->programKeahlian->nama_program ?? '-',
                        'nilai' => $items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'mata_pelajaran_id' => $item->mapel_id,
                                'mata_pelajaran' => $item->mapel->nama_mata_pelajaran,
                                'kelompok' => $item->mapel->kelompok,
                                'nilai' => $item->nilai,
                            ];
                        })->values(),
                        'rata_rata' => round($items->avg('nilai'), 2),
                    ];
                })
                ->values(); // convert Collection to array

            return response()->json(['data' => $data], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function siswaBelumAdaNilai()
    {
        try {
            $siswaSudahDinilai = TranskripNilai::select('siswa_id')->distinct()->pluck('siswa_id');

            $data = MasterSiswa::whereNotIn('id', $siswaSudahDinilai)
                ->select('id', 'nama_lengkap', 'nisn')
                ->get();

            return response()->json(['data' => $data], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'siswa_id' => 'required|uuid|exists:master_siswas,id',
            'nilai' => 'required|array|min:1',
            'nilai.*.mata_pelajaran_id' => 'required|uuid|exists:mata_pelajarans,id',
            'nilai.*.nilai' => 'required|numeric|min:0|max:100',
        ])->validate();

        try {
            $result = [];

            foreach ($validated['nilai'] as $item) {
                $transkrip = TranskripNilai::create([
                    'siswa_id' => $validated['siswa_id'],
                    'mapel_id' => $item['mata_pelajaran_id'],
                    'nilai' => $item['nilai'],
                ]);

                $result[] = $transkrip;
            }

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menyimpan data', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'siswa_id' => 'required|uuid|exists:master_siswas,id',
            'nilai' => 'required|array|min:1',
            'nilai.*.id' => 'nullable|uuid|exists:transkrip_nilais,id',
            'nilai.*.mata_pelajaran_id' => 'required|uuid|exists:mata_pelajarans,id',
            'nilai.*.nilai' => 'required|numeric|min:0|max:100',
        ])->validate();

        try {
            foreach ($validated['nilai'] as $nilaiItem) {
                // Update jika ada ID, jika tidak ada → bisa skip atau insert baru (optional)
                if (!empty($nilaiItem['id'])) {
                    $transkrip = TranskripNilai::find($nilaiItem['id']);
                    if ($transkrip) {
                        $transkrip->update([
                            'siswa_id' => $validated['siswa_id'],
                            'mapel_id' => $nilaiItem['mata_pelajaran_id'],
                            'nilai' => $nilaiItem['nilai'],
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Data berhasil diperbarui']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memperbarui data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Hapus semua transkrip milik siswa dengan ID tersebut
            TranskripNilai::where('siswa_id', $id)->delete();

            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus data', 'error' => $e->getMessage()], 500);
        }
    }
}
