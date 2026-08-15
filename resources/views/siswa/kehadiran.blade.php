@extends('layouts.app')
@section('title', 'Kehadiran Saya')
@section('page_title', 'Kehadiran Saya')
@section('content')

<div class="space-y-6">

    <!-- A. IDENTITAS SISWA -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[11px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-md border border-blue-200">
                    Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
                </span>
                <span class="text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-md border border-slate-200">
                    NIS: {{ $siswa->nis ?? '-' }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-800">{{ Auth::user()->name }}</h1>
            <p class="text-xs text-slate-500 font-medium">Buku Presensi Digital Siswa</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 px-4 py-2.5 rounded-xl flex items-center gap-3 w-full md:w-auto">
            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-lg flex-shrink-0 font-bold">
                💻
            </div>
            <div>
                <span class="text-[10px] text-blue-600 font-bold uppercase block">Mata Pelajaran</span>
                <span class="text-sm font-black text-blue-900 block">Informatika</span>
            </div>
        </div>
    </div>

    <!-- B. REKAP KEHADIRAN -->
    <div>
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>📊</span> Rekap Kehadiran Presensi
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            
            <!-- Hadir (Hijau) -->
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-bold text-green-700 uppercase tracking-wider block mb-1">Hadir</span>
                <span class="text-3xl font-black text-green-800 block">{{ $rekap['hadir'] }}</span>
                <span class="text-[10px] text-green-600 font-medium mt-1 block">Pertemuan</span>
            </div>

            <!-- Sakit (Kuning) -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-bold text-yellow-700 uppercase tracking-wider block mb-1">Sakit</span>
                <span class="text-3xl font-black text-yellow-800 block">{{ $rekap['sakit'] }}</span>
                <span class="text-[10px] text-yellow-600 font-medium mt-1 block">Hari</span>
            </div>

            <!-- Izin (Biru) -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-bold text-blue-700 uppercase tracking-wider block mb-1">Izin</span>
                <span class="text-3xl font-black text-blue-800 block">{{ $rekap['izin'] }}</span>
                <span class="text-[10px] text-blue-600 font-medium mt-1 block">Hari</span>
            </div>

            <!-- Dispen (Ungu) -->
            <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-bold text-purple-700 uppercase tracking-wider block mb-1">Dispen</span>
                <span class="text-3xl font-black text-purple-800 block">{{ $rekap['dispen'] }}</span>
                <span class="text-[10px] text-purple-600 font-medium mt-1 block">Hari</span>
            </div>

            <!-- Alpa (Merah) -->
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-bold text-red-700 uppercase tracking-wider block mb-1">Alpa</span>
                <span class="text-3xl font-black text-red-800 block">{{ $rekap['alpa'] }}</span>
                <span class="text-[10px] text-red-600 font-medium mt-1 block">Hari</span>
            </div>

            <!-- Total Pertemuan (Slate) -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center col-span-2 sm:col-span-1">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Total Presensi</span>
                <span class="text-3xl font-black text-slate-800 block">{{ $rekap['total'] }}</span>
                <span class="text-[10px] text-slate-500 font-medium mt-1 block">Tercatat</span>
            </div>

        </div>
    </div>

    <!-- C. TABEL KEHADIRAN (READ-ONLY BUKU ABSENSI) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span>📋</span> Detail Riwayat Kehadiran
            </h3>
            <span class="text-xs text-slate-500 font-medium">Read-Only</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 min-w-[140px]">Tanggal</th>
                        <th class="py-3 px-4 min-w-[120px]">Pertemuan</th>
                        <th class="py-3 px-4 min-w-[100px] text-center">Status</th>
                        <th class="py-3 px-4 min-w-[180px]">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($absensis as $index => $absen)
                    @php
                        $tglKey = \Carbon\Carbon::parse($absen->tanggal)->format('Y-m-d');
                        $ph = $phMap[$tglKey] ?? null;
                        $st = strtolower(trim($absen->status));
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-slate-500">
                            {{ $absensis->firstItem() + $index }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($absen->tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-600">
                            @if($ph && $ph->pertemuan)
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-mono text-[11px] font-semibold border border-slate-200">
                                    Ke-{{ $ph->pertemuan }}
                                </span>
                            @else
                                <span class="text-slate-400 font-mono">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($st === 'hadir')
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 border border-green-300 font-black rounded-full text-[11px] shadow-2xs">
                                    Hadir
                                </span>
                            @elseif($st === 'sakit')
                                <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 font-black rounded-full text-[11px] shadow-2xs">
                                    Sakit
                                </span>
                            @elseif($st === 'izin')
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 border border-blue-300 font-black rounded-full text-[11px] shadow-2xs">
                                    Izin
                                </span>
                            @elseif($st === 'alpha' || $st === 'alpa')
                                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 border border-red-300 font-black rounded-full text-[11px] shadow-2xs">
                                    Alpa
                                </span>
                            @elseif($st === 'dispen')
                                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-800 border border-purple-300 font-black rounded-full text-[11px] shadow-2xs">
                                    Dispen
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 border border-slate-300 font-bold rounded-full text-[11px]">
                                    {{ ucfirst($absen->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            {{ $ph->catatan ?? $absen->catatan ?? $absen->keterangan ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 px-4 text-center text-slate-400 font-medium">
                            Belum ada catatan presensi/kehadiran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- OPTIMASI: PAGINATION LINKS -->
        @if($absensis->hasPages())
        <div class="p-4 bg-slate-50 border-t border-slate-200">
            {{ $absensis->links() }}
        </div>
        @endif
    </div>

</div>

@endsection
