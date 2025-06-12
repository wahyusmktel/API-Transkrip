<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranscriptConfig extends Model
{
    protected $table = 'transcript_configs';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tanggal_kelulusan', 'tanggal_transkrip', 'skala_penilaian'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}