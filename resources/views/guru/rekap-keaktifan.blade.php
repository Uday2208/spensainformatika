@extends('layouts.app')
@section('title', 'Rekap Keaktifan Siswa')
@section('page_title', '📊 Rekap Nilai Keaktifan')
@section('content')

{{-- ============================================================
     BANNER INFO UTAMA
     ============================================================ --}}
<div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-5 mb-5 shadow-xl relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-24 translate-x-24"></div>
    </div>
    <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg relative">
        <svg class="w-7 h-7 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base leading-tight">Rekapitulasi Nilai Keaktifan Siswa</h2>
        <p class="text-blue-200 text-xs mt-1 leading-relaxed">
            Pantau rekam jejak dinamika keaktifan harian per pertemuan, evaluasi catatan pembinaan, serta <strong class="text-amber-300">edit langsung nilai keaktifan</strong> siswa.
        </p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
    </div>
    <p class="font-semibold text-sm">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-sm">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </div>
    <div class="flex-1">
        <p class="font-bold text-sm">Terjadi Kesalahan</p>
        <ul class="list-disc ml-5 text-xs text-red-700 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- ============================================================
     FILTER & CONTROLS
     ============================================================ --}}
<div class="space-y-4 mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Filter Per Kelas -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Pilih Kelas
            </h3>
            <form action="{{ url('/app/rekap-keaktifan') }}" method="GET" id="formFilterKelas">
                @if(request('nama_siswa')) <input type="hidden" name="nama_siswa" value="{{ request('nama_siswa') }}"> @endif
                <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer font-semibold text-slate-700 min-h-[42px]" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas untuk Rekap Matriks --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Filter Pencarian Siswa -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Pencarian Siswa
            </h3>
            <form action="{{ url('/app/rekap-keaktifan') }}" method="GET" class="flex gap-2">
                @if($kelas_id) <input type="hidden" name="kelas_id" value="{{ $kelas_id }}"> @endif
                <input type="text" name="nama_siswa" value="{{ request('nama_siswa') }}" placeholder="Cari NIS / Nama siswa..." class="input-compact bg-slate-50 flex-1 min-h-[42px]">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm px-4 min-h-[42px] font-bold">Cari</button>
                @if(request('nama_siswa') || request('kelas_id'))
                    <a href="{{ url('/app/rekap-keaktifan') }}" class="btn-compact bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 flex items-center justify-center min-h-[42px]" title="Reset Filter">✕</a>
                @endif
            </form>
        </div>

        <!-- Tombol Aksi & Ekspor -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor & Cetak
            </h3>
            <div class="flex items-center gap-2">
                @if($kelas_id)
                <form action="{{ url('/app/rekap-keaktifan/export') }}" method="GET" class="flex-1">
                    <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                    <button type="submit" class="btn-compact w-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm text-xs min-h-[42px] font-bold">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Excel CSV
                    </button>
                </form>
                @else
                <button disabled class="btn-compact w-full bg-slate-200 text-slate-400 cursor-not-allowed flex items-center justify-center text-xs min-h-[42px] font-bold">
                    Pilih Kelas Dulu
                </button>
                @endif
                <button onclick="window.print()" class="btn-compact bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center shadow-sm text-xs px-3.5 min-h-[42px] font-bold">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         STATISTIK RINGKASAN (JIKA KELAS DIPILIH)
         ============================================================ --}}
    @if($kelas_id)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Rata-rata Keaktifan Kelas -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Kelas</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black {{ $stats['rata_kelas'] >= 85 ? 'text-emerald-600' : ($stats['rata_kelas'] >= 75 ? 'text-blue-600' : 'text-amber-600') }}">
                    {{ number_format($stats['rata_kelas'], 1) }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">Skor</span>
            </div>
        </div>

        <!-- Total Pertemuan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pertemuan</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-indigo-600">{{ $stats['total_pertemuan'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Sesi</span>
            </div>
        </div>

        <!-- Total Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Siswa</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-slate-800">{{ $stats['total_siswa'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">Orang</span>
            </div>
        </div>

        <!-- Sangat Aktif (>= 90) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sangat Aktif (≥90)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-emerald-700">{{ $stats['sangat_aktif'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
        </div>

        <!-- Perlu Bimbingan (< 75) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu Bimbingan (&lt;75)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-amber-600">{{ $stats['perlu_bimbingan'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
        </div>

        <!-- Total Log Tercatat -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Total Log Nilai</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-purple-700">{{ $stats['total_log'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700">Data</span>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ============================================================
     KONDISI 1: JIKA KELAS SUDAH DIPILIH (TABEL MATRIKS KEAKTIFAN)
     ============================================================ --}}
