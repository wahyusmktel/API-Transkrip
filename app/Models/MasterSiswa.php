<?php

// app/Models/MasterSiswa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MasterSiswa extends Model
{
    use HasFactory;

    protected $table = 'master_siswas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'nisn',
        'nomor_ijazah',
        'no_transkrip',
        'pdf_transkrip_filename',
        'program_keahlian_id',
        'kelas_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function programKeahlian()
    {
        return $this->belongsTo(ProgramKeahlian::class, 'program_keahlian_id');
    }

    public function kelas()
    {
        return $this->belongsTo(MasterKelas::class, 'kelas_id');
    }

    public function transkripNilai()
    {
        return $this->hasMany(TranskripNilai::class, 'siswa_id');
    }
}
