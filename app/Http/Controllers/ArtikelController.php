<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artikel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ArtikelController extends Controller
{
    /**
     * Menampilkan daftar semua artikel.
     */
    public function index()
    {
        $artikels = Artikel::latest()->paginate(9);
        return view('artikel.index', compact('artikels'));
    }

    /**
     * Menampilkan detail artikel berdasarkan slug.
     */
    public function show($slug)
    {
        $artikel = null;

        // 1. Jika kolom 'slug' sudah ada di database, cari langsung
        if (Schema::hasColumn('artikels', 'slug')) {
            $artikel = Artikel::where('slug', $slug)->first();
        }

        // 2. Jika pencarian menggunakan ID (angka)
        if (!$artikel && is_numeric($slug)) {
            $artikel = Artikel::find($slug);
        }

        // 3. Jika artikel tetap tidak ditemukan
        if (!$artikel) {
            abort(404, 'Artikel tidak ditemukan');
        }

        // Ambil rekomendasi artikel lainnya
        $artikelLainnya = Artikel::where('id', '!=', $artikel->id)
            ->latest()
            ->take(3)
            ->get();

        return view('artikel.detail', compact('artikel', 'artikelLainnya'));
    }
}
