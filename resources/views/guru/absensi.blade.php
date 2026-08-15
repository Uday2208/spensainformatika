@extends('layouts.app')
@section('title', 'Input Presensi')
@section('page_title', '📝 Input Presensi')
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
        <h2 class="text-white font-bold text-base leading-tight">Halaman Input & Koreksi Presensi</h2>
        <p class="text-blue-200 text-xs mt-1 leading-relaxed">
            Halaman ini digunakan untuk <strong class="text-amber-300">mencatat kehadiran siswa</strong> kelas Anda, baik input baru maupun mengedit data absensi hari sebelumnya.
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

{{-- ============================================================
     FILTER FORM (GET — mengubah tampilan tanpa submit absensi)
     ============================================================ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-5">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-6 h-6 bg-slate-100 rounded-lg flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        </div>
        <h3 class="font-bold text-slate-700 text-sm">Filter Tampilan</h3>
    </div>
    <form action="{{ url('/app/absensi') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Filter Kelas</label>
            <select name="kelas_id" id="kelasFilter"
                class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all cursor-pointer min-h-[44px]">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Tanggal Absensi</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all min-h-[44px]">
        </div>
        <div class="flex items-end">
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-500/25 transition-all min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Tampilkan
            </button>
        </div>
    </form>
</div>

{{-- ============================================================
     STATUS INDICATOR: Sudah / Belum Terisi
     ============================================================ --}}
@if(!$kelas_id)
<div class="p-8 bg-blue-50 border border-blue-200 rounded-2xl shadow-sm text-center flex flex-col items-center justify-center">
    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <h3 class="text-base font-bold text-blue-800 mb-1">Silakan Pilih Kelas Terlebih Dahulu</h3>
    <p class="text-xs text-blue-700 max-w-md leading-relaxed">
        Untuk melakukan pengisian presensi siswa, pilih salah satu kelas pada filter di atas lalu klik tombol <strong>Tampilkan</strong>.
    </p>
</div>
@else

@if($sudahDiisi)
<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 mb-4">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
    </div>
    <div>
        <p class="font-bold text-emerald-800 text-sm">Data absensi <span class="font-black">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</span> sudah ada.</p>
        <p class="text-emerald-700 text-xs">Anda bisa mengubah status kehadiran di bawah, lalu klik <strong>Simpan Presensi</strong>.</p>
    </div>
</div>
@else
<div class="flex items-center justify-between gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 mb-4">
    <div class="flex items-center gap-3">
        <div class="w-7 h-7 bg-amber-400 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <p class="font-bold text-amber-800 text-sm">Belum ada data absensi untuk tanggal ini.</p>
            <p class="text-amber-700 text-xs">Silakan tentukan status kehadiran siswa kelas Anda di bawah, lalu klik <strong>Simpan Presensi</strong>.</p>
        </div>
    </div>
</div>
@endif

{{-- ============================================================
     FORM KOREKSI ABSENSI
     ============================================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ url('/app/absensi') }}" method="POST" id="absensiForm">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        {{-- Header Tabel --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-700 text-sm">
                    Daftar Siswa
                    @if($kelas_id)
                        — <span class="text-blue-600">{{ $kelas->find($kelas_id)->nama_kelas ?? '' }}</span>
                    @else
                        <span class="text-slate-400 font-normal">(Semua Kelas)</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
                    · {{ $siswas->count() }} siswa
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="setAll('hadir')"
                    class="flex items-center gap-1.5 px-3 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 transition-all active:scale-95">
                    ✅ Set Semua Hadir
                </button>
                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/25 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Presensi
                </button>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="w-10 text-center px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">No</th>
                        <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">NIS</th>
                        <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Nama Siswa</th>
                        <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Kelas</th>
                        <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide text-center w-56">Status Kehadiran</th>
                        <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide text-center w-24">Data di DB</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $index => $siswa)
                    @php
                        $existingStatus = $existingAbsensi->get($siswa->id, 'hadir');
                        $adaData = $existingAbsensi->has($siswa->id);
                        $statusColor = [
                            'hadir'  => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            'sakit'  => 'text-amber-700 bg-amber-50 border-amber-200',
                            'izin'   => 'text-blue-700 bg-blue-50 border-blue-200',
                            'dispen' => 'text-purple-700 bg-purple-50 border-purple-200',
                            'alpha'  => 'text-red-700 bg-red-50 border-red-200',
                        ][$existingStatus] ?? 'text-slate-700 bg-slate-50 border-slate-200';
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors siswa-row {{ $adaData ? 'bg-white' : 'bg-slate-50/30' }}"
                        data-kelas="{{ $siswa->kelas_id }}">
                        <td class="text-center text-slate-400 text-sm px-3 py-3">{{ $index + 1 }}</td>
                        <td class="font-mono text-slate-600 text-xs px-3 py-3">{{ $siswa->nis }}</td>
                        <td class="font-semibold text-slate-800 text-sm px-3 py-3">{{ $siswa->user->name ?? '-' }}</td>
                        <td class="text-slate-500 text-xs px-3 py-3">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <select name="absensi[{{ $siswa->id }}]"
                                class="w-full text-sm font-semibold bg-white border-2 rounded-xl px-3 py-2 focus:outline-none transition-all cursor-pointer absensi-select min-h-[40px]
                                       {{ $adaData ? $statusColor . ' border-2' : 'border-slate-200 text-slate-500' }}"
                                onchange="updateRowColor(this)">
                                <option value="hadir"  {{ $existingStatus === 'hadir'  ? 'selected' : '' }}>✅ Hadir</option>
                                <option value="sakit"  {{ $existingStatus === 'sakit'  ? 'selected' : '' }}>🤒 Sakit</option>
                                <option value="izin"   {{ $existingStatus === 'izin'   ? 'selected' : '' }}>📝 Izin</option>
                                <option value="dispen" {{ $existingStatus === 'dispen' ? 'selected' : '' }}>🎫 Dispen</option>
                                <option value="alpha"  {{ $existingStatus === 'alpha'  ? 'selected' : '' }}>❌ Alpha</option>
                            </select>
                        </td>
                        <td class="text-center px-3 py-3">
                            @if($adaData)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusColor }} border">
                                    ✓ Terisi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-400 rounded-full text-[10px] font-bold border border-slate-200">
                                    — Kosong
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="font-medium">Belum ada data siswa terdaftar.</p>
                            <a href="{{ url('/app/data-siswa') }}" class="text-blue-500 text-sm hover:underline mt-1 block">→ Tambah Data Siswa</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </form>

        @if($siswas->count() > 0)
        {{-- Footer Hapus Data --}}
        <div class="px-5 py-4 border-t border-red-100 bg-red-50/50">
            <details class="group">
                <summary class="flex items-center gap-2 cursor-pointer text-xs font-bold text-red-500 list-none select-none hover:text-red-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    🗑️ Zona Bahaya: Hapus Semua Data Absensi Tanggal Ini
                    <svg class="w-3 h-3 ml-auto group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </summary>
                <form action="{{ url('/app/absensi/delete-by-date') }}" method="POST"
                    class="flex flex-col sm:flex-row items-end gap-3 mt-4"
                    onsubmit="return confirm('⚠️ YAKIN ingin menghapus SELURUH data absensi tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }} untuk kelas yang dipilih? Tindakan ini tidak dapat dibatalkan!');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="tanggal_hapus" value="{{ $tanggal }}">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-red-500 mb-1">Filter Kelas yang Dihapus</label>
                        <select name="kelas_id_hapus" class="w-full text-sm bg-white border border-red-200 rounded-xl px-3 py-2 focus:outline-none focus:border-red-400 cursor-pointer min-h-[40px]">
                            <option value="all">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2 bg-red-600 hover:bg-red-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-red-500/20 transition-all min-h-[40px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Data
                    </button>
                </form>
            </details>
        </div>
        @endif
    </div>

<script>
    const colorMap = {
        hadir:  'text-emerald-700 bg-emerald-50 border-emerald-300',
        sakit:  'text-amber-700 bg-amber-50 border-amber-300',
        izin:   'text-blue-700 bg-blue-50 border-blue-300',
        dispen: 'text-purple-700 bg-purple-50 border-purple-300',
        alpha:  'text-red-700 bg-red-50 border-red-300',
    };
    const allClasses = Object.values(colorMap).join(' ').split(' ');

    function updateRowColor(select) {
        const val = select.value;
        // reset then apply
        allClasses.forEach(c => select.classList.remove(c));
        if (colorMap[val]) {
            colorMap[val].split(' ').forEach(c => select.classList.add(c));
        }
    }

    function setAll(status) {
        document.querySelectorAll('.absensi-select').forEach(select => {
            select.value = status;
            updateRowColor(select);
        });
    }

    // Init colors on load
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.absensi-select').forEach(select => {
            updateRowColor(select);
        });
    });
</script>

@endif

@endsection
