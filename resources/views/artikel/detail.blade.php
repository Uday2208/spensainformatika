@extends('layouts.public')

@section('title', $artikel->judul . ' - Artikel')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Back Button -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-brand transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Beranda
            </a>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                Portal Artikel
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Main Article Content (8 columns) -->
            <article class="lg:col-span-8 bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden p-6 sm:p-8 lg:p-10">
                
                <!-- Category / Tag & Date -->
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500 mb-4">
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-brand border border-blue-100 uppercase tracking-wider">
                        Informasi
                    </span>
                    <span class="flex items-center text-slate-400">
                        <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $artikel->created_at->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                <!-- Judul Artikel -->
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-snug tracking-tight mb-6">
                    {{ $artikel->judul }}
                </h1>

                <!-- Gambar Utama / Gallery -->
                @if($artikel->gambar && is_array($artikel->gambar) && count($artikel->gambar) > 0)
                    @if(count($artikel->gambar) == 1)
                        <div class="w-full rounded-2xl overflow-hidden mb-8 shadow-md max-h-[450px]">
                            <img src="{{ asset('uploads/artikel/' . $artikel->gambar[0]) }}" alt="{{ $artikel->judul }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <!-- Carousel Gambar -->
                        <div x-data="{ activeSlide: 0, slides: {{ count($artikel->gambar) }} }" class="relative w-full rounded-2xl overflow-hidden mb-8 shadow-md max-h-[450px] bg-slate-900 group">
                            <div class="w-full h-[350px] sm:h-[450px] relative">
                                @foreach($artikel->gambar as $index => $img)
                                <div x-show="activeSlide === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                                    <img src="{{ asset('uploads/artikel/' . $img) }}" class="w-full h-full object-cover">
                                </div>
                                @endforeach
                            </div>

                            <!-- Buttons -->
                            <button @click="activeSlide = activeSlide === 0 ? slides - 1 : activeSlide - 1" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <button @click="activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>

                            <!-- Indicators -->
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                                @foreach($artikel->gambar as $index => $img)
                                <button @click="activeSlide = {{ $index }}" :class="activeSlide === {{ $index }} ? 'bg-white w-6' : 'bg-white/50 w-2.5'" class="h-2.5 rounded-full transition-all duration-300"></button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="w-full rounded-2xl overflow-hidden mb-8 shadow-md max-h-[400px]">
                        <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=1200&q=80" alt="{{ $artikel->judul }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <!-- Isi Artikel (Formatted Line Break & Clean Spacing) -->
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-base sm:text-lg space-y-4">
                    {!! nl2br(e($artikel->konten)) !!}
                </div>

                <!-- Article Footer / Share & Back -->
                <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>

            </article>

            <!-- Sidebar / Rekomendasi Artikel Lainnya (4 columns) -->
            <aside class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xl p-6">
                    <h3 class="text-lg font-extrabold text-slate-900 mb-6 pb-3 border-b border-slate-100 flex items-center justify-between">
                        <span>Artikel Lainnya</span>
                        <span class="w-2 h-2 rounded-full bg-brand"></span>
                    </h3>

                    @if(isset($artikelLainnya) && $artikelLainnya->count() > 0)
                        <div class="space-y-5">
                            @foreach($artikelLainnya as $item)
                            <a href="{{ route('artikel.show', $item->slug) }}" class="group flex gap-4 items-center p-2 rounded-xl hover:bg-slate-50 transition-colors">
                                <div class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 relative">
                                    @if($item->gambar && is_array($item->gambar) && count($item->gambar) > 0)
                                        <img src="{{ asset('uploads/artikel/' . $item->gambar[0]) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=300&q=80" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-400 mb-1">
                                        {{ $item->created_at->translatedFormat('d M Y') }}
                                    </p>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand transition-colors line-clamp-2 leading-snug">
                                        {{ $item->judul }}
                                    </h4>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada artikel rekomendasi lainnya.</p>
                    @endif
                </div>
            </aside>

        </div>
    </div>
</div>
@endsection
