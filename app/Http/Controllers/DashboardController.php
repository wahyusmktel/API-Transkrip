<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterSiswa;
use App\Models\ProgramKeahlian;
use App\Models\TranskripNilai;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = MasterSiswa::count();
        $totalProgram = ProgramKeahlian::count();
        $totalTranskrip = TranskripNilai::distinct('siswa_id')->count('siswa_id');
        $totalMapel = MataPelajaran::count();

        $top5Siswa = DB::table('transkrip_nilais as tn')
            ->join('master_siswas as s', 'tn.siswa_id', '=', 's.id')
            ->leftJoin('program_keahlians as pk', 's.program_keahlian_id', '=', 'pk.id')
            ->select(
                's.nama_lengkap as name',
                DB::raw('COALESCE(pk.nama_program, "-") as program'),
                DB::raw('AVG(tn.nilai) as average')
            )
            ->groupBy('tn.siswa_id', 's.nama_lengkap', 'pk.nama_program')
            ->orderByDesc('average')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'totalSiswa' => $totalSiswa,
                'totalProgram' => $totalProgram,
                'totalTranskrip' => $totalTranskrip,
                'totalMapel' => $totalMapel,
            ],
            'topPerformers' => $top5Siswa
        ]);
    }
}