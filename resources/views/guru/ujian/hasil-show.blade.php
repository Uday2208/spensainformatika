@extends('layouts.app')
@section('title', 'Rekap Hasil & Koreksi Siswa')
@section('page_title', 'Rekap Hasil & Koreksi Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3.5 rounded-xl text-sm border border-green-200 shadow-sm flex items-center gap-2">
    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3.5 rounded-xl text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p>• {{ $error }}</p>
    @endforeach
</div>
@endif

<!-- Exam Summary Card -->
<div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs bg-slate-100 text-slate-600 font-extrabold px-2.5 py-0.5 rounded-md border border-slate-200 uppercase">{{ $ujian->bab }}</span>
            <span class="text-xs text-slate-400 font-semibold">KKM: {{ $kkm }}</span>
        </div>
        <h2 class="text-xl font-black text-slate-800">{{ $ujian->judul }}</h2>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
        @if($hasEssay && $ujian->isSelesai())
        <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-[0.98] flex items-center gap-1.5 border border-indigo-700">
            ✍️ Koreksi Essay Massal
        </a>
        @endif

        @if($ujian->isSelesai())
        <form action="{{ route('guru.ujian.finalisasi', $ujian->id) }}" method="POST" onsubmit="return confirm('Simpan & publish nilai ulangan ini ke Rekap Nilai Utama?');">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-[0.98] border border-emerald-700 flex items-center gap-1.5">
                📌 Finalisasi & Push ke Rekap Nilai
            </button>
        </form>
        @endif

        <a href="{{ route('guru.hasil.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2.5 rounded-xl text-sm transition-all">
            ← Kembali
        </a>
    </div>
</div>

<!-- Student Results Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5" x-data="{ selectedKelas: '{{ $selectedKelas }}' }">
    <div class="flex flex-wrap justify-between items-center pb-4 mb-4 border-b border-slate-100 gap-4">
        <div>
            <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                <span>📋 Hasil & Lembar Pengerjaan Siswa</span>
                <span class="text-xs bg-blue-100 text-blue-800 font-extrabold px-2.5 py-0.5 rounded-full border border-blue-200">Total: {{ $hasilUjians->count() }}</span>
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Filter dan periksa hasil pengerjaan ujian siswa per rombel / kelas.</p>
        </div>

        <!-- Filter Kelas -->
        <div class="flex items-center gap-2">
            <label for="filter_kelas" class="text-xs font-extrabold text-slate-600 whitespace-nowrap flex items-center gap-1.5 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Kelas:
            </label>
            <select id="filter_kelas" x-model="selectedKelas" @change="window.location.href = '{{ route('guru.hasil.show', $ujian->id) }}?kelas_id=' + selectedKelas" class="text-xs font-extrabold text-slate-800 bg-white border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ $selectedKelas == $k->id ? 'selected' : '' }}>Kelas {{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            @if($selectedKelas)
            <a href="{{ route('guru.hasil.show', $ujian->id) }}" class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-2 rounded-xl transition-colors flex items-center gap-1" title="Reset Filter">
                ✕ Reset
            </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-extrabold border-b border-slate-200">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4 text-center">Skor PG</th>
                    <th class="py-3 px-4 text-center">Skor Essay</th>
                    <th class="py-3 px-4 text-center">Nilai Akhir</th>
                    <th class="py-3 px-4 text-center">Status KKM</th>
                    <th class="py-3 px-4 text-center">Deteksi Tab Switch</th>
                    <th class="py-3 px-4 text-center w-36">Aksi Inspeksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium">
                @forelse($hasilUjians as $index => $h)
                <tr class="hover:bg-slate-50/80 transition-colors hasil-row" data-kelas="{{ $h->siswa->kelas_id }}" x-show="!selectedKelas || selectedKelas == '{{ $h->siswa->kelas_id }}'">
                    <td class="py-4 px-4 text-center text-slate-400 font-bold text-xs">{{ $index + 1 }}</td>
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-extrabold text-slate-800">{{ $h->siswa->user->name ?? '-' }}</span>
                            @if($h->status !== 'dinilai')
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md border border-amber-200 uppercase animate-pulse">⏳ Belum Dikoreksi</span>
                            @else
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md border border-slate-200 uppercase">✓ Sudah Dinilai</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 font-mono block mt-0.5">NIS: {{ $h->siswa->nis ?? '-' }}</span>
                    </td>
                    <td class="py-4 px-4 text-xs font-bold text-slate-600">
                        {{ $h->siswa->kelas->nama_kelas ?? '-' }}
                    </td>
                    <td class="py-4 px-4 text-center font-extrabold text-slate-700">
                        {{ number_format($h->nilai_pg, 1) }}
                    </td>
                    <td class="py-4 px-4 text-center font-extrabold text-indigo-700">
                        {{ number_format($h->nilai_essay, 1) }}
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span class="text-base font-black px-3 py-1 rounded-xl border {{ $h->nilai_akhir >= $kkm ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                            {{ number_format($h->nilai_akhir, 1) }}
                        </span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        @if($h->nilai_akhir >= $kkm)
                        <span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full border border-green-200">LULUS</span>
                        @else
                        <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded-full border border-red-200">REMEDIAL</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center">
                        @if($h->tab_switch_count > 0)
                        <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-black rounded-lg border border-red-200 flex items-center justify-center gap-1">
                            ⚠️ {{ $h->tab_switch_count }}x Switch
                        </span>
                        @else
                        <span class="text-xs text-slate-400 font-semibold">Aman (0x)</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center">
                        <a href="{{ route('guru.hasil.detail-siswa', [$ujian->id, $h->siswa_id]) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-extrabold text-xs py-1.5 px-3 rounded-xl border border-blue-200 transition-all inline-block shadow-sm">
                            🔍 Periksa Jawaban
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 text-center text-slate-400 text-sm font-semibold">
                        Belum ada siswa yang mengerjakan atau menyelesaikan ujian ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
