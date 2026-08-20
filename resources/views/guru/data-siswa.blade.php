@extends('layouts.app')
@section('title', 'Data Siswa')
@section('page_title', '👥 Manajemen Data Siswa')
@section('content')

{{-- ============================================================
     BANNER INFO UTAMA
     ============================================================ --}}
<div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-5 mb-5 shadow-xl relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-24 translate-x-24"></div>
    </div>
    <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg relative text-white font-bold text-xl">
        👥
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base leading-tight">Manajemen & Master Data Siswa</h2>
        <p class="text-blue-200 text-xs mt-1 leading-relaxed">
            Kelola data siswa per rombongan belajar (kelas), akun akses login siswa, serta ekspor & impor data siswa melalui CSV.
        </p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">
        ✓
    </div>
    <p class="font-semibold text-sm">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-sm">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold mt-0.5">
        ✕
    </div>
    <div class="flex-1">
        <strong class="block text-sm font-bold">Terjadi Kesalahan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div x-data="{ 
    editModalOpen: false, 
    editData: { id: '', nis: '', name: '', kelas_id: '' },
    createModalOpen: false,
    importModalOpen: false
}">

    {{-- ============================================================
         FILTER & ACTION CONTROLS
         ============================================================ --}}
    <div class="space-y-4 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Filter Per Kelas -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Pilih Kelas
                </h3>
                <form action="{{ url('/app/data-siswa') }}" method="GET" id="formFilterKelas">
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer font-semibold text-slate-700 min-h-[42px]" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas untuk Melihat Data --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }} ({{ $k->siswas_count }} Siswa)</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Filter Pencarian Siswa -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Pencarian Siswa
                </h3>
                <form action="{{ url('/app/data-siswa') }}" method="GET" class="flex gap-2">
                    @if($kelas_id) <input type="hidden" name="kelas_id" value="{{ $kelas_id }}"> @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS / Nama siswa..." class="input-compact bg-slate-50 flex-1 min-h-[42px]">
                    <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm px-4 min-h-[42px] font-bold">Cari</button>
                    @if(request('search') || request('kelas_id'))
                        <a href="{{ url('/app/data-siswa') }}" class="btn-compact bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 flex items-center justify-center min-h-[42px]" title="Reset Filter">✕</a>
                    @endif
                </form>
            </div>

            <!-- Tombol Tambah Siswa & CSV Tools -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Aksi Data
                </h3>
                <div class="flex items-center gap-2">
                    <button type="button" @click="createModalOpen = true" class="btn-compact flex-1 bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-sm text-xs min-h-[42px] font-bold rounded-xl active:scale-95 transition-all">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Siswa
                    </button>
                    <button type="button" @click="importModalOpen = true" class="btn-compact bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center shadow-sm text-xs px-3 min-h-[42px] font-bold rounded-xl transition-all" title="Import CSV">
                        📥 Import
                    </button>
                    @if($kelas_id)
                    <form action="{{ url('/app/export-siswa') }}" method="GET" class="inline-block">
                        <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                        <button type="submit" class="btn-compact bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm text-xs px-3 min-h-[42px] font-bold rounded-xl transition-all" title="Export CSV Kelas Ini">
                            📤 Export
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         KONDISI 1: JIKA KELAS DIPILIH ATAU PENCARIAN AKTIF
         (MENAMPILKAN SELURUH SISWA 1 KELAS DALAM 1 PAGE)
         ============================================================ --}}
    @if($kelas_id || $search)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center text-sm shadow-sm">
                    {{ $selectedKelas ? $selectedKelas->nama_kelas : '🔍' }}
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        @if($selectedKelas)
                            Daftar Siswa Kelas {{ $selectedKelas->nama_kelas }}
                        @else
                            Hasil Pencarian Siswa: "{{ $search }}"
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan seluruh <strong class="text-blue-700 font-bold">{{ $siswas->count() }} siswa</strong> dalam 1 halaman penuh.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full font-extrabold text-xs border border-emerald-200">
                    Total: {{ $siswas->count() }} Siswa
                </span>
            </div>
        </div>

        @if($siswas->isEmpty())
        <div class="p-12 text-center text-slate-400">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <p class="text-base font-bold text-slate-600">Tidak ada data siswa ditemukan</p>
            <p class="text-xs text-slate-400 mt-1">Silakan tambahkan siswa atau impor dari CSV untuk kelas ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-32">Nomer Induk (NIS)</th>
                        <th class="py-3 px-4">Nama Lengkap Siswa</th>
                        <th class="py-3 px-4 w-28">Kelas</th>
                        <th class="py-3 px-4 w-36">Username Login</th>
                        <th class="py-3 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($siswas as $index => $siswa)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 text-center text-slate-500 font-bold">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $siswa->nis }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $siswa->user->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-md font-extrabold text-[11px] border border-blue-200">
                                {{ $siswa->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                            {{ $siswa->user->username ?? $siswa->nis }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Reset Password --}}
                                <form action="{{ url('/app/data-siswa/'.$siswa->id.'/reset-password') }}" method="POST" onsubmit="return confirm('Reset username dan password siswa {{ addslashes($siswa->user->name ?? $siswa->nis) }} ke default NIS ({{ $siswa->nis }})?');">
                                    @csrf
                                    <button type="submit" class="btn-compact p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors" title="Reset Sandi Siswa ke NIS">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </form>
                                {{-- Edit Siswa --}}
                                <button type="button" 
                                    @click="editData = { id: '{{ $siswa->id }}', nis: '{{ $siswa->nis }}', name: {{ json_encode($siswa->user->name ?? '') }}, kelas_id: '{{ $siswa->kelas_id }}' }; editModalOpen = true" 
                                    class="btn-compact p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="Ubah Data Siswa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                {{-- Hapus Siswa --}}
                                <form action="{{ url('/app/data-siswa', $siswa->id) }}" method="POST" onsubmit="return confirm('Hapus siswa {{ addslashes($siswa->user->name ?? $siswa->nis) }} secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-compact p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
            <span>Menampilkan total <strong>{{ $siswas->count() }}</strong> data siswa.</span>
            <span class="font-bold text-blue-700">✓ Seluruh siswa termuat dalam 1 halaman</span>
        </div>
        @endif
    </div>

    {{-- ============================================================
         KONDISI 2: JIKA BELUM MEMILIH KELAS (CARD GRID PEMILIH KELAS)
         ============================================================ --}}
    @else
    <div class="space-y-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm text-center">
            <div class="w-14 h-14 bg-blue-100 text-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xs text-2xl font-bold">
                🏫
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">
                Silakan Pilih Kelas Terlebih Dahulu
            </h3>
            <p class="text-xs text-slate-500 mb-6 max-w-md mx-auto">Pilih salah satu kelas di bawah untuk melihat daftar 32 siswa lengkap dalam 1 halaman penuh.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 max-w-4xl mx-auto">
                @foreach($kelas as $k)
                <a href="{{ url('/app/data-siswa') }}?kelas_id={{ $k->id }}" 
                   class="p-4 bg-slate-50 hover:bg-blue-600 hover:text-white rounded-2xl border border-slate-200 hover:border-blue-600 shadow-xs hover:shadow-md transition-all flex flex-col items-center justify-center gap-1 group active:scale-95">
                    <span class="text-xs font-bold text-slate-400 group-hover:text-blue-200 uppercase tracking-wider">Kelas</span>
                    <span class="text-lg font-black text-slate-800 group-hover:text-white">{{ $k->nama_kelas }}</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-white group-hover:bg-blue-500 text-blue-700 group-hover:text-white font-extrabold border border-slate-200 group-hover:border-blue-400 mt-1">
                        {{ $k->siswas_count }} Siswa
                    </span>
                    <span class="text-[10px] text-blue-600 group-hover:text-blue-100 font-bold mt-1.5">Buka Data Siswa →</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         MODAL TAMBAH SISWA BARU
         ============================================================ --}}
    <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md" @click.outside="createModalOpen = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">➕</span>
                    <h3 class="font-bold text-slate-800 text-base">Tambah Siswa Baru</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">✕</button>
            </div>
            
            <form action="{{ url('/app/data-siswa') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nomer Induk (NIS) *</label>
                    <input type="text" name="nis" required placeholder="Contoh: 004123" class="input-compact w-full bg-slate-50 min-h-[40px]">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nama Lengkap Siswa *</label>
                    <input type="text" name="name" required placeholder="Nama lengkap siswa" class="input-compact w-full bg-slate-50 min-h-[40px]">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Pilih Kelas *</label>
                    <select name="kelas_id" required class="input-compact w-full bg-slate-50 cursor-pointer min-h-[40px]">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-blue-800 text-[11px]">
                    💡 <strong>Info:</strong> Akun login siswa otomatis dibuat dengan username & password default menggunakan <strong>NIS</strong>.
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createModalOpen = false" class="btn-compact px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-xs">Batal</button>
                    <button type="submit" class="btn-compact px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL EDIT SISWA
         ============================================================ --}}
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md" @click.outside="editModalOpen = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✏️</span>
                    <h3 class="font-bold text-slate-800 text-base">Ubah Data Siswa</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">✕</button>
            </div>
            <form :action="`{{ url('/app/data-siswa') }}/${editData.id}`" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nomer Induk (NIS)</label>
                    <input type="text" name="nis" x-model="editData.nis" required class="input-compact w-full bg-slate-50 min-h-[40px]">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editData.name" required class="input-compact w-full bg-slate-50 min-h-[40px]">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Kelas</label>
                    <select name="kelas_id" x-model="editData.kelas_id" required class="input-compact w-full bg-slate-50 cursor-pointer min-h-[40px]">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModalOpen = false" class="btn-compact px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-xs">Batal</button>
                    <button type="submit" class="btn-compact px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL IMPORT CSV SISWA
         ============================================================ --}}
    <div x-show="importModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md" @click.outside="importModalOpen = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📥</span>
                    <h3 class="font-bold text-slate-800 text-base">Import Data Siswa (CSV)</h3>
                </div>
                <button type="button" @click="importModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">✕</button>
            </div>
            <form action="{{ url('/app/import-siswa') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Target Kelas *</label>
                    <select name="kelas_id" required class="input-compact w-full bg-slate-50 cursor-pointer min-h-[40px]">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">File CSV *</label>
                    <input type="file" name="file" accept=".csv, .txt" required class="w-full text-xs text-slate-500 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 cursor-pointer border border-slate-200 rounded-xl p-1.5">
                    <p class="text-[10px] text-slate-400 mt-1">Format kolom: <code>Nomer Induk;Nama Lengkap</code> (pemisah titik koma <code>;</code>)</p>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="importModalOpen = false" class="btn-compact px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold text-xs">Batal</button>
                    <button type="submit" class="btn-compact px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
