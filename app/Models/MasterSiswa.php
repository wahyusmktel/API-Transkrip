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
        'program_keahlian',
        'kelas',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
