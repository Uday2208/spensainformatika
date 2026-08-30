@extends('layouts.app')
@section('title', 'Tugas Pembelajaran')
@section('page_title', '📋 Tugas Pembelajaran')
@section('content')

<!-- Header Section -->
<div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-5 sm:p-6 mb-6 shadow-xl text-white relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg bg-amber-400 text-amber-950 font-black text-[11px] shadow-xs uppercase">
                    Penugasan KBM
                </span>
                <span class="px-2.5 py-0.5 rounded-lg bg-white/10 text-blue-200 font-mono font-bold text-[11px] border border-white/10">
                    Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black">Daftar Tugas Pembelajaran</h1>
            <p class="text-xs text-blue-200 mt-1">Pantau seluruh instruksi penugasan rombel dan tugas khusus individu dari Guru.</p>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('siswa.tugas') }}" method="GET" class="w-full md:w-80">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari tugas / instruksi..." 
                       class="w-full bg-white/10 border border-white/20 rounded-2xl px-4 py-2.5 pl-10 text-xs font-semibold text-white placeholder:text-blue-200 focus:bg-white focus:text-slate-800 focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                <svg class="w-4 h-4 text-blue-200 absolute left-3.5 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request('search'))
                <a href="{{ route('siswa.tugas') }}" class="absolute right-3 top-2.5 text-xs text-blue-200 hover:text-white font-bold">✕</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Grid Daftar Tugas -->
@if($tugasList->isEmpty())
<div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center shadow-xs">
    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100 text-2xl">
        📋
    </div>
    <h3 class="font-extrabold text-slate-800 text-base mb-1">
        {{ request('search') ? 'Tugas Tidak Ditemukan' : 'Belum Ada Tugas Aktif' }}
    </h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto">
        {{ request('search') ? 'Tidak ditemukan tugas dengan kata kunci "'.request('search').'". Coba gunakan kata kunci lainnya.' : 'Saat ini belum ada tugas yang dibagikan untuk kelasmu atau untukmu secara individu.' }}
    </p>
    @if(request('search'))
    <a href="{{ route('siswa.tugas') }}" class="inline-block mt-4 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl transition-all">
        Lihat Semua Tugas
    </a>
    @endif
</div>
@else

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach($tugasList as $tugas)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all p-5 flex flex-col justify-between">
        <div>
            <!-- Header Card Tugas -->
            <div class="flex items-center justify-between gap-2 mb-3">
                @if($tugas->tipe_target === 'individu')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                    <span>⭐</span>
                    <span>Tugas Khusus Kamu</span>
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    <span>🏫</span>
                    <span>Rombel Kelas {{ $tugas->kelas->nama_kelas ?? '-' }}</span>
                </span>
                @endif

                @if($tugas->deadline)
                    @php
                        $isOverdue = \Carbon\Carbon::now()->isAfter($tugas->deadline);
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $isOverdue ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        <span>⏰</span>
                        <span>{{ \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M, H:i') }}</span>
                    </span>
                @endif
            </div>

            <!-- Judul Tugas -->
            <h3 class="font-bold text-slate-800 text-sm leading-snug">{{ $tugas->judul }}</h3>

            <!-- Nama Guru -->
            <p class="text-[11px] text-slate-400 font-medium mt-1">
                Oleh: <span class="font-semibold text-slate-600">{{ $tugas->guru->name ?? 'Guru Pengampu' }}</span>
            </p>

            <!-- Deskripsi / Petunjuk -->
            @if($tugas->deskripsi)
            <div class="mt-3 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed">
                {{ $tugas->deskripsi }}
            </div>
            @endif
        </div>

        <!-- Tombol Aksi Lampiran / Link -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-2">
            @if($tugas->file_tugas)
            <a href="{{ $tugas->file_url }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Unduh Lampiran / Lembar Kerja</span>
            </a>
            @endif

            @if($tugas->link)
            <a href="{{ $tugas->link }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all border border-slate-200">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>Buka Link Referensi</span>
            </a>
            @endif

            <span class="text-[10px] text-slate-400 text-right mt-1">
                Diterbitkan {{ \Carbon\Carbon::parse($tugas->created_at)->diffForHumans() }}
            </span>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6">
    {{ $tugasList->links() }}
</div>

@endif

@endsection
