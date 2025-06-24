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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class TranskripNilaiController extends Controller
{

    public function index()
    {
        try {
            $data = TranskripNilai::with(['siswa:id,nama_lengkap,nisn,program_keahlian_id,tempat_lahir,tanggal_lahir,nomor_ijazah,no_transkrip', 'siswa.programKeahlian:id,nama_program,nama_konsentrasi', 'mapel:id,nama_mata_pelajaran,kelompok'])
                ->get()
                ->groupBy('siswa_id')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'id' => $first->siswa_id,
                        'nama_siswa' => $first->siswa->nama_lengkap,
                        'nisn' => $first->siswa->nisn,
                        'program_keahlian' => $first->siswa->programKeahlian->nama_program ?? '-',
                        'konsentrasi_keahlian' => $first->siswa->programKeahlian->nama_konsentrasi ?? '-',
                        'tempat_lahir' => $first->siswa->tempat_lahir ?? '-',
                        'tanggal_lahir' => $first->siswa->tanggal_lahir ?? '-',
                        'no_ijazah' => $first->siswa->nomor_ijazah ?? '-',
                        'no_transkrip' => $first->siswa->no_transkrip ?? '-',
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

    // public function generatePdf($siswaId)
    // {
    //     $siswa = MasterSiswa::with('transkripNilai.mapel')->findOrFail($siswaId);
    //     $schoolConfig = SchoolConfig::first();
    //     $transcriptConfig = TranscriptConfig::first();

    //     $pdf = Pdf::loadView('pdf.transkrip', [
    //         'siswa' => $siswa,
    //         'schoolConfig' => $schoolConfig,
    //         'transcriptConfig' => $transcriptConfig,
    //     ])->setPaper('a4', 'portrait');

    //     return $pdf->download("Transkrip_{$siswa->nama_lengkap}.pdf");
    // }

    public function generateAndStorePdf($siswaId)
    {
        $siswa = MasterSiswa::with('transkripNilai.mapel')->findOrFail($siswaId);
        $schoolConfig = SchoolConfig::first();
        $transcriptConfig = TranscriptConfig::first();

        $groupedNilai = collect($siswa->transkripNilai)
            ->sortBy(function ($item) {
                return $item->mapel->urutan_mapel ?? 9999;
            })
            ->groupBy(function ($item) {
                // Muatan lokal tetap digabung ke kelompok aslinya
                return $item->mapel->kelompok ?? 'Lainnya';
            });

        $filename = "transkrip_{$siswa->id}.pdf";
        $path = "public/transkrip/{$filename}";

        // === Convert watermark image to base64 ===
        $watermarkBase64 = null;
        if ($schoolConfig && $schoolConfig->watermark) {
            if (Storage::disk('public')->exists($schoolConfig->watermark)) {
                $watermarkPath = Storage::disk('public')->path($schoolConfig->watermark);
                $watermarkBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath));
                Log::info('Base64 Watermark generated successfully.');
            } else {
                $watermarkBase64 = null;
                Log::warning('Watermark file not found: ' . $schoolConfig->watermark);
            }
        }

        // === Convert kop sekolah image to base64 ===
        $kopBase64 = null;
        if ($schoolConfig && $schoolConfig->kop_sekolah) {
            if (Storage::disk('public')->exists($schoolConfig->kop_sekolah)) {
                $kopPath = Storage::disk('public')->path($schoolConfig->kop_sekolah);
                $kopBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath));
            } else {
                Log::warning('Kop Sekolah file not found: ' . $schoolConfig->kop_sekolah);
            }
        }

        Log::info('Base64 Watermark:', [$watermarkBase64]);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.transkrip', [
            'siswa' => $siswa,
            'schoolConfig' => $schoolConfig,
            'transcriptConfig' => $transcriptConfig,
            'watermarkBase64' => $watermarkBase64,
            'kopBase64' => $kopBase64,
            'groupedNilai' => $groupedNilai,
        ])->setPaper('a4', 'portrait');

        // Simpan file ke storage
        Storage::put($path, $pdf->output());

        // Update kolom pdf_transkrip_filename di tabel siswa
        $siswa->pdf_transkrip_filename = $filename;
        $siswa->save();

        return response()->json([
            'message' => 'PDF berhasil digenerate, disimpan, dan diperbarui ke database.',
            'filename' => $filename,
        ]);
    }

    public function downloadStoredPdf($siswaId)
    {
        $siswa = MasterSiswa::findOrFail($siswaId);

        // Cek apakah file PDF sudah pernah digenerate dan disimpan di DB
        if (!$siswa->pdf_transkrip_filename) {
            return response()->json([
                'message' => 'PDF belum digenerate. Silakan generate terlebih dahulu.'
            ], Response::HTTP_NOT_FOUND);
        }

        $filename = $siswa->pdf_transkrip_filename;
        $path = storage_path("app/private/public/transkrip/{$filename}");

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'File PDF tidak ditemukan di storage. Silakan generate ulang.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function cetakMasal(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 detik (5 menit)
        Log::info("Memulai proses cetak masal...");

        $siswas = MasterSiswa::with('transkripNilai.mapel')->get();
        Log::info("Jumlah siswa yang akan diproses: " . $siswas->count());

        $schoolConfig = SchoolConfig::first();
        $transcriptConfig = TranscriptConfig::first();

        $zipFilename = 'transkrip_masal.zip';
        $zipPath = storage_path("app/public/transkrip/{$zipFilename}");
        Log::info("Lokasi file ZIP: " . $zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($siswas as $siswa) {
                try {
                    $groupedNilai = collect($siswa->transkripNilai)
                        ->sortBy(fn($item) => $item->mapel->urutan_mapel ?? 9999)
                        ->groupBy(fn($item) => $item->mapel->kelompok ?? 'Lainnya');

                    Log::info("Sedang membuat PDF untuk: " . $siswa->nama_lengkap);

                    // === Convert watermark image to base64 ===
                    $watermarkBase64 = null;
                    if ($schoolConfig && $schoolConfig->watermark) {
                        if (Storage::disk('public')->exists($schoolConfig->watermark)) {
                            $watermarkPath = Storage::disk('public')->path($schoolConfig->watermark);
                            $watermarkBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath));
                            Log::info('Base64 Watermark generated successfully.');
                        } else {
                            Log::warning('Watermark file not found: ' . $schoolConfig->watermark);
                        }
                    }

                    // === Convert kop sekolah image to base64 ===
                    $kopBase64 = null;
                    if ($schoolConfig && $schoolConfig->kop_sekolah) {
                        if (Storage::disk('public')->exists($schoolConfig->kop_sekolah)) {
                            $kopPath = Storage::disk('public')->path($schoolConfig->kop_sekolah);
                            $kopBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath));
                            Log::info('Base64 Kop Sekolah generated successfully.');
                        } else {
                            Log::warning('Kop Sekolah file not found: ' . $schoolConfig->kop_sekolah);
                        }
                    }

                    $pdf = Pdf::loadView('pdf.transkrip', [
                        'siswa' => $siswa,
                        'schoolConfig' => $schoolConfig,
                        'transcriptConfig' => $transcriptConfig,
                        'watermarkBase64' => $watermarkBase64,
                        'kopBase64' => $kopBase64,
                        'groupedNilai' => $groupedNilai,
                    ])->setPaper('a4', 'portrait');

                    $pdfContent = $pdf->output();

                    $pdfFileName = 'Transkrip_' . preg_replace('/[^A-Za-z0-9]/', '_', $siswa->nama_lengkap) . '.pdf';
                    Log::info("Menambahkan ke ZIP: " . $pdfFileName);

                    $zip->addFromString($pdfFileName, $pdfContent);
                } catch (\Throwable $e) {
                    Log::error("Gagal membuat PDF untuk " . $siswa->nama_lengkap . ": " . $e->getMessage());
                }
            }

            $zip->close();
            Log::info("ZIP file berhasil dibuat.");

            // return response()->download($zipPath)->deleteFileAfterSend(true);
            return response()->json([
                'message' => 'ZIP file berhasil dibuat.',
                'file' => asset('storage/transkrip/' . $zipFilename), // jika kamu ingin share URL file-nya
                'filename' => $zipFilename,
            ]);
        } else {
            Log::error("Gagal membuka ZIP archive.");
            return response()->json(['message' => 'Gagal membuat ZIP file.'], 500);
        }
    }

}
