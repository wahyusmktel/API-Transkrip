<?php

namespace App\Http\Controllers;

use App\Models\MasterSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TranscriptConfig;

class StatistikController extends Controller
{
    public function index()
    {
        try {
            $skala = TranscriptConfig::first()?->skala_penilaian ?? 100;
            // $precision = $skala == 100 ? 2 : 1;
            $precision = 2;

            // Rata-rata nilai & jumlah siswa per kelas
            $nilaiPerKelas = DB::table('transkrip_nilais as tn')
                ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
                ->join('master_kelas as k', 's.kelas_id', '=', 'k.id')
                ->select('k.nama_kelas as kelas', DB::raw('AVG(tn.nilai) as rataRata'), DB::raw('COUNT(DISTINCT s.id) as jumlahSiswa'))
                ->groupBy('k.nama_kelas')
                ->get()
            ->map(function ($item) use ($precision) {
                $item->rataRata = round($item->rataRata, $precision);
                return $item;
            });

            // Rata-rata semua siswa
            $rataRataKeseluruhan = round(DB::table('transkrip_nilais')->avg('nilai'), $precision);

            // Nilai tertinggi
            $nilaiTertinggi = DB::table('transkrip_nilais as tn')
                ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
                ->join('master_kelas as k', 's.kelas_id', '=', 'k.id')
                ->select('s.nama_lengkap as nama', 's.nisn', 'k.nama_kelas as kelas', DB::raw('AVG(tn.nilai) as rataRata'))
                ->groupBy('tn.siswa_id', 's.nama_lengkap', 's.nisn', 'k.nama_kelas')
                ->orderByDesc('rataRata')
                ->limit(1)
                ->first();

            if ($nilaiTertinggi) {
                $nilaiTertinggi->rataRata = round($nilaiTertinggi->rataRata, $precision);
            }

            // Nilai terendah
            $nilaiTerendah = DB::table('transkrip_nilais as tn')
                ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
                ->join('master_kelas as k', 's.kelas_id', '=', 'k.id')
                ->select('s.nama_lengkap as nama', 's.nisn', 'k.nama_kelas as kelas', DB::raw('AVG(tn.nilai) as rataRata'))
                ->groupBy('tn.siswa_id', 's.nama_lengkap', 's.nisn', 'k.nama_kelas')
                ->orderBy('rataRata')
                ->limit(1)
                ->first();

            if ($nilaiTerendah) {
                $nilaiTerendah->rataRata = round($nilaiTerendah->rataRata, $precision);
            }

            // Ranking 10 besar
            $rankingTeratas = DB::table('transkrip_nilais as tn')
                ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
                ->join('master_kelas as k', 's.kelas_id', '=', 'k.id')
                ->select('s.nama_lengkap as nama', 's.nisn', 'k.nama_kelas as kelas', DB::raw('AVG(tn.nilai) as rataRata'))
                ->groupBy('tn.siswa_id', 's.nama_lengkap', 's.nisn', 'k.nama_kelas')
                ->orderByDesc('rataRata')
                ->limit(10)
                ->get()
            ->map(function ($item) use ($precision) {
                $item->rataRata = round($item->rataRata, $precision);
                return $item;
            });

            // Ranking 10 terbawah
            $rankingTerbawah = DB::table('transkrip_nilais as tn')
                ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
                ->join('master_kelas as k', 's.kelas_id', '=', 'k.id')
                ->select('s.nama_lengkap as nama', 's.nisn', 'k.nama_kelas as kelas', DB::raw('AVG(tn.nilai) as rataRata'))
                ->groupBy('tn.siswa_id', 's.nama_lengkap', 's.nisn', 'k.nama_kelas')
                ->orderBy('rataRata')
                ->limit(10)
                ->get()
            ->map(function ($item) use ($precision) {
                $item->rataRata = round($item->rataRata, $precision);
                return $item;
            });

            return response()->json([
                'nilaiPerKelas' => $nilaiPerKelas,
                'rataRataKeseluruhan' => round($rataRataKeseluruhan, 2),
                'nilaiTertinggi' => $nilaiTertinggi,
                'nilaiTerendah' => $nilaiTerendah,
                'rankingTeratas' => $rankingTeratas,
                'rankingTerbawah' => $rankingTerbawah,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data statistik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