@if($kelas_id)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
    <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span>📑</span> Matriks Nilai Keaktifan Per Pertemuan
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                💡 <span class="font-semibold text-blue-700">Tips:</span> Klik pada <strong class="underline cursor-pointer">badge nilai</strong> atau tombol <strong class="text-slate-700">✏️ Edit</strong> untuk langsung memperbarui skor dan catatan siswa.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/app/penilaian-harian') }}?kelas_id={{ $kelas_id }}" class="btn-compact bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold px-3 py-1.5 rounded-xl border border-blue-200 flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Pertemuan Baru
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-slate-100/90 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200">
                    <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-10">No</th>
                    <th class="py-3 px-3 w-28 sticky left-10 bg-slate-100 z-10">NIS</th>
                    <th class="py-3 px-4 min-w-[180px] sticky left-38 bg-slate-100 z-10 shadow-r">Nama Siswa</th>
                    
                    {{-- Kolom Dinamis Per Pertemuan --}}
                    @forelse($daftarPertemuan as $p)
                    <th class="py-3 px-3 min-w-[100px] text-center border-l border-slate-200">
                        <div class="font-extrabold text-slate-800">P-{{ $p->pertemuan }}</div>
                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                            {{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m') : '-' }}
                        </div>
                    </th>
                    @empty
                    <th class="py-3 px-4 text-center text-slate-400 font-normal italic">
                        Belum ada data pertemuan keaktifan
                    </th>
                    @endforelse

                    <th class="py-3 px-4 min-w-[110px] text-center bg-blue-50/70 border-l border-blue-200 text-blue-900 font-extrabold">
                        Rata-Rata
                    </th>
                    <th class="py-3 px-4 min-w-[130px] text-center bg-blue-50/70 text-blue-900 font-extrabold">
                        Predikat
                    </th>
                    <th class="py-3 px-3 w-16 text-center border-l border-slate-200">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswas as $idx => $siswa)
                @php
                    $phMap = $siswa->penilaianHarians->keyBy('pertemuan');
                    $avg = $siswa->penilaianHarians->avg('nilai');
                    
                    $predikatText = '-';
                    $predikatColor = 'bg-slate-100 text-slate-600 border-slate-200';
                    if ($avg !== null) {
                        if ($avg >= 90) {
                            $predikatText = 'Sangat Aktif';
                            $predikatColor = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                        } elseif ($avg >= 80) {
                            $predikatText = 'Aktif';
                            $predikatColor = 'bg-blue-100 text-blue-800 border-blue-300';
                        } elseif ($avg >= 70) {
                            $predikatText = 'Cukup';
                            $predikatColor = 'bg-amber-100 text-amber-800 border-amber-300';
                        } else {
                            $predikatText = 'Pasif';
                            $predikatColor = 'bg-red-100 text-red-800 border-red-300';
                        }
                    }
                @endphp
                <tr class="hover:bg-slate-50/90 transition-colors group">
                    <td class="py-3 px-3 text-center font-bold text-slate-400 sticky left-0 bg-white group-hover:bg-slate-50 z-10">
                        {{ $idx + 1 }}
                    </td>
                    <td class="py-3 px-3 font-mono text-slate-600 text-xs sticky left-10 bg-white group-hover:bg-slate-50 z-10">
                        {{ $siswa->nis }}
                    </td>
                    <td class="py-3 px-4 font-bold text-slate-800 sticky left-38 bg-white group-hover:bg-slate-50 z-10 shadow-r">
                        {{ $siswa->user->name ?? '-' }}
                    </td>

                    {{-- Matriks Pertemuan --}}
                    @foreach($daftarPertemuan as $p)
                    @php
                        $item = $phMap->get($p->pertemuan);
                    @endphp
                    <td class="py-2.5 px-2.5 text-center border-l border-slate-100">
                        @if($item)
                            @php
                                $val = (int)$item->nilai;
                                $colorClass = 'bg-slate-100 text-slate-700 border-slate-300';
                                if ($val >= 100) $colorClass = 'bg-emerald-500 text-white border-emerald-600 shadow-xs';
                                elseif ($val >= 90) $colorClass = 'bg-emerald-100 text-emerald-800 border-emerald-300 font-extrabold';
                                elseif ($val >= 80) $colorClass = 'bg-blue-100 text-blue-800 border-blue-300 font-bold';
                                elseif ($val >= 70) $colorClass = 'bg-amber-100 text-amber-800 border-amber-300';
                                else $colorClass = 'bg-red-100 text-red-800 border-red-300';
                            @endphp
                            <button type="button" 
                                    onclick="openEditModal('{{ $item->id }}', '{{ $siswa->id }}', '{{ addslashes($siswa->user->name ?? $siswa->nis) }}', '{{ $p->pertemuan }}', '{{ $item->tanggal }}', '{{ $item->nilai }}', '{{ addslashes($item->catatan ?? '') }}')"
                                    class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs border {{ $colorClass }} hover:ring-2 hover:ring-blue-400 transition-all cursor-pointer min-w-[42px]"
                                    title="{{ $item->catatan ? 'Catatan: ' . $item->catatan : 'Klik untuk edit nilai' }}">
                                <span>{{ $item->nilai }}</span>
                                @if(!empty($item->catatan))
                                    <span class="ml-1 text-[9px] opacity-80" title="Ada catatan">💬</span>
                                @endif
                            </button>
                        @else
                            <button type="button" 
                                    onclick="openEditModal('new', '{{ $siswa->id }}', '{{ addslashes($siswa->user->name ?? $siswa->nis) }}', '{{ $p->pertemuan }}', '{{ $p->tanggal ?? date('Y-m-d') }}', '80', '')"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-300 hover:text-blue-600 hover:bg-blue-50 border border-dashed border-slate-200 transition-colors"
                                    title="Tambah nilai pertemuan ini">
                                +
                            </button>
                        @endif
                    </td>
                    @endforeach

                    @if($daftarPertemuan->count() === 0)
                    <td class="py-3 px-4 text-center text-slate-400 italic">
                        -
                    </td>
                    @endif

                    {{-- Rata-Rata --}}
                    <td class="py-3 px-4 text-center bg-blue-50/30 border-l border-blue-100 font-black text-sm {{ $avg >= 85 ? 'text-emerald-700' : ($avg >= 75 ? 'text-blue-700' : 'text-amber-700') }}">
                        {{ $avg !== null ? number_format($avg, 1) : '-' }}
                    </td>

                    {{-- Predikat Badge --}}
                    <td class="py-3 px-4 text-center bg-blue-50/30">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $predikatColor }}">
                            {{ $predikatText }}
                        </span>
                    </td>

                    {{-- Tombol Edit Siswa --}}
                    <td class="py-3 px-3 text-center border-l border-slate-100">
                        <button type="button"
                                onclick="openEditModal('new', '{{ $siswa->id }}', '{{ addslashes($siswa->user->name ?? $siswa->nis) }}', '', '{{ date('Y-m-d') }}', '80', '')"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                title="Edit / Input Keaktifan Siswa Ini">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 6 + $daftarPertemuan->count() }}" class="py-12 px-4 text-center text-slate-400">
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-400 text-xl">👥</div>
                        <p class="font-semibold text-sm">Tidak ada data siswa ditemukan pada filter ini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Legenda Skor Keaktifan -->
    <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center gap-4 text-xs text-slate-600">
        <span class="font-bold text-slate-700">Keterangan Skor:</span>
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold">100</span>
            <span>Sangat Aktif (100)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center text-[9px] font-bold">90</span>
            <span>Aktif (90)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded bg-blue-100 text-blue-800 border border-blue-300 flex items-center justify-center text-[9px] font-bold">80</span>
            <span>Hadir Normal (80)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center text-[9px] font-bold">70</span>
            <span>Tidak Hadir (70)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded bg-red-100 text-red-800 border border-red-300 flex items-center justify-center text-[9px] font-bold">60</span>
            <span>Pasif (60)</span>
        </div>
        <div class="flex items-center gap-1.5 text-slate-400">
            <span>💬 = Ada Catatan Guru</span>
        </div>
    </div>
