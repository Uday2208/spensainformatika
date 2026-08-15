@extends('layouts.app')
@section('title', 'Monitoring Ujian')
@section('page_title', 'Monitoring Ujian')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200 shadow-sm">
    {{ session('success') }}
</div>
@endif

<div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <h2 class="text-lg font-bold text-slate-800">{{ $ujian->judul }}</h2>
        @if($ujian->isAktif())
        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full border border-green-200 animate-pulse">AKTIF</span>
        @else
        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-200">SELESAI</span>
        @endif
    </div>
    <div class="flex items-center gap-2">
        @if($ujian->isAktif())
        <span class="text-sm font-semibold text-slate-500">Token: <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-1 rounded border border-slate-200">{{ $ujian->token }}</span></span>
        @endif
        <a href="{{ route('guru.ujian.index') }}" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs py-1.5 px-3 rounded">Kembali</a>
    </div>
</div>

<div x-data="{ activeTab: 'peserta' }">
    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-4">
        <button @click="activeTab = 'peserta'" :class="activeTab === 'peserta' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-2.5 text-sm transition-all focus:outline-none">
            Status Peserta ({{ $hasilUjians->count() }})
        </button>
        <button @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-2.5 text-sm transition-all focus:outline-none">
            Log Aktivitas / Kecurangan ({{ $logs->count() }})
        </button>
    </div>

    <!-- Tab: Status Peserta -->
    <div x-show="activeTab === 'peserta'" class="bg-white rounded border border-slate-200 shadow-sm p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 text-xs uppercase font-bold border-b border-slate-200">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4 text-center">Kelas</th>
                        <th class="py-3 px-4 text-center">Mulai</th>
                        <th class="py-3 px-4 text-center">Selesai</th>
                        <th class="py-3 px-4 text-center">Jawaban Disimpan</th>
                        <th class="py-3 px-4 text-center">Peringatan Tab</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Skor PG</th>
                        <th class="py-3 px-4 text-center">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($hasilUjians as $index => $hasil)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 text-center text-slate-500">{{ $hasilUjians->firstItem() + $index }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $hasil->siswa->user->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-center text-slate-600">{{ $hasil->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="py-3 px-4 text-center text-slate-600 font-mono text-xs">{{ $hasil->started_at ? \Carbon\Carbon::parse($hasil->started_at)->format('H:i:s') : '-' }}</td>
                        <td class="py-3 px-4 text-center text-slate-600 font-mono text-xs">{{ $hasil->finished_at ? \Carbon\Carbon::parse($hasil->finished_at)->format('H:i:s') : '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="font-bold text-slate-700">{{ $hasil->jawaban_count }}</span>
                            <span class="text-slate-400">/ {{ $totalSoal }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $hasil->tab_switch_count > 3 ? 'bg-red-100 text-red-700 border border-red-200' : ($hasil->tab_switch_count > 0 ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500') }}">
                                {{ $hasil->tab_switch_count }}x
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($hasil->status === 'mengerjakan')
                            <span class="inline-block px-2.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full border border-blue-200 animate-pulse">Mengerjakan</span>
                            @elseif($hasil->status === 'selesai')
                            <span class="inline-block px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full border border-indigo-200">Selesai (Koreksi)</span>
                            @else
                            <span class="inline-block px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">Dinilai</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $hasil->nilai_pg }}</td>
                        <td class="py-3 px-4 text-center font-bold text-lg text-blue-700 bg-blue-50/20">{{ $hasil->nilai_akhir }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-slate-500 italic">Belum ada siswa memulai ujian ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $hasilUjians->links() }}
        </div>
    </div>

    <!-- Tab: Log Aktivitas -->
    <div x-show="activeTab === 'logs'" x-cloak class="bg-white rounded border border-slate-200 shadow-sm p-4">
        <div class="max-h-[500px] overflow-y-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 text-xs uppercase font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4 text-center">Event</th>
                        <th class="py-3 px-4">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition-colors {{ in_array($log->event, ['tab_switch', 'minimize', 'blur', 'keluar']) ? 'bg-red-50/30' : '' }}">
                        <td class="py-3 px-4 text-slate-500 font-mono text-xs w-40">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $log->siswa->user->name ?? '-' }} ({{ $log->siswa->kelas->nama_kelas ?? '-' }})</td>
                        <td class="py-3 px-4 text-center">
                            @if(in_array($log->event, ['tab_switch', 'minimize', 'blur', 'keluar']))
                            <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded border border-red-200">CURIGA</span>
                            @elseif($log->event === 'mulai')
                            <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded border border-green-200">MULAI</span>
                            @else
                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded border border-slate-200">INFO</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-semibold">{{ $log->detail }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-500 italic">Belum ada log aktivitas masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
