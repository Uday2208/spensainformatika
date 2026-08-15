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

<!-- Filter Controls -->
<div class="mb-4">
    <div class="bg-white rounded border border-slate-200 shadow-sm p-4">
        <h3 class="font-bold text-slate-700 text-sm mb-3 border-b border-slate-100 pb-2">Filter Berdasarkan Kelas</h3>
        <form action="{{ url('/app/rekap-jurnal') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3 max-w-lg">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Kelas</label>
                <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm w-full sm:w-auto h-[30px]">Tampilkan</button>
        </form>
    </div>
</div>

<!-- Table Data -->
<div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 w-16 text-center">No</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Pertemuan</th>
                    <th class="px-4 py-3">Materi / Tujuan</th>
                    <th class="px-4 py-3">Kegiatan</th>
                    <th class="px-4 py-3">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($jurnals as $index => $j)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-center text-slate-500">{{ $jurnals->firstItem() + $index }}</td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d M Y') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">{{ $j->kelas->nama_kelas ?? 'N/A' }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        Ke-{{ $j->pertemuan }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[200px]">
                            <p class="font-semibold text-slate-700">{{ $j->materi ?: '-' }}</p>
                            @if($j->tujuan_pembelajaran)
                                <p class="text-xs text-slate-500 mt-1">{{ $j->tujuan_pembelajaran }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[200px] text-slate-600">
                            {{ $j->kegiatan ?: '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="whitespace-normal min-w-[150px] text-slate-500 italic">
                            {{ $j->catatan ?: '-' }}
                        </div>
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
