<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jurnal_mengajars';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'tanggal',
        'pertemuan',
        'materi',
        'tujuan_pembelajaran',
        'kegiatan',
        'catatan',
        'tindak_lanjut'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
