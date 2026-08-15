<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id', 'ujian_id', 'nilai_pg', 'nilai_essay', 'nilai_akhir',
        'status', 'started_at', 'finished_at', 'tab_switch_count'
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function isMengerjakan()
    {
        return $this->status === 'mengerjakan';
    }

    public function isSelesai()
    {
        return $this->status === 'selesai';
    }

    public function isDinilai()
    {
        return $this->status === 'dinilai';
    }
}