</div>

{{-- ============================================================
     KONDISI 2: JIKA BELUM MEMILIH KELAS (SUMMARY CARD DAFTAR KELAS)
     ============================================================ --}}
@else
<div class="space-y-4">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
            <span>🏫</span> Ringkasan Keaktifan Semua Kelas
        </h3>
        <p class="text-xs text-slate-500 mb-6">Pilih salah satu kelas di bawah untuk melihat matriks lengkap dan mengedit nilai keaktifan per pertemuan.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($ringkasanKelas as $rk)
            <div class="border border-slate-200 hover:border-blue-300 rounded-2xl p-5 hover:shadow-md transition-all bg-gradient-to-b from-white to-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 font-extrabold text-sm rounded-xl border border-blue-200">
                            {{ $rk->nama_kelas }}
                        </span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $rk->siswas_count }} Siswa
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 my-4">
                        <div class="bg-white p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Pertemuan</span>
                            <span class="text-lg font-black text-indigo-600">{{ $rk->total_pertemuan }} Sesi</span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Rata-Rata</span>
                            <span class="text-lg font-black {{ $rk->rata_keaktifan >= 80 ? 'text-emerald-600' : 'text-blue-600' }}">
                                {{ $rk->rata_keaktifan > 0 ? number_format($rk->rata_keaktifan, 1) : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ url('/app/rekap-keaktifan') }}?kelas_id={{ $rk->id }}" class="mt-2 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl text-center shadow-sm transition-colors flex items-center justify-center gap-2">
                    <span>Buka Rekap Kelas {{ $rk->nama_kelas }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ============================================================
     MODAL EDIT NILAI KEAKTIFAN (INTERAKTIF & FLEKSIBEL)
     ============================================================ --}}
