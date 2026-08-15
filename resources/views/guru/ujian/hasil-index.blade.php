@extends('layouts.app')
@section('title', 'Hasil & Koreksi Ujian')
@section('page_title', 'Hasil & Koreksi Ujian')
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

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <!-- Header Filter & Info -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 pb-4 border-b border-slate-100">
        <div>
            <h2 class="text-lg font-black text-slate-800">Daftar Hasil & Koreksi Ujian</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih paket ujian di bawah ini untuk memeriksa lembar jawaban siswa dan menginput nilai essay.</p>
        </div>
        <form action="{{ route('guru.hasil.index') }}" method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-extrabold border-b border-slate-200">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4">Judul Ujian & Bab</th>
                    <th class="py-3 px-4">Tanggal & Durasi</th>
                    <th class="py-3 px-4">Kelas Peserta</th>
                    <th class="py-3 px-4 text-center">Peserta Selesai</th>
                    <th class="py-3 px-4 text-center">Status Ujian</th>
                    <th class="py-3 px-4 text-center w-44">Aksi / Koreksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium">
                @forelse($ujians as $index => $u)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-4 text-center text-slate-400 font-bold text-xs">{{ $index + 1 }}</td>
                    <td class="py-4 px-4">
                        <span class="font-extrabold text-slate-800 block leading-tight">{{ $u->judul }}</span>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold uppercase inline-block mt-1 border border-slate-200">{{ $u->bab }}</span>
                    </td>
                    <td class="py-4 px-4 text-xs text-slate-600">
                        <div>📅 {{ $u->tanggal ? \Carbon\Carbon::parse($u->tanggal)->translatedFormat('d M Y') : '-' }}</div>
                        <div class="text-slate-400 font-semibold mt-0.5">⏱️ {{ $u->durasi }} Menit</div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($u->kelasList as $kelas)
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-800 text-[11px] font-black rounded-md border border-blue-100">{{ $kelas->nama_kelas }}</span>
                            @empty
                            <span class="text-xs text-slate-400 font-normal italic">Belum diaktifkan</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span class="px-3 py-1 bg-slate-100 text-slate-800 rounded-xl font-black text-xs border border-slate-200">
                            {{ $u->selesai_count }} Siswa
                        </span>
                        @if($u->perlu_koreksi)
                        <span class="block text-[10px] text-amber-700 font-bold mt-1 animate-pulse">
                            ⚠️ Perlu Koreksi Essay
                        </span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center">
                        @if($u->isDraft())
                        <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-full border border-slate-200">Draft</span>
                        @elseif($u->isAktif())
                        <span class="inline-block px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">Aktif</span>
                        @else
                        <span class="inline-block px-2.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full border border-amber-200">Selesai</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center">
                        <a href="{{ route('guru.hasil.show', $u->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs py-2 px-3.5 rounded-xl shadow-md transition-all active:scale-[0.98] inline-flex items-center gap-1.5 border border-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Periksa Hasil ➔
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-400 text-sm font-semibold">
                        Belum ada paket ujian. Silakan buat ujian baru terlebih dahulu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
