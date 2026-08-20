@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', '🎓 Dashboard')
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
     2. DAFTAR NILAI RAPOR (INTI DASHBOARD SISWA)
     ============================================================ --}}
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                <span>📑</span> Rapor Akademik Siswa
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi nilai per bab mata pelajaran Informatika (KKM: {{ $kkm }}).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('siswa.materi') }}" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 shadow-xs">
                <span>📚</span> Buka Materi Belajar →
            </a>
        </div>
    </div>

    <div class="p-4 sm:p-6">
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
                        <h3 class="font-black text-slate-800 text-sm leading-tight">{{ $nilai->bab }}</h3>
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
</div>

@endsection
