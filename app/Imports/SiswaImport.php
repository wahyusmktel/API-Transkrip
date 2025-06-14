<?php

namespace App\Imports;

use App\Models\MasterSiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\ProgramKeahlian;
use App\Models\MasterKelas;

class SiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Normalisasi input ke lowercase dan trim spasi
        $kodeProgramInput = strtolower(trim($row['program_keahlian']));
        $namaKelasInput = strtolower(trim($row['kelas']));

        // Cari program keahlian berdasarkan kode_program
        $program = ProgramKeahlian::whereRaw('LOWER(kode_program) = ?', [$kodeProgramInput])->first();

        // Cari kelas berdasarkan nama_kelas
        $kelas = MasterKelas::whereRaw('LOWER(nama_kelas) = ?', [$namaKelasInput])->first();

        // Jika salah satu tidak ditemukan, bisa skip atau bisa throw error
        if (!$program) {
            throw new \Exception("Program keahlian '{$row['program_keahlian']}' tidak ditemukan.");
        }
        if (!$kelas) {
            throw new \Exception("Kelas '{$row['kelas']}' tidak ditemukan.");
        }

        return new MasterSiswa([
            'id' => Str::uuid(),
            'nama_lengkap' => $row['nama_lengkap'],
            'tempat_lahir' => $row['tempat_lahir'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'nisn' => $row['nisn'],
            'nomor_ijazah' => $row['nomor_ijazah'],
            'program_keahlian_id' => $program->id,
            'kelas_id' => $kelas->id,
        ]);
    }
}
