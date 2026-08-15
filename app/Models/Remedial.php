<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remedial extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function nilai()
    {
        return $this->belongsTo(Nilai::class);
    }
}
