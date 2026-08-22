<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id', 'ujian_id', 'soal_id', 'jawaban', 'is_correct'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }

    public function aiKoreksi()
    {
        return $this->hasOne(AiKoreksiEssay::class, 'jawaban_siswa_id')->latestOfMany();
    }

    public function aiKoreksis()
    {
        return $this->hasMany(AiKoreksiEssay::class, 'jawaban_siswa_id');
    }
}
