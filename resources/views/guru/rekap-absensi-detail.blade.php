@extends('layouts.app')
@section('title', 'Detail Rekap Kehadiran')
@section('page_title', 'Detail Rekap Kehadiran')
@section('content')

<div class="mb-4">
    <a href="{{ url('/app/rekap-absensi') }}" class="btn-compact bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Rekap
    </a>
</div>

<div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 mb-1">{{ $siswa->user->name ?? 'Tanpa Nama' }}</h2>
            <div class="flex items-center gap-4 text-sm text-slate-500 font-medium">
                <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg> NIS: {{ $siswa->nis }}</span>
                <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</span>
            </div>
        </div>
        <div>
            <a href="{{ url('/app/rekap-absensi/siswa/' . $siswa->id . '/export') }}" class="btn-compact bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm w-full sm:w-auto px-4 py-2 rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV Rincian
            </a>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 border-b border-slate-100 divide-x divide-y md:divide-y-0 divide-slate-100">
        @php
            $hadir = $siswa->absensis->where('status', 'hadir')->count();
            $sakit = $siswa->absensis->where('status', 'sakit')->count();
            $izin = $siswa->absensis->where('status', 'izin')->count();
            $dispen = $siswa->absensis->where('status', 'dispen')->count();
            $alpha = $siswa->absensis->where('status', 'alpha')->count();
        @endphp
        <div class="p-4 text-center">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Hadir</p>
            <p class="text-2xl font-bold text-green-600">{{ $hadir }}</p>
        </div>
        <div class="p-4 text-center">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sakit</p>
            <p class="text-2xl font-bold text-blue-600">{{ $sakit }}</p>
        </div>
        <div class="p-4 text-center">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Izin</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $izin }}</p>
        </div>
        <div class="p-4 text-center">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dispen</p>
            <p class="text-2xl font-bold text-purple-600">{{ $dispen }}</p>
        </div>
        <div class="p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alpha</p>
            <p class="text-2xl font-bold text-red-600">{{ $alpha }}</p>
        </div>
    </div>

    <!-- Details Table -->
    <div class="p-0">
        @if($siswa->absensis->isEmpty())
            <div class="p-10 text-center text-slate-500 italic">
                Belum ada data kehadiran untuk siswa ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-compact">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="w-16 text-center border-y border-slate-200">No</th>
                            <th class="border-y border-slate-200">Tanggal</th>
                            <th class="border-y border-slate-200">Hari</th>
                            <th class="border-y border-slate-200 w-48 text-center">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($siswa->absensis as $index => $absensi)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="text-center text-slate-500">{{ $index + 1 }}</td>
                                <td class="font-medium text-slate-700">
                                    {{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d F Y') }}
                                </td>
                                <td class="text-slate-500 text-sm">
                                    {{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('l') }}
                                </td>
                                <td class="text-center">
                                    @if($absensi->status == 'hadir')
                                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Hadir</span>
                                    @elseif($absensi->status == 'sakit')
                                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">Sakit</span>
                                    @elseif($absensi->status == 'izin')
                                        <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">Izin</span>
                                    @elseif($absensi->status == 'dispen')
                                        <span class="inline-block px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-full">Dispen</span>
                                    @else
                                        <span class="inline-block px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
