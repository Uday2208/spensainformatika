<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogUjian extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'siswa_id', 'ujian_id', 'event', 'detail', 'created_at'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
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
}
