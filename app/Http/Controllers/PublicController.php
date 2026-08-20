<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komentar;
use App\Models\Setting;

class PublicController extends Controller
{
    public function index()
    {
        $limitSetting = Setting::where('key', 'komentar_homepage_limit')->first();
        $limit = $limitSetting ? (int)$limitSetting->value : 50;

        $komentars = Komentar::with('siswa.user')
            ->latest()
            ->take($limit)
            ->get();
            
        // Statistik
        $stats = [
            'siswa' => \App\Models\Siswa::count(),
            'kelas' => \App\Models\Kelas::count(),
            'materi' => \App\Models\Materi::count(),
        ];

        // Artikel
        $artikels = \App\Models\Artikel::latest()->get();

        return view('home', compact('komentars', 'stats', 'artikels'));
    }
}
