<?php

namespace App\Imports;

use App\Models\MasterSiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MasterSiswa([
            'id' => Str::uuid(),
            'nama_lengkap' => $row['nama_lengkap'],
            'tempat_lahir' => $row['tempat_lahir'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'nisn' => $row['nisn'],
            'nomor_ijazah' => $row['nomor_ijazah'],
            'program_keahlian' => $row['program_keahlian'],
            'kelas' => $row['kelas'],
        ]);
    }
}
