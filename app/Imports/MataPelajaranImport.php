<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Ramsey\Uuid\Uuid;

class MataPelajaranImport implements ToModel
{
    public function model(array $row)
    {
        return new MataPelajaran([
            'id' => Uuid::uuid4()->toString(),
            'nama_mata_pelajaran' => $row[0],
            'kelompok' => $row[1],
        ]);
    }
}
