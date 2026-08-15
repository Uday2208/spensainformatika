@extends('layouts.app')
@section('title', 'Kelola Ujian Harian')
@section('page_title', 'Kelola Ujian Harian')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200 shadow-sm">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="bg-white rounded border border-slate-200 shadow-sm p-4">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div class="flex items-center gap-2">
            <form action="{{ route('guru.ujian.index') }}" method="GET" class="flex gap-2">
                <select name="status" onchange="this.form.submit()" class="input-compact bg-slate-50 w-40 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>
        </div>
        <a href="{{ route('guru.ujian.create') }}" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-1.5 shadow-sm text-xs py-2 px-3 rounded-lg font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Ujian Baru
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-compact">
            <thead>
                <tr class="bg-slate-50 text-slate-700 text-xs uppercase font-bold border-b border-slate-200">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4">Judul Ujian</th>
                    <th class="py-3 px-4">Bab</th>
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4">Durasi</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Token</th>
                    <th class="py-3 px-4 text-center w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($ujians as $index => $u)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4 text-center text-slate-500">{{ $index + 1 }}</td>
                    <td class="py-3 px-4 font-semibold text-slate-800">{{ $u->judul }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $u->bab }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $u->tanggal ? \Carbon\Carbon::parse($u->tanggal)->translatedFormat('d M Y') : '-' }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $u->durasi }} Menit</td>
                    <td class="py-3 px-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($u->kelasList as $kelas)
                            <span class="inline-block px-1.5 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold rounded border border-blue-100">{{ $kelas->nama_kelas }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($u->isDraft())
                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full border border-slate-200">Draft</span>
                        @elseif($u->isAktif())
                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full border border-green-200">Aktif</span>
                        @else
                        <span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full border border-amber-200">Selesai</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($u->isAktif())
                        <span class="font-mono bg-slate-100 px-2 py-1 rounded text-slate-800 font-bold border border-slate-200 text-xs select-all">{{ $u->token }}</span>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($u->isDraft())
                            <a href="{{ route('guru.ujian.show', $u->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 py-1 px-2.5 rounded border border-blue-200 transition-colors">
                                Soal & Detail
                            </a>
                            <form action="{{ route('guru.ujian.activate', $u->id) }}" method="POST" onsubmit="return confirm('Aktifkan ujian ini? Token akan digenerate dan siswa bisa mulai mengerjakan.');" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold text-xs py-1 px-2.5 rounded border border-green-700 transition-colors shadow-sm">
                                    Aktifkan
                                </button>
                            </form>
                            <form action="{{ route('guru.ujian.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus ujian ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-xs bg-red-50 hover:bg-red-100 p-1 rounded border border-red-200 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @elseif($u->isAktif())
                            <a href="{{ route('guru.ujian.monitoring', $u->id) }}" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs py-1 px-2.5 rounded border border-sky-700 transition-colors shadow-sm flex items-center gap-1">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                </span>
                                Monitor
                            </a>
                            <form action="{{ route('guru.ujian.finish', $u->id) }}" method="POST" onsubmit="return confirm('Akhiri ujian ini? Siswa yang sedang mengerjakan akan otomatis dikumpulkan.');" class="inline">
                                @csrf
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs py-1 px-2.5 rounded border border-amber-700 transition-colors shadow-sm">
                                    Akhiri
                                </button>
                            </form>
                            @else
                            <a href="{{ route('guru.ujian.show', $u->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 py-1 px-2.5 rounded border border-blue-200 transition-colors">
                                ⚙️ Setting / Detail
                            </a>
                            <a href="{{ route('guru.ujian.monitoring', $u->id) }}" class="text-slate-600 hover:text-slate-800 font-semibold text-xs bg-slate-100 hover:bg-slate-200 py-1 px-2.5 rounded border border-slate-300 transition-colors">
                                Hasil
                            </a>
                            @if($u->soals->where('tipe', 'essay')->count() > 0)
                            <a href="{{ route('guru.ujian.koreksi', $u->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs py-1 px-2.5 rounded border border-indigo-700 transition-colors shadow-sm">
                                Koreksi Essay
                            </a>
                            @endif
                            <form action="{{ route('guru.ujian.finalisasi', $u->id) }}" method="POST" onsubmit="return confirm('Finalisasi nilai ujian? Nilai akan masuk ke Rekap Nilai Akhir.');" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-1 px-2.5 rounded border border-blue-700 transition-colors shadow-sm">
                                    Finalisasi
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-8 text-slate-500 italic">Belum ada data ujian harian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $ujians->links() }}
    </div>
</div>

@endsection
