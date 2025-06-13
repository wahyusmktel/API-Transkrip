<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramKeahlian extends Model
{
    protected $table = 'program_keahlians';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'kode_program', 'nama_program', 'nama_konsentrasi'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function kelas()
    {
        return $this->hasMany(MasterKelas::class, 'id_program');
    }
}
