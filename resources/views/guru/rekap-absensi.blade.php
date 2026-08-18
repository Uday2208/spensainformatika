@extends('layouts.app')
@section('title', 'Rekap Kehadiran')
@section('page_title', 'Rekap Kehadiran Siswa')
@section('content')

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div>
<!-- Filter & Action Controls -->
<div class="space-y-4 mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Filter Per Kelas -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Filter Kelas
            </h3>
            <form action="{{ url('/app/rekap-absensi') }}" method="GET" id="formFilterKelas">
                @if(request('nama_siswa')) <input type="hidden" name="nama_siswa" value="{{ request('nama_siswa') }}"> @endif
                <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer font-semibold text-slate-700" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Filter Per Nama Siswa -->
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Pencarian Siswa
            </h3>
            <form action="{{ url('/app/rekap-absensi') }}" method="GET" class="flex gap-2">
                @if($kelas_id) <input type="hidden" name="kelas_id" value="{{ $kelas_id }}"> @endif
                <input type="text" name="nama_siswa" value="{{ request('nama_siswa') }}" placeholder="Ketik nama siswa..." class="input-compact bg-slate-50 flex-1">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm px-3">Cari</button>
                @if(request('nama_siswa') || request('kelas_id'))
                    <a href="{{ url('/app/rekap-absensi') }}" class="btn-compact bg-slate-100 hover:bg-slate-200 text-slate-600 px-2.5 flex items-center justify-center" title="Reset Filter">✕</a>
                @endif
            </form>
        </div>

        <!-- Export & Cetak Buttons -->
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Laporan
            </h3>
            <div class="flex items-center gap-2">
                <form action="{{ url('/app/rekap-absensi/export') }}" method="GET" class="flex-1">
                    @if($kelas_id) <input type="hidden" name="kelas_id" value="{{ $kelas_id }}"> @endif
                    @if($nama_siswa) <input type="hidden" name="nama_siswa" value="{{ $nama_siswa }}"> @endif
                    <button type="submit" class="btn-compact w-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm text-xs">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        CSV Excel
                    </button>
                </form>
                <button onclick="window.print()" class="btn-compact bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center shadow-sm text-xs px-3">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Visual (Summary Cards) -->
    @if(isset($stats))
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Rata-rata Kehadiran -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Hadir</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold {{ $stats['avg_kehadiran'] >= 85 ? 'text-emerald-600' : ($stats['avg_kehadiran'] >= 75 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $stats['avg_kehadiran'] }}%
                </span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $stats['avg_kehadiran'] >= 85 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $stats['total_siswa'] }} Siswa
                </span>
            </div>
        </div>

        <!-- Total Hadir -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-green-600">Total Hadir (H)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold text-green-700">{{ $stats['total_hadir'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
            </div>
        </div>

        <!-- Total Sakit -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Total Sakit (S)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold text-blue-700">{{ $stats['total_sakit'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            </div>
        </div>

        <!-- Total Izin -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Total Izin (I)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold text-amber-700">{{ $stats['total_izin'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
        </div>

        <!-- Total Dispensasi -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Dispen (D)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold text-purple-700">{{ $stats['total_dispen'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            </div>
        </div>

        <!-- Total Alpha -->
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Alpha / Alpa (A)</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl font-extrabold text-red-700">{{ $stats['total_alpha'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Tabel Rekapitulasi Presensi -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($siswas->isEmpty())
        <div class="p-12 text-center text-slate-400 italic">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Tidak ada data presensi yang sesuai dengan filter.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                        <th class="w-12 text-center py-3">No</th>
                        <th class="py-3">Nomer Induk</th>
                        <th class="py-3">Nama Siswa</th>
                        <th class="py-3">Kelas</th>
                        <th class="text-center w-16 py-3 text-green-700 bg-green-50/50 font-bold">H</th>
                        <th class="text-center w-16 py-3 text-blue-700 bg-blue-50/50 font-bold">S</th>
                        <th class="text-center w-16 py-3 text-amber-700 bg-amber-50/50 font-bold">I</th>
                        <th class="text-center w-16 py-3 text-purple-700 bg-purple-50/50 font-bold">D</th>
                        <th class="text-center w-16 py-3 text-red-700 bg-red-50/50 font-bold">A</th>
                        <th class="text-center w-24 py-3 bg-slate-100 font-bold">% Kehadiran</th>
                        <th class="text-center w-24 py-3 no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($siswas as $index => $siswa)
                        @php
                            $totalHari = ($siswa->hadir_count ?? 0) + ($siswa->sakit_count ?? 0) + ($siswa->izin_count ?? 0) + ($siswa->dispen_count ?? 0) + ($siswa->alpha_count ?? 0);
                            $persen = $totalHari > 0 ? round((($siswa->hadir_count ?? 0) / $totalHari) * 100, 1) : 0;
                            $isWarning = ($siswa->alpha_count ?? 0) >= 3 || ($totalHari > 0 && $persen < 75);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors {{ $isWarning ? 'bg-red-50/20' : '' }}">
                            <td class="text-center text-slate-500 font-medium">{{ $siswas->firstItem() + $index }}</td>
                            <td class="font-mono font-bold text-slate-700">{{ $siswa->nis }}</td>
                            <td class="font-semibold text-slate-900">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $siswa->user->name ?? '-' }}</span>
                                    @if($isWarning)
                                        <span class="inline-flex items-center px-1.5 py-0.2 bg-red-100 text-red-700 text-[9px] font-extrabold rounded-full" title="Perhatian: Sering Alpha / Kehadiran Rendah">
                                            Perhatian
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-slate-600 font-medium">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center font-bold text-green-700 bg-green-50/30">{{ $siswa->hadir_count ?? 0 }}</td>
                            <td class="text-center font-bold text-blue-700 bg-blue-50/30">{{ $siswa->sakit_count ?? 0 }}</td>
                            <td class="text-center font-bold text-amber-700 bg-amber-50/30">{{ $siswa->izin_count ?? 0 }}</td>
                            <td class="text-center font-bold text-purple-700 bg-purple-50/30">{{ $siswa->dispen_count ?? 0 }}</td>
                            <td class="text-center font-bold text-red-700 bg-red-50/30">{{ $siswa->alpha_count ?? 0 }}</td>
                            <td class="text-center font-extrabold text-xs
                                {{ $persen >= 85 ? 'text-emerald-700 bg-emerald-50/60' : ($persen >= 75 ? 'text-amber-700 bg-amber-50/60' : 'text-red-700 bg-red-50/60') }}">
                                {{ $totalHari > 0 ? $persen . '%' : '-' }}
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ url('/app/rekap-absensi/siswa/' . $siswa->id) }}" class="btn-compact bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-colors text-[11px] font-bold px-2.5 py-1 inline-flex items-center justify-center rounded-lg">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 flex items-center justify-between no-print">
            <span class="text-xs text-slate-400">Menampilkan {{ $siswas->firstItem() }} - {{ $siswas->lastItem() }} dari {{ $siswas->total() }} data siswa</span>
            {{ $siswas->links() }}
        </div>
    @endif
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    .no-print, nav, aside, header { display: none !important; }
    body { background: white !important; font-size: 11pt !important; }
    .shadow-sm, .shadow-md, .shadow-xl { box-shadow: none !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; }
}
</style>

@endsection
