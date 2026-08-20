@extends('layouts.app')
@section('title', 'Input Nilai Keaktifan')
@section('page_title', '📝 Input Nilai Keaktifan')
@section('content')

{{-- ============================================================
     BANNER INFO UTAMA
     ============================================================ --}}
<div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-5 mb-5 shadow-xl relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-24 translate-x-24"></div>
    </div>
    <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg relative">
        <svg class="w-7 h-7 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base leading-tight">Halaman Input Nilai Keaktifan</h2>
        <p class="text-blue-200 text-xs mt-1 leading-relaxed">
            Halaman ini digunakan untuk <strong class="text-amber-300">mengisi nilai keaktifan harian</strong> siswa berdasarkan kelas dan pertemuan.
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
     FILTER FORM
     ============================================================ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-5">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-6 h-6 bg-slate-100 rounded-lg flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        </div>
        <h3 class="font-bold text-slate-700 text-sm">Filter Kelas & Pertemuan</h3>
    </div>
    <form action="{{ url('app/penilaian-harian') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Pilih Kelas</label>
            <select name="kelas_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all cursor-pointer min-h-[44px]" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all min-h-[44px]" required>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Pertemuan ke-</label>
            <input type="text" name="pertemuan" value="{{ $pertemuan }}" placeholder="Misal: 1 atau Bab 1" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all min-h-[44px]" required>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-500/25 transition-all min-h-[44px]">
                Tampilkan Siswa
            </button>
        </div>
    </form>
</div>

{{-- ============================================================
     VALIDASI ABSENSI BELUM DIISI
     ============================================================ --}}
@if($kelas_id && isset($absensi_belum_diisi) && $absensi_belum_diisi)
<div class="p-8 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm mb-6 flex flex-col items-center justify-center text-center">
    <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    </div>
    <h3 class="text-base font-bold text-amber-800 mb-1">Absensi Belum Diisi untuk Tanggal & Kelas Ini!</h3>
    <p class="text-xs text-amber-700 max-w-md leading-relaxed">
        Anda harus mengisi kehadiran siswa terlebih dahulu pada tanggal ini sebelum memberikan nilai keaktifan harian.
    </p>
    <div class="mt-4 flex gap-3">
        <a href="{{ url('app/input-pembelajaran') }}?kelas_id={{ $kelas_id }}&tanggal={{ $tanggal }}&pertemuan={{ $pertemuan }}" 
           class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
            Lakukan Input Terpadu (Sangat Direkomendasikan)
        </a>
        <a href="{{ url('app/absensi') }}?kelas_id={{ $kelas_id }}&tanggal={{ $tanggal }}" 
           class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
            Koreksi Absensi Terlebih Dahulu
        </a>
    </div>
</div>

