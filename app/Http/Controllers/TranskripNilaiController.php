<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TranskripNilai;
use Illuminate\Support\Facades\Validator;
use App\Models\MasterSiswa;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\MataPelajaran;
use App\Models\SchoolConfig;
use App\Models\TranscriptConfig;

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
            $data = TranskripNilai::with(['siswa:id,nama_lengkap,nisn,program_keahlian_id,tempat_lahir,tanggal_lahir,nomor_ijazah', 'siswa.programKeahlian:id,nama_program', 'mapel:id,nama_mata_pelajaran,kelompok'])
                ->get()
                ->groupBy('siswa_id')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'id' => $first->siswa_id,
                        'nama_siswa' => $first->siswa->nama_lengkap,
                        'nisn' => $first->siswa->nisn,
                        'program_keahlian' => $first->siswa->programKeahlian->nama_program ?? '-',
                        'tempat_lahir' => $first->siswa->tempat_lahir ?? '-',
                        'tanggal_lahir' => $first->siswa->tanggal_lahir ?? '-',
                        'no_ijazah' => $first->siswa->nomor_ijazah ?? '-',
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

            // Ambil konfigurasi tambahan
            $schoolConfig = SchoolConfig::first();
            $transcriptConfig = TranscriptConfig::first();

            return response()->json([
                'data' => $data,
                'school_config' => $schoolConfig,
                'transcript_config' => $transcriptConfig,
            ], 200);

            // return response()->json(['data' => $data], 200);
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

    public function import(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls|max:5120',
            ])->validate();

            $file = $request->file('file');
            $data = Excel::toArray([], $file)[0]; // Ambil sheet pertama

            if (count($data) < 2) {
                return response()->json(['message' => 'File kosong atau format tidak valid.'], 422);
            }

            $header = $data[0];
            $rows = array_slice($data, 1);

            foreach ($rows as $row) {
                $nisn = $row[2] ?? null;

                if (!$nisn) continue;

                $siswa = MasterSiswa::where('nisn', $nisn)->first();
                if (!$siswa) continue;

                for ($i = 3; $i < count($header); $i++) {
                    $namaMapel = $header[$i];
                    $nilai = $row[$i] ?? null;

                    if ($nilai === null || $nilai === '') continue;

                    $mapel = MataPelajaran::where('nama_mata_pelajaran', $namaMapel)->first();
                    if (!$mapel) continue;

                    TranskripNilai::updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'mapel_id' => $mapel->id,
                        ],
                        [
                            'nilai' => floatval($nilai),
                        ]
                    );
                }
            }

            return response()->json(['message' => 'Import data berhasil.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
