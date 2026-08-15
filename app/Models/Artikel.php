<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Artikel extends Model
{
    use HasFactory;

    protected $fillable = ['judul', 'slug', 'konten', 'gambar'];

    protected $casts = [
        'gambar' => 'array',
    ];

    /**
     * Auto generate slug sebelum menyimpan ke database jika kolom slug ada di DB.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($artikel) {
            if (Schema::hasColumn('artikels', 'slug')) {
                if (empty($artikel->slug)) {
                    $slug = Str::slug($artikel->judul);
                    $originalSlug = $slug;
                    $count = 1;
                    while (static::where('slug', $slug)->where('id', '!=', $artikel->id ?? 0)->exists()) {
                        $slug = "{$originalSlug}-" . $count++;
                    }
                    $artikel->slug = $slug ?: 'artikel-' . time();
                }
            }
        });
    }

    /**
     * Accessor fallback untuk slug jika kolom slug di DB bernilai null.
     */
    public function getSlugAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return Str::slug($this->judul) ?: 'artikel-' . $this->id;
    }
}
