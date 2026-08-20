@extends('layouts.app')
@section('title', 'Materi Pembelajaran')
@section('page_title', '📚 Materi Pembelajaran')
@section('content')

<!-- Header Section -->
<div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-5 sm:p-6 mb-6 shadow-xl text-white relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg bg-amber-400 text-amber-950 font-black text-[11px] shadow-xs uppercase">
                    Modul & Materi KBM
                </span>
                <span class="px-2.5 py-0.5 rounded-lg bg-white/10 text-blue-200 font-mono font-bold text-[11px] border border-white/10">
                    Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black">Materi Pembelajaran Informatika</h1>
            <p class="text-xs text-blue-200 mt-1">Akses seluruh dokumen modul, slide presentasi, dan video pembelajaran dari Guru.</p>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('siswa.materi') }}" method="GET" class="w-full md:w-80">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari topik / judul materi..." 
                       class="w-full bg-white/10 border border-white/20 rounded-2xl px-4 py-2.5 pl-10 text-xs font-semibold text-white placeholder:text-blue-200 focus:bg-white focus:text-slate-800 focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                <svg class="w-4 h-4 text-blue-200 absolute left-3.5 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request('search'))
                <a href="{{ route('siswa.materi') }}" class="absolute right-3 top-2.5 text-xs text-blue-200 hover:text-white font-bold">✕</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Material Cards Grid -->
@if($materis->isEmpty())
<div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center shadow-xs">
    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
    </div>
    <h3 class="font-extrabold text-slate-800 text-base mb-1">
        {{ request('search') ? 'Materi Tidak Ditemukan' : 'Belum Ada Materi Pembelajaran' }}
    </h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto">
        {{ request('search') ? 'Tidak ditemukan materi dengan kata kunci "'.request('search').'". Coba gunakan kata kunci lainnya.' : 'Guru belum membagikan modul/materi pembelajaran untuk kelas ini.' }}
    </p>
    @if(request('search'))
    <a href="{{ route('siswa.materi') }}" class="inline-block mt-4 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl transition-all">
        Lihat Semua Materi
    </a>
    @endif
</div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    @foreach($materis as $materi)
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden flex flex-col group hover:shadow-lg hover:border-blue-300 transition-all duration-300">
        @if($materi->foto)
            <div class="h-44 w-full overflow-hidden bg-slate-100 relative">
                <img src="{{ \App\Services\FileStorageService::url($materi->foto, 'materi') }}" alt="{{ $materi->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent"></div>
            </div>
        @else
            <div class="h-44 w-full bg-gradient-to-br from-blue-600 via-indigo-600 to-slate-800 flex items-center justify-center relative">
                <svg class="w-14 h-14 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        @endif
        
        <div class="p-5 flex flex-col flex-grow">
            <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1 mb-1.5">
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $materi->created_at->diffForHumans() }}
            </span>

            <h3 class="font-black text-slate-800 line-clamp-2 leading-snug mb-2 text-sm group-hover:text-blue-600 transition-colors">
                {{ $materi->judul }}
            </h3>
            
            <p class="text-xs text-slate-500 mb-4 flex-grow line-clamp-3 leading-relaxed">
                {{ $materi->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}
            </p>
            
            <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                @if($materi->file_materi)
                <a href="{{ asset('uploads/materi/' . $materi->file_materi) }}" target="_blank" class="text-xs font-black text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3.5 py-2 rounded-xl flex items-center transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh Modul
                </a>
                @else
                <span></span>
                @endif

                @if($materi->link)
                <a href="{{ $materi->link }}" target="_blank" class="text-xs font-black text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3.5 py-2 rounded-xl flex items-center transition-colors">
                    Buka Tautan
                    <svg class="w-3.5 h-3.5 ml-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($materis->hasPages())
<div class="mt-6 p-4 bg-white rounded-2xl border border-slate-200 shadow-xs flex justify-between items-center text-xs text-slate-500">
    <span>Menampilkan {{ $materis->firstItem() }} - {{ $materis->lastItem() }} dari {{ $materis->total() }} materi</span>
    {{ $materis->links() }}
</div>
@endif

@endif

@endsection