<div id="modalEditKeaktifan" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-900 to-indigo-900 text-white flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-400 text-amber-900 flex items-center justify-center font-bold text-base shadow-sm">
                    ✏️
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-tight" id="modalTitle">Edit Nilai Keaktifan</h3>
                    <p class="text-[11px] text-blue-200" id="modalSubtitle">Perbarui skor dan catatan evaluasi siswa</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                ✕
            </button>
        </div>

        <!-- Modal Form -->
        <form id="formEditKeaktifan" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="siswa_id" id="edit_siswa_id">
            <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">

            <!-- Nama Siswa (Readonly) -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nama Siswa</label>
                <input type="text" id="edit_nama_siswa" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 cursor-not-allowed">
            </div>

            <!-- Pertemuan & Tanggal -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Pertemuan Ke-</label>
                    <input type="text" name="pertemuan" id="edit_pertemuan" placeholder="Contoh: 1 atau Bab 1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-400 focus:bg-white transition-all" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="edit_tanggal" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-400 focus:bg-white transition-all" required>
                </div>
            </div>

            <!-- Pilihan Nilai Keaktifan -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Nilai Keaktifan</label>
                <div class="grid grid-cols-5 gap-1.5 mb-2">
                    <button type="button" onclick="setSkor(60)" class="btn-skor py-2 px-1 text-center rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-800 font-bold text-xs transition-all" data-val="60">
                        60<br><span class="text-[9px] font-normal opacity-80">Pasif</span>
                    </button>
                    <button type="button" onclick="setSkor(70)" class="btn-skor py-2 px-1 text-center rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs transition-all" data-val="70">
                        70<br><span class="text-[9px] font-normal opacity-80">Tdk Hadir</span>
                    </button>
                    <button type="button" onclick="setSkor(80)" class="btn-skor py-2 px-1 text-center rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold text-xs transition-all" data-val="80">
                        80<br><span class="text-[9px] font-normal opacity-80">Hadir</span>
                    </button>
                    <button type="button" onclick="setSkor(90)" class="btn-skor py-2 px-1 text-center rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs transition-all" data-val="90">
                        90<br><span class="text-[9px] font-normal opacity-80">Aktif</span>
                    </button>
                    <button type="button" onclick="setSkor(100)" class="btn-skor py-2 px-1 text-center rounded-xl border border-emerald-300 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs transition-all" data-val="100">
                        100<br><span class="text-[9px] font-normal opacity-90">Sangat Aktif</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium">Input Angka:</span>
                    <input type="number" name="nilai" id="edit_nilai" min="0" max="100" class="w-24 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-sm font-extrabold text-blue-700 text-center focus:outline-none focus:border-blue-500" required>
                    <span class="text-[11px] text-slate-400">(Rentang 0 - 100)</span>
                </div>
            </div>

            <!-- Catatan Keaktifan Guru -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Catatan Guru / Catatan Pembinaan</label>
                <textarea name="catatan" id="edit_catatan" rows="3" placeholder="💬 Tuliskan catatan apresiasi, dinamika kelompok, atau catatan pembinaan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="button" id="btnHapusLog" onclick="hapusLogKeaktifan()" class="px-3.5 py-2 text-red-600 hover:bg-red-50 rounded-xl text-xs font-bold transition-colors hidden">
                    🗑️ Hapus Log Ini
                </button>
                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 active:scale-95 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Hidden Form untuk Hapus Log --}}
