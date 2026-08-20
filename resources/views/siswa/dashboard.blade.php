@extends('layouts.app')
@section('title', 'Rapor & Dashboard Siswa')
@section('page_title', '🎓 Dashboard Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">✓</div>
    <p class="font-semibold text-sm">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold mt-0.5">✕</div>
    <div class="flex-1">
        <strong class="block text-sm font-bold">Terjadi Kesalahan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@php
    $rataRata = $nilais->avg('nilai_akhir') ?? 0;
@endphp

{{-- ============================================================
     1. BANNER SAMBUTAN & STATUS AKADEMIK SISWA (HERO SECTION)
     ============================================================ --}}
<div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden text-white">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-white rounded-full blur-2xl"></div>
    </div>
    
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <!-- Identitas Siswa -->
        <div class="flex items-center gap-4">
            <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-amber-400/80 shadow-lg flex-shrink-0 bg-white/10" alt="{{ auth()->user()->name }}">
            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-400 text-amber-950 font-black text-[11px] shadow-xs">
                        Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-white/10 text-blue-200 font-mono font-bold text-[11px] border border-white/10">
                        NIS: {{ $siswa->nis ?? '-' }}
                    </span>
                </div>
                <h1 class="text-lg sm:text-2xl font-black leading-tight">{{ auth()->user()->name }}</h1>
                <p class="text-blue-200 text-xs mt-1">Selamat datang di Portal Pembelajaran Digital Informatika Spensa.</p>
            </div>
        </div>

        <!-- Quick Summary Badges -->
        <div class="grid grid-cols-3 gap-2 sm:gap-3 w-full md:w-auto">
            <!-- Rata-rata Nilai -->
            <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-2xl p-3 text-center flex-1">
                <span class="text-[10px] uppercase font-bold text-blue-200 block">Rata-Rata</span>
                <span class="text-lg sm:text-xl font-black block mt-0.5 {{ $rataRata >= $kkm ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ number_format($rataRata, 1) }}
                </span>
                <span class="text-[9px] font-bold block mt-0.5 {{ $rataRata >= $kkm ? 'text-emerald-300' : 'text-amber-300' }}">
                    {{ $rataRata >= $kkm ? 'Tuntas' : 'Remedial' }}
                </span>
            </div>

            <!-- Kehadiran -->
            <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-2xl p-3 text-center flex-1">
                <span class="text-[10px] uppercase font-bold text-blue-200 block">Kehadiran</span>
                <span class="text-lg sm:text-xl font-black block mt-0.5 text-blue-300">
                    {{ number_format($persentaseHadir, 0) }}%
                </span>
                <span class="text-[9px] text-blue-200 font-bold block mt-0.5">
                    {{ $totalAbsen }} Hari
                </span>
            </div>

            <!-- Keaktifan -->
            <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-2xl p-3 text-center flex-1">
                <span class="text-[10px] uppercase font-bold text-blue-200 block">Keaktifan</span>
                <span class="text-lg sm:text-xl font-black block mt-0.5 text-amber-300">
                    {{ number_format($rataKeaktifan, 1) }}
                </span>
                <span class="text-[9px] text-blue-200 font-bold block mt-0.5">
                    Sikap KBM
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Ujian Aktif (Jika Ada) -->
    @if(isset($ujianAktifCount) && $ujianAktifCount > 0)
    <div class="mt-4 pt-3.5 border-t border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-amber-400/15 border-amber-300/30 p-3 rounded-2xl">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3 flex-shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-400"></span>
            </span>
            <p class="text-xs font-bold text-amber-200">
                Ada <span class="font-black text-amber-300">{{ $ujianAktifCount }} Ujian Harian</span> yang sedang aktif untuk kelasmu!
            </p>
        </div>
        <a href="{{ route('siswa.ujian.index') }}" class="px-4 py-1.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xs rounded-xl shadow-md transition-all active:scale-95 whitespace-nowrap">
            Masuk Ruang Ujian →
        </a>
    </div>
    @endif
</div>

{{-- ============================================================
     2. TAB NAVIGASI KONTEN (NILAI, MATERI, PROFIL, TESTIMONI)
     ============================================================ --}}
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ tab: 'nilai', searchMateri: '' }">
    <!-- Tabs Header -->
    <div class="flex border-b border-slate-200 bg-slate-50/80 overflow-x-auto p-1.5 gap-1">
        <button @click="tab = 'nilai'" 
                :class="tab === 'nilai' ? 'bg-white text-blue-800 font-extrabold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                class="px-5 py-2.5 text-xs rounded-2xl transition-all whitespace-nowrap flex items-center gap-2">
            <span>📑</span> Daftar Nilai Rapor
        </button>
        <button @click="tab = 'materi'" 
                :class="tab === 'materi' ? 'bg-white text-blue-800 font-extrabold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                class="px-5 py-2.5 text-xs rounded-2xl transition-all whitespace-nowrap flex items-center gap-2">
            <span>📚</span> Materi Pembelajaran
        </button>
        <button @click="tab = 'profil'" 
                :class="tab === 'profil' ? 'bg-white text-blue-800 font-extrabold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                class="px-5 py-2.5 text-xs rounded-2xl transition-all whitespace-nowrap flex items-center gap-2">
            <span>👤</span> Akun & Profil
        </button>
        <button @click="tab = 'testimoni'" 
                :class="tab === 'testimoni' ? 'bg-white text-blue-800 font-extrabold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                class="px-5 py-2.5 text-xs rounded-2xl transition-all whitespace-nowrap flex items-center gap-2">
            <span>💬</span> Testimoni & Masukan
        </button>
    </div>

    {{-- ============================================================
         TAB 1: DAFTAR NILAI (RESPONSIVE: TABLE ON DESKTOP, CARDS ON MOBILE)
         ============================================================ --}}
    <div x-show="tab === 'nilai'" class="p-4 sm:p-6">
        @if($nilais->isEmpty())
        <div class="p-12 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="font-bold text-slate-600 text-sm">Belum Ada Nilai Tersimpan</p>
            <p class="text-xs text-slate-400 mt-1">Nilai ulangan dan tugas akan muncul setelah dinilai dan dipublikasikan oleh Guru.</p>
        </div>
        @else

        <!-- A. TAMPILAN TABEL DESKTOP (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto border border-slate-200 rounded-2xl shadow-xs">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 uppercase font-extrabold text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Materi / Bab Pembelajaran</th>
                        <th class="py-3.5 px-4 w-24 text-center bg-blue-50/50 text-blue-900">Keaktifan</th>
                        <th class="py-3.5 px-4 w-20 text-center">Tugas</th>
                        <th class="py-3.5 px-4 w-20 text-center">Quiz</th>
                        <th class="py-3.5 px-4 w-20 text-center">Proyek</th>
                        <th class="py-3.5 px-4 w-24 text-center bg-amber-50/60 text-amber-900">Ulangan</th>
                        <th class="py-3.5 px-4 w-36 text-center bg-slate-100">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($nilais as $index => $nilai)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-400 font-bold">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $nilai->bab }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-blue-800 bg-blue-50/20">{{ number_format($rataKeaktifan, 1) }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-600 font-medium">{{ (float)$nilai->tugas > 0 ? number_format($nilai->tugas, 1) : '-' }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-600 font-medium">{{ (float)$nilai->quiz > 0 ? number_format($nilai->quiz, 1) : '-' }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-600 font-medium">{{ (float)$nilai->proyek > 0 ? number_format($nilai->proyek, 1) : '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-amber-800 bg-amber-50/20">{{ (float)$nilai->ulangan > 0 ? number_format($nilai->ulangan, 1) : '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-sm font-black {{ $nilai->nilai_akhir >= $kkm ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ number_format($nilai->nilai_akhir, 1) }}
                                </span>
                                @if($nilai->nilai_akhir >= $kkm)
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase border border-emerald-200">Tuntas</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-black rounded-full uppercase border border-red-200">Remedial</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- B. TAMPILAN KARTU MOBILE (Khusus Layar HP / Mobile-First) -->
        <div class="block md:hidden space-y-3.5">
            @foreach($nilais as $index => $nilai)
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 shadow-xs flex flex-col gap-3">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bab {{ $index + 1 }}</span>
                        <h4 class="font-black text-slate-800 text-sm leading-tight">{{ $nilai->bab }}</h4>
                    </div>
                    <div>
                        @if($nilai->nilai_akhir >= $kkm)
                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase border border-emerald-200">Tuntas</span>
                        @else
                            <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-[10px] font-black rounded-full uppercase border border-red-200">Remedial</span>
                        @endif
                    </div>
                </div>

                <!-- Breakdown Skor Grid -->
                <div class="grid grid-cols-3 gap-2 text-center bg-white p-2.5 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold block">Keaktifan</span>
                        <span class="font-extrabold text-blue-700">{{ number_format($rataKeaktifan, 1) }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold block">Tugas</span>
                        <span class="font-bold text-slate-700">{{ (float)$nilai->tugas > 0 ? number_format($nilai->tugas, 1) : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold block">Ulangan</span>
                        <span class="font-bold text-amber-700">{{ (float)$nilai->ulangan > 0 ? number_format($nilai->ulangan, 1) : '-' }}</span>
                    </div>
                </div>

                <!-- Nilai Akhir Box -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-500">Nilai Akhir (KKM: {{ $kkm }}):</span>
                    <span class="text-xl font-black {{ $nilai->nilai_akhir >= $kkm ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ number_format($nilai->nilai_akhir, 1) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ============================================================
         TAB 2: MATERI BELAJAR DENGAN PENCARIAN
         ============================================================ --}}
    <div x-show="tab === 'materi'" x-cloak class="p-4 sm:p-6 bg-slate-50/50">
        <!-- Search Bar Materi -->
        <div class="mb-5 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" x-model="searchMateri" placeholder="Cari judul materi atau topik pembelajaran..." class="input-compact bg-transparent border-0 focus:ring-0 w-full text-xs font-semibold text-slate-700 placeholder:text-slate-400">
            <button x-show="searchMateri" @click="searchMateri = ''" class="text-xs text-slate-400 hover:text-slate-600 px-2 font-bold">✕</button>
        </div>

        @if(isset($materis) && $materis->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center shadow-xs">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <p class="text-slate-600 font-bold text-sm">Belum Ada Materi Belajar</p>
            <p class="text-slate-400 text-xs mt-1">Materi pelajaran yang dibagikan guru akan muncul di sini.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            @foreach($materis as $materi)
            <div x-show="!searchMateri || '{{ strtolower(addslashes($materi->judul)) }}'.includes(searchMateri.toLowerCase()) || '{{ strtolower(addslashes($materi->deskripsi ?? '')) }}'.includes(searchMateri.toLowerCase())" 
                 class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col group hover:shadow-md hover:border-blue-300 transition-all">
                @if($materi->foto)
                    <div class="h-36 sm:h-44 w-full overflow-hidden bg-slate-100">
                        <img src="{{ \App\Services\FileStorageService::url($materi->foto, 'materi') }}" alt="{{ $materi->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                @else
                    <div class="h-36 sm:h-44 w-full bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                @endif
                
                <div class="p-4 flex flex-col flex-grow">
                    <h4 class="font-extrabold text-slate-800 line-clamp-2 leading-snug mb-2 text-sm">{{ $materi->judul }}</h4>
                    
                    <p class="text-xs text-slate-500 mb-4 flex-grow line-clamp-3 leading-relaxed">
                        {{ $materi->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}
                    </p>
                    
                    <div class="mt-auto flex flex-col gap-2 pt-3 border-t border-slate-100">
                        <span class="text-[10px] font-medium text-slate-400 flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $materi->created_at->diffForHumans() }}
                        </span>
                        
                        <div class="flex items-center justify-between gap-2">
                            @if($materi->file_materi)
                            <a href="{{ asset('uploads/materi/' . $materi->file_materi) }}" target="_blank" class="text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl flex items-center transition-colors shadow-xs">
                                <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh File
                            </a>
                            @else
                            <span></span>
                            @endif

                            @if($materi->link)
                            <a href="{{ $materi->link }}" target="_blank" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-xl flex items-center transition-colors">
                                Buka Link
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ============================================================
         TAB 3: PROFIL & AKUN SISWA
         ============================================================ --}}
    <div x-show="tab === 'profil'" x-cloak class="p-6">
        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 text-sm flex items-center gap-2">
            <span>⚙️</span> Pengaturan Akun & Profil Siswa
        </h3>
        
        <form action="{{ url('/app/profil') }}" method="POST" enctype="multipart/form-data" class="max-w-md space-y-4">
            @csrf
            @method('PUT')

            <!-- Upload Foto Profil -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Foto Profil Siswa</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl border-2 border-blue-500 overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0 shadow-xs">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                    </div>
                    <div class="flex-1">
                        <input type="file" id="siswaAvatarInput" name="avatar" accept=".jpg,.jpeg,.png,.webp" onchange="compressSiswaAvatar(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Otomatis dioptimasi)</p>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Username (Untuk Login)</label>
                <input type="text" name="username" value="{{ auth()->user()->username }}" required class="input-compact w-full bg-slate-50 rounded-xl min-h-[40px] font-bold text-slate-800">
                <p class="text-[10px] text-slate-400 mt-1">Defaultnya adalah Nomer Induk (NIS) Anda.</p>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Password Baru (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah sandi" class="input-compact w-full bg-slate-50 rounded-xl min-h-[40px]">
                <p class="text-[10px] text-slate-400 mt-1">Isi minimal 4 karakter jika ingin mengganti sandi akun.</p>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="btn-compact px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md active:scale-95 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- ============================================================
         TAB 4: TESTIMONI
         ============================================================ --}}
    <div x-show="tab === 'testimoni'" x-cloak class="p-6">
        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 text-sm flex items-center gap-2">
            <span>💬</span> Kirim Testimoni & Masukan
        </h3>
        
        <form action="{{ url('/app/komentar') }}" method="POST" class="max-w-xl space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Isi Masukan / Kesan Pembelajaran</label>
                <textarea name="isi_komentar" required maxlength="300" rows="4" class="input-compact w-full bg-slate-50 rounded-xl p-3 text-xs" placeholder="Tuliskan pengalaman belajar, materi favorit, atau saran untuk pembelajaran Informatika..."></textarea>
                <p class="text-[10px] text-slate-400 mt-1">Maksimal 300 karakter. Masukan dapat dikirim 1x setiap 7 hari.</p>
            </div>
            
            <div class="flex items-center mt-2">
                <input type="checkbox" name="is_anonim" id="is_anonim" value="1" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                <label for="is_anonim" class="ml-2 text-xs font-semibold text-slate-700 cursor-pointer">Tampilkan sebagai Anonim (sembunyikan nama lengkap)</label>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="btn-compact px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md active:scale-95 transition-all">
                    Kirim Masukan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let compressedSiswaAvatarBlob = null;

function compressSiswaAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    compressedSiswaAvatarBlob = null;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            try {
                const canvas = document.createElement('canvas');
                const targetSize = 150;
                canvas.width = targetSize;
                canvas.height = targetSize;
                const ctx = canvas.getContext('2d');

                let srcX = 0, srcY = 0, srcSize = Math.min(img.width, img.height);
                if (img.width > img.height) {
                    srcX = (img.width - img.height) / 2;
                } else {
                    srcY = (img.height - img.width) / 2;
                }

                ctx.drawImage(img, srcX, srcY, srcSize, srcSize, 0, 0, targetSize, targetSize);

                canvas.toBlob(function(blob) {
                    if (blob && blob.size > 0) {
                        compressedSiswaAvatarBlob = blob;
                    }
                }, 'image/jpeg', 0.85);
            } catch (err) {
                compressedSiswaAvatarBlob = null;
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('form[action="{{ url('/app/profil') }}"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (compressedSiswaAvatarBlob) {
                e.preventDefault();
                const formData = new FormData(profileForm);
                formData.set('avatar', compressedSiswaAvatarBlob, 'avatar.jpg');

                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Menyimpan...';
                }

                fetch('{{ url("/app/profil") }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                    redirect: 'manual'
                })
                .then(response => {
                    if (response.status === 200 || response.type === 'opaqueredirect' || response.status === 0) {
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan profil. Silakan coba lagi.');
                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Simpan Perubahan'; }
                    }
                })
                .catch(() => {
                    alert('Koneksi terputus. Silakan coba lagi.');
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Simpan Perubahan'; }
                });
            }
        });
    }
});
</script>

@endsection
