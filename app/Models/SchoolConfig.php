<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SchoolConfig extends Model {
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'nama_sekolah', 'npsn', 'alamat', 'kota', 'provinsi',
        'koordinat', 'nama_kepala_sekolah', 'nip_kepala_sekolah',
        'no_telp', 'kop_sekolah', 'watermark'
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid()->toString();
        });
    }
}