<form id="formDeleteLog" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    let currentLogId = null;

    function openEditModal(logId, siswaId, namaSiswa, pertemuan, tanggal, nilai, catatan) {
        currentLogId = logId;
        const modal = document.getElementById('modalEditKeaktifan');
        const form = document.getElementById('formEditKeaktifan');
        const title = document.getElementById('modalTitle');
        const btnHapus = document.getElementById('btnHapusLog');

        document.getElementById('edit_siswa_id').value = siswaId;
        document.getElementById('edit_nama_siswa').value = namaSiswa;
        document.getElementById('edit_pertemuan').value = pertemuan || '';
        document.getElementById('edit_tanggal').value = tanggal || '{{ date('Y-m-d') }}';
        document.getElementById('edit_nilai').value = nilai || 80;
        document.getElementById('edit_catatan').value = catatan || '';

        if (logId && logId !== 'new') {
            title.innerText = 'Edit Nilai Keaktifan: Pertemuan ' + pertemuan;
            form.action = '{{ url("/app/penilaian-harian") }}/' + logId;
            btnHapus.classList.remove('hidden');
        } else {
            title.innerText = 'Input / Tambah Nilai Keaktifan';
            form.action = '{{ url("/app/penilaian-harian") }}/new';
            btnHapus.classList.add('hidden');
        }

        highlightSkorBtn(nilai || 80);
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('modalEditKeaktifan').classList.add('hidden');
    }

    function setSkor(val) {
        document.getElementById('edit_nilai').value = val;
        highlightSkorBtn(val);
    }

    function highlightSkorBtn(val) {
        document.querySelectorAll('.btn-skor').forEach(btn => {
            if (parseInt(btn.getAttribute('data-val')) === parseInt(val)) {
                btn.classList.add('ring-2', 'ring-blue-500', 'scale-105');
            } else {
                btn.classList.remove('ring-2', 'ring-blue-500', 'scale-105');
            }
        });
    }

    function hapusLogKeaktifan() {
        if (!currentLogId || currentLogId === 'new') return;
        if (confirm('Apakah Anda yakin ingin menghapus data keaktifan pada pertemuan ini?')) {
            const formDelete = document.getElementById('formDeleteLog');
            formDelete.action = '{{ url("/app/penilaian-harian") }}/' + currentLogId;
            formDelete.submit();
        }
    }

    // Close on Escape or click outside
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEditModal();
    });
</script>

<style>
    @media print {
        body {
            background: white !important;
        }
        aside, header, #formFilterKelas, .btn-compact, #modalEditKeaktifan, .shadow-xl, .shadow-sm {
            display: none !important;
        }
        .overflow-x-auto {
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            font-size: 10px !important;
        }
    }
</style>

@endsection
