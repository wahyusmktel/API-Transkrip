<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TranskripNilai extends Model
{
    use HasUuids;

    protected $fillable = [
        'siswa_id',
        'mapel_id',
        'nilai',
        'kelompok',
    ];

    public function siswa()
    {
        return $this->belongsTo(MasterSiswa::class);
    }

    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }
}
