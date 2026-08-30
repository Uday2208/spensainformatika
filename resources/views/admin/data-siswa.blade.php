@extends('layouts.app')
@section('title', 'Kelola Data Siswa')
@section('page_title', 'Kelola Data Siswa')
@section('content')

{{-- Flash Messages --}}
@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium flex items-center gap-3 shadow-xs">
        <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">
            ✓
        </div>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-start gap-3 shadow-xs">
        <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs mt-0.5">
            ✕
        </div>
        <div>
            <strong class="font-bold block mb-1">Terjadi Kesalahan:</strong>
            <ul class="list-disc ml-4 text-xs text-red-600 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div x-data="{ 
    addModal: false, 
    importModal: false, 
    editModal: false, 
    editSiswa: { id: '', name: '', nis: '', kelas_id: '' } 
}">

    {{-- Banner Info Card --}}
    <div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-800 rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-24 translate-x-24"></div>
        </div>
        <div class="w-12 h-12 bg-blue-500/80 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg text-white font-bold text-2xl">
            👥
        </div>
        <div class="relative flex-1 min-w-0">
            <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Manajemen Master Data Siswa</h2>
            <p class="text-blue-200 text-xs sm:text-sm mt-1 leading-relaxed">
                Kelola data siswa per rombongan belajar (kelas), akun akses login siswa, serta ekspor & impor data siswa melalui CSV.
            </p>
        </div>
    </div>

    {{-- Filter & Action Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            {{-- Filter & Search Form --}}
            <form action="{{ route('admin.siswa.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                {{-- Filter Kelas --}}
                <div class="w-full sm:w-52 flex-shrink-0">
                    <select name="kelas_id" 
                            onchange="this.form.submit()" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Bar --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? request('search') }}" 
                           placeholder="Cari NIS, nama siswa, atau username..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5 min-w-[70px]">
                        Filter
                    </button>
                    @if(request('search') || request('kelas_id'))
                        <a href="{{ route('admin.siswa.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center justify-center" title="Reset Filter">
                            ✕
                        </a>
                    @endif
                </div>
            </form>

            {{-- Action Buttons (Tambah Siswa & Import CSV) --}}
            <div class="flex items-center gap-2.5 flex-shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <button type="button" 
                        @click="importModal = true" 
                        class="flex-1 sm:flex-initial px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow-sm transition-all flex items-center justify-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Import CSV</span>
                </button>
                <button type="button" 
                        @click="addModal = true" 
                        class="flex-1 sm:flex-initial px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Siswa</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Data Siswa Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 sm:p-5 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center text-sm shadow-sm">
                    👥
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        Daftar Siswa
                        @if($kelas_id)
                            @php $selectedK = $kelas->firstWhere('id', $kelas_id); @endphp
                            @if($selectedK)
                                <span class="text-xs font-bold px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-lg">Kelas {{ $selectedK->nama_kelas }}</span>
                            @endif
                        @endif
                        @if($search)
                            <span class="text-xs font-normal text-slate-500">(Pencarian: "{{ $search }}")</span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan data siswa yang terdaftar dalam sistem akademik.
                    </p>
                </div>
            </div>
            <div>
                <span class="px-3 py-1 bg-blue-50 text-blue-800 rounded-full font-extrabold text-xs border border-blue-200">
                    Total: {{ $siswas->total() }} Siswa
                </span>
            </div>
        </div>

        @if($siswas->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-700">Tidak ada data siswa</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search || $kelas_id)
                        Tidak ditemukan data siswa dengan filter yang dipilih. Silakan sesuaikan pencarian atau filter kelas.
                    @else
                        Belum ada data siswa terdaftar. Klik tombol <strong>Tambah Siswa</strong> atau <strong>Import CSV</strong> untuk menambahkan data baru.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200 tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4 w-36">NIS</th>
                            <th class="py-3.5 px-4">Nama</th>
                            <th class="py-3.5 px-4 w-32">Kelas</th>
                            <th class="py-3.5 px-4 w-40">Username</th>
                            <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($siswas as $index => $siswa)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-500 font-bold">
                                    {{ $siswas->firstItem() ? $siswas->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-800 rounded-lg border border-slate-200">
                                        {{ $siswa->nis }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($siswa->user->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 block">{{ $siswa->user->name ?? '-' }}</span>
                                            <span class="text-[10px] text-slate-400">ID: #{{ $siswa->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                        {{ $siswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                    {{ $siswa->user->username ?? $siswa->nis }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Edit Siswa (Modal) --}}
                                        <button type="button" 
                                                @click="editSiswa = { id: '{{ $siswa->id }}', nis: '{{ $siswa->nis }}', name: {{ json_encode($siswa->user->name ?? '') }}, kelas_id: '{{ $siswa->kelas_id }}' }; editModal = true" 
                                                class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                                title="Ubah Data Siswa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>

                                        {{-- Reset Password Form --}}
                                        <form action="{{ url('/app/admin/data-siswa/' . $siswa->id . '/reset-password') }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin mereset password siswa {{ addslashes($siswa->user->name ?? $siswa->nis) }} ke default NIS ({{ $siswa->nis }})?');" 
                                              class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors" 
                                                    title="Reset Password Siswa ke NIS">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Hapus Form --}}
                                        <form action="{{ url('/app/admin/data-siswa/' . $siswa->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa {{ addslashes($siswa->user->name ?? $siswa->nis) }} secara permanen? Seluruh data nilai dan absensi terkait juga akan terhapus.');" 
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" 
                                                    title="Hapus Siswa">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-xs text-slate-500">
                    Menampilkan <strong class="font-bold text-slate-700">{{ $siswas->firstItem() ?? 0 }}</strong> sampai <strong class="font-bold text-slate-700">{{ $siswas->lastItem() ?? 0 }}</strong> dari total <strong class="font-bold text-slate-700">{{ $siswas->total() }}</strong> data siswa
                </div>
                <div>
                    {{ $siswas->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================================
         MODAL TAMBAH SISWA
         ============================================================ --}}
    <div x-cloak 
         x-show="addModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="addModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md border border-slate-100" 
             @click.outside="addModal = false">
            
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                        ➕
                    </div>
                    <h3 class="font-bold text-slate-800 text-base">Tambah Siswa Baru</h3>
                </div>
                <button type="button" @click="addModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form action="{{ route('admin.siswa.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nomor Induk Siswa (NIS) <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="nis" 
                           required 
                           placeholder="Contoh: 2024001" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="Contoh: Ahmad Rizky" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Pilih Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas_id" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-blue-800 text-xs leading-relaxed">
                    💡 <strong>Info:</strong> Password default = <strong>NIS</strong>. Akun login siswa otomatis dibuat dengan username & password default menggunakan <strong>NIS</strong>.
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" 
                            @click="addModal = false" 
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL IMPORT CSV SISWA
         ============================================================ --}}
    <div x-cloak 
         x-show="importModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="importModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md border border-slate-100" 
             @click.outside="importModal = false">
            
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        📥
                    </div>
                    <h3 class="font-bold text-slate-800 text-base">Import Data Siswa (CSV)</h3>
                </div>
                <button type="button" @click="importModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Target Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas_id" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">File CSV <span class="text-red-500">*</span></label>
                    <input type="file" 
                           name="file" 
                           accept=".csv,.txt" 
                           required 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1.5 bg-slate-50 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Format kolom: <code>NIS,Nama Lengkap</code> (baris pertama header akan otomatis dilewati).</p>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-xs">
                    <p class="font-bold text-slate-700 mb-1">Contoh Format Isi File CSV:</p>
                    <div class="bg-white p-2 rounded-lg border border-slate-200 font-mono text-[11px] text-slate-600 space-y-0.5">
                        <div>NIS,Nama Lengkap</div>
                        <div>2024001,Ahmad Pratama</div>
                        <div>2024002,Bella Safitri</div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">💡 Password default untuk seluruh akun siswa hasil import adalah <strong>NIS</strong> masing-masing.</p>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" 
                            @click="importModal = false" 
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                        Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL EDIT SISWA
         ============================================================ --}}
    <div x-cloak 
         x-show="editModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="editModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md border border-slate-100" 
             @click.outside="editModal = false">
            
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                        ✏️
                    </div>
                    <h3 class="font-bold text-slate-800 text-base">Ubah Data Siswa</h3>
                </div>
                <button type="button" @click="editModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form :action="`{{ url('/app/admin/data-siswa') }}/${editSiswa.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nomor Induk Siswa (NIS) <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="nis" 
                           x-model="editSiswa.nis" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           x-model="editSiswa.name" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas_id" 
                            x-model="editSiswa.kelas_id" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-amber-800 text-xs">
                    ℹ️ <strong>Catatan:</strong> Mengubah NIS juga akan memperbarui username akun login siswa. Gunakan tombol <strong>Reset Password</strong> di tabel untuk mereset kata sandi ke NIS.
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" 
                            @click="editModal = false" 
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
