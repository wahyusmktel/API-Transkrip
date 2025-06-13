<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterKelas extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'master_kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'id_program',
        'wali_kelas',
        'jumlah_siswa',
        'tahun_ajaran',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid()->toString();
        });
    }

    public function program()
    {
        return $this->belongsTo(ProgramKeahlian::class, 'id_program');
    }
}
