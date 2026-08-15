<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id', 'tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban_benar', 'bobot', 'urutan'
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function jawabanSiswas()
    {
        return $this->hasMany(JawabanSiswa::class);
    }

    public function isPg()
    {
        return $this->tipe === 'pg';
    }

    public function isEssay()
    {
        return $this->tipe === 'essay';
    }
}
