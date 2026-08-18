@extends('layouts.public')
@section('title', 'Beranda - Kabar Terbaru dari Kelas')
@section('content')

<!-- Section Artikel & Informasi (Bagian Atas Landing Page) -->
<section id="artikel" class="py-12 md:py-16 bg-white border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                Kabar Terbaru dari Kelas
            </h1>
            <p class="mt-4 text-base text-slate-500 sm:text-lg">
                Dapatkan wawasan, pengumuman, serta materi terbaru seputar kegiatan pembelajaran.
            </p>
        </div>

        @if(isset($artikels) && $artikels->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($artikels as $artikel)
            <a href="{{ route('artikel.show', $artikel->slug) }}" class="group bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col h-full cursor-pointer decoration-none">
                
                <!-- Image / Thumbnail -->
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
                
                <!-- Content -->
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
        @else
        <div class="text-center py-12 bg-slate-50 rounded-2xl border border-slate-100 max-w-xl mx-auto">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <p class="text-slate-500 font-medium text-sm">Belum ada artikel atau kabar terbaru yang dipublikasikan.</p>
        </div>
        @endif
    </div>
</section>

<!-- Stats Section -->
<div class="bg-brand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="bg-white/10 rounded-xl p-6 backdrop-blur-sm border border-white/20">
                <div class="text-4xl font-extrabold text-white mb-2">{{ $stats['siswa'] ?? 0 }}<span class="text-blue-200">+</span></div>
                <div class="text-blue-100 font-medium">Siswa Terdaftar</div>
            </div>
            <div class="bg-white/10 rounded-xl p-6 backdrop-blur-sm border border-white/20">
                <div class="text-4xl font-extrabold text-white mb-2">{{ $stats['kelas'] ?? 0 }}</div>
                <div class="text-blue-100 font-medium">Kelas Aktif</div>
            </div>
            <div class="bg-white/10 rounded-xl p-6 backdrop-blur-sm border border-white/20">
                <div class="text-4xl font-extrabold text-white mb-2">{{ $stats['materi'] ?? 0 }}<span class="text-blue-200">+</span></div>
                <div class="text-blue-100 font-medium">Materi Pembelajaran</div>
            </div>
        </div>
    </div>
</div>



<!-- Testimonials -->
@if(isset($komentars) && $komentars->count() > 0)
<style>
    @keyframes scrollMarquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marquee-track {
        display: flex;
        width: max-content;
        animation: scrollMarquee {{ max($komentars->count() * 4, 15) }}s linear infinite;
    }
    .marquee-container:hover .marquee-track {
        animation-play-state: paused;
    }
    .fade-edges {
        mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
    }
</style>

<section class="py-20 bg-white border-t border-slate-100 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        <div class="text-center">
            <h2 class="text-base font-bold text-brand tracking-wide uppercase">Testimoni Pengguna</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Suara dari Kelas Kita
            </p>
        </div>
    </div>
    
    <div class="marquee-container w-full overflow-hidden fade-edges py-4">
        <div class="marquee-track gap-6 px-4">
            {{-- Loop dua kali untuk efek infinite marquee --}}
            @for ($i = 0; $i < 2; $i++)
                @foreach($komentars as $komentar)
                <div class="w-80 sm:w-96 flex-shrink-0 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between transform transition hover:-translate-y-1 hover:shadow-md">
                    <div>
                        <svg class="w-8 h-8 text-blue-200 mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                        <p class="text-slate-700 italic mb-6 leading-relaxed line-clamp-4">"{{ $komentar->isi_komentar }}"</p>
                    </div>
                    <div class="flex items-center gap-4 mt-auto">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl border-2 border-white shadow-sm overflow-hidden flex-shrink-0">
                            @if(!$komentar->is_anonim && isset($komentar->siswa->user))
                                <img src="{{ $komentar->siswa->user->avatar_url }}" class="w-full h-full object-cover" alt="{{ $komentar->siswa->user->name }}">
                            @else
                                {{ $komentar->is_anonim ? 'A' : substr($komentar->siswa->user->name ?? 'A', 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-900">{{ $komentar->is_anonim ? 'Siswa Anonim' : ($komentar->siswa->user->name ?? 'Anonim') }}</p>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($komentar->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @endfor
        </div>
    </div>
</section>
@endif

<!-- Final CTA Section -->
<section class="bg-slate-900 relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-blue-600 mix-blend-multiply opacity-20"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24 relative z-10 text-center">
        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
            <span class="block">Siap untuk memulai?</span>
            <span class="block text-blue-400 mt-2">Akses dashboard Anda sekarang.</span>
        </h2>
        <p class="mt-4 text-lg leading-6 text-slate-300">
            Guru dapat mulai mengelola kelas, dan siswa dapat melihat perkembangan akademiknya dalam hitungan detik.
        </p>
        <div class="mt-8 flex justify-center">
            <a href="{{ url('/login') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-bold rounded-lg text-slate-900 bg-white hover:bg-slate-100 shadow-lg shadow-white/10 transition-all hover:-translate-y-0.5">
                Login ke Sistem
                <svg class="ml-2 w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </div>
</section>

@endsection
