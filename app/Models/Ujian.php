<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul', 'bab', 'tanggal', 'durasi', 'status', 'token', 'token_expired_at'
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'token_expired_at' => 'datetime',
        ];
    }

    public function kelasList()
    {
        return $this->belongsToMany(Kelas::class, 'ujian_kelas');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class)->orderBy('urutan');
    }

    public function hasilUjians()
    {
        return $this->hasMany(HasilUjian::class);
    }

    public function logUjians()
    {
        return $this->hasMany(LogUjian::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isAktif()
    {
        return $this->status === 'aktif';
    }

    public function isSelesai()
    {
        return $this->status === 'selesai';
    }

    public function generateToken()
    {
        $this->token = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        $this->save();
        return $this->token;
    }

    public function totalBobot()
    {
        return $this->soals()->sum('bobot');
    }

    public function soalPg()
    {
        return $this->soals()->where('tipe', 'pg');
    }

    public function soalEssay()
    {
        return $this->soals()->where('tipe', 'essay');
    }
}
