<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'guru_id',
        'judul',
        'deskripsi',
        'tipe_target',
        'kelas_id',
        'siswa_id',
        'deadline',
        'file_tugas',
        'link',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function getFileUrlAttribute()
    {
        if (!$this->file_tugas) {
            return null;
        }
        return \App\Services\FileStorageService::url($this->file_tugas, 'tugas');
    }
}
