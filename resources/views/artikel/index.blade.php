@extends('layouts.public')

@section('title', 'Semua Artikel & Informasi')

@section('content')
<div class="bg-slate-50 py-12 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                Artikel & Informasi Terbaru
            </h1>
            <p class="text-slate-500 text-base sm:text-lg">
                Kumpulan kabar, wawasan, dan pengumuman seputar kegiatan pembelajaran.
            </p>
        </div>

        @if(isset($artikels) && $artikels->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($artikels as $artikel)
            <a href="{{ route('artikel.show', $artikel->slug) }}" class="group bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col h-full cursor-pointer decoration-none">
                
                <div class="w-full h-48 overflow-hidden bg-slate-100 relative">
                    @if($artikel->gambar && is_array($artikel->gambar) && count($artikel->gambar) > 0)
                        <img src="{{ asset('uploads/artikel/' . $artikel->gambar[0]) }}" alt="{{ $artikel->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=600&q=80" alt="{{ $artikel->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif
                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-md text-brand text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                        Artikel
                    </div>
                </div>
                
                <div class="p-6 flex flex-col flex-grow justify-between">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 mb-2 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $artikel->created_at->translatedFormat('d M Y') }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3 group-hover:text-brand transition-colors line-clamp-2 leading-snug">
                            {{ $artikel->judul }}
                        </h3>
                        <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit(strip_tags($artikel->konten), 120) }}
                        </p>
                    </div>
                    
                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center text-sm font-bold text-brand group-hover:translate-x-1 transition-transform">
                        <span>Baca Selengkapnya</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-12 flex justify-center">
            {{ $artikels->links() }}
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-3xl border border-slate-100 max-w-xl mx-auto shadow-sm">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Artikel</h3>
            <p class="text-slate-500 text-sm">Artikel dan informasi terbaru akan ditampilkan di halaman ini.</p>
        </div>
        @endif

    </div>
</div>
@endsection
