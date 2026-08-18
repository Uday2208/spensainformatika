@extends('layouts.app')
@section('title', 'Rekap Jurnal Harian')
@section('page_title', 'Rekap Jurnal Harian')
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

<!-- Filter Controls & Actions -->
<div class="mb-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <form action="{{ url('/app/rekap-jurnal') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3 w-full md:max-w-md">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Filter Berdasarkan Kelas</label>
                <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            @if($kelas_id)
                <a href="{{ url('/app/rekap-jurnal') }}" class="btn-compact bg-slate-200 text-slate-700 hover:bg-slate-300 h-[30px] px-3 flex items-center justify-center">✕</a>
            @endif
        </form>

        <div class="flex items-center gap-2 w-full md:w-auto no-print">
            <a href="{{ route('guru.rekap-jurnal.export', ['kelas_id' => $kelas_id]) }}" class="btn-compact bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm text-xs px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </a>
            <button onclick="window.print()" class="btn-compact bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center shadow-sm text-xs px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak
            </button>
        </div>
    </div>
</div>

<!-- Table Data -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 w-12 text-center">No</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3 text-center">Pertemuan</th>
                    <th class="px-4 py-3">Materi / Topik</th>
                    <th class="px-4 py-3">Kegiatan Pembelajaran</th>
                    <th class="px-4 py-3">Catatan / Tindak Lanjut</th>
                    <th class="px-4 py-3 text-center w-16 no-print">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($jurnals as $index => $j)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-center text-slate-500 font-medium">{{ $jurnals->firstItem() + $index }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-700">
                        {{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d F Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[11px]">{{ $j->kelas->nama_kelas ?? 'N/A' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-slate-700">
                        Ke-{{ $j->pertemuan }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[200px]">
                            <p class="font-bold text-slate-900">{{ $j->materi ?: '-' }}</p>
                            @if($j->tujuan_pembelajaran)
                                <p class="text-[11px] text-slate-500 mt-0.5">🎯 {{ $j->tujuan_pembelajaran }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[200px] text-slate-600">
                            {{ $j->kegiatan ?: '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[150px] text-slate-500">
                            @if($j->catatan)
                                <p class="text-xs text-amber-700">📝 {{ $j->catatan }}</p>
                            @endif
                            @if($j->tindak_lanjut)
                                <p class="text-[11px] text-indigo-600 mt-0.5">➡️ {{ $j->tindak_lanjut }}</p>
                            @endif
                            @if(!$j->catatan && !$j->tindak_lanjut)
                                <span>-</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center no-print">
                        <form action="{{ route('guru.jurnal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jurnal pertemuan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Hapus Jurnal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p class="text-base font-medium text-slate-600">Belum ada jurnal mengajar</p>
                        <p class="text-xs text-slate-400 mt-1">Data jurnal akan muncul setelah Anda menginput pembelajaran.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100">
        {{ $jurnals->links() }}
    </div>
</div>

@endsection