@elseif($kelas_id && $siswas->count() > 0)
<div>
    {{-- Banner Status Keterisian Nilai Keaktifan --}}
    @if(isset($sudah_dinilai) && $sudah_dinilai)
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 mb-4 shadow-2xs">
        <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="font-bold text-emerald-900 text-sm">
                Data Nilai Keaktifan Tanggal <span class="font-black">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</span> Sudah Ada (Pertemuan Ke-{{ $existing_pertemuan ?? $pertemuan }}).
            </p>
            <p class="text-emerald-700 text-xs mt-0.5">
                Nilai di bawah dimuat dari data tersimpan. Lakukan perubahan jika ingin mengoreksi nilai/catatan, lalu klik <strong>Simpan Perubahan Nilai Keaktifan</strong>.
            </p>
        </div>
    </div>
    @else
    <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-2xl px-5 py-3.5 mb-4 shadow-2xs">
        <div class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="font-bold text-blue-900 text-xs">
                Belum ada data nilai keaktifan tersimpan untuk tanggal ini.
            </p>
            <p class="text-blue-700 text-[11px] mt-0.5">
                Nilai awal otomatis disesuaikan dengan kehadiran siswa (Hadir = 80, Tidak Hadir = 70).
            </p>
        </div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        <div>
            <h3 class="font-bold text-slate-700 text-sm">Daftar Keaktifan Siswa</h3>
            <p class="text-xs text-slate-400 mt-0.5">Pertemuan: <strong>{{ $pertemuan }}</strong> | Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</strong></p>
        </div>
    </div>

    <form action="{{ url('app/penilaian-harian') }}" method="POST">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        <input type="hidden" name="pertemuan" value="{{ $pertemuan }}">

        <div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-compact">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="w-12 text-center px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">No</th>
                            <th class="w-28 px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">NIS</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Nama Siswa</th>
                            <th class="w-32 px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wide">Kehadiran</th>
                            <th class="w-40 px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wide">Nilai Keaktifan</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Catatan Keaktifan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($siswas as $index => $siswa)
                        @php
                            $absensiStatus = $siswa->absensis->first()->status ?? 'alpha';
                            $isHadir = in_array($absensiStatus, ['hadir', 'dispen']);
                            $existing = $siswa->penilaianHarians->first();
                            $nilai_awal = $existing ? $existing->nilai : ($isHadir ? 80 : 70);
                            $catatan_awal = $existing ? $existing->catatan : '';
                            
                            $badgeColor = [
                                'hadir'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'sakit'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                'izin'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                'dispen' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'alpha'  => 'bg-red-50 text-red-700 border-red-200',
                            ][$absensiStatus] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors {{ !$isHadir ? 'bg-slate-50/30 opacity-70' : '' }}">
                            <td class="text-center text-slate-500 px-4 py-3 text-sm">{{ $index + 1 }}</td>
                            <td class="font-mono text-slate-600 text-xs px-4 py-3">{{ $siswa->nis }}</td>
                            <td class="font-semibold text-slate-800 text-sm px-4 py-3">{{ $siswa->user->name ?? '-' }}</td>
                            <td class="text-center px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase {{ $badgeColor }}">
                                    {{ $absensiStatus === 'alpha' ? 'Alpa' : $absensiStatus }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-3">
                                <select name="nilai[{{ $siswa->id }}]" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-2 py-1.5 focus:outline-none focus:border-blue-400 cursor-pointer min-h-[32px] {{ !$isHadir ? 'bg-slate-100 text-slate-500' : '' }}">
                                    <option value="60" {{ $nilai_awal == 60 ? 'selected' : '' }}>Pasif (60)</option>
                                    <option value="70" {{ $nilai_awal == 70 ? 'selected' : '' }}>Tidak Hadir (70)</option>
                                    <option value="80" {{ $nilai_awal == 80 ? 'selected' : '' }}>Hadir (80)</option>
                                    <option value="90" {{ $nilai_awal == 90 ? 'selected' : '' }}>Aktif (90)</option>
                                    <option value="100" {{ $nilai_awal == 100 ? 'selected' : '' }}>Sangat Aktif (100)</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="catatan[{{ $siswa->id }}]" value="{{ $catatan_awal }}" 
                                       placeholder="💬 Catatan keaktifan siswa..." 
                                       class="w-full text-xs py-1.5 px-3 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:border-blue-400 focus:bg-white transition-colors">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="w-full lg:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-500/25 transition-all flex items-center justify-center min-h-[44px]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                {{ (isset($sudah_dinilai) && $sudah_dinilai) ? 'SIMPAN PERUBAHAN NILAI KEAKTIFAN' : 'SIMPAN NILAI KEAKTIFAN' }}
            </button>
        </div>
    </form>
</div>
@elseif($kelas_id)
<div class="p-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
    <p class="font-medium">Tidak ada data siswa untuk kelas ini.</p>
    <a href="{{ url('/app/data-siswa') }}" class="text-blue-500 text-sm hover:underline mt-1 block">→ Tambah Data Siswa</a>
</div>
@endif

@endsection
