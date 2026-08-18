<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'isi_komentar',
        'is_anonim',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
