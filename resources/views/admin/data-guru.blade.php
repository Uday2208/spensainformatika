@extends('layouts.app')
@section('title', 'Kelola Data Guru')
@section('page_title', 'Kelola Data Guru & Pengampu')
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
            <p>{{ $errors->first() }}</p>
        </div>
    </div>
@endif

<div x-data="{ 
    addModal: false, 
    editModal: false, 
    editGuru: { id: '', name: '', username: '' },
    assignModal: false,
    assignGuru: { id: '', name: '', kelas_ids: [] }
}">

    {{-- Banner Info Card --}}
    <div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-800 rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-24 translate-x-24"></div>
        </div>
        <div class="w-12 h-12 bg-blue-500/80 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg text-white font-bold text-2xl">
            👨‍🏫
        </div>
        <div class="relative flex-1 min-w-0">
            <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Manajemen Akun Guru & Penugasan Kelas</h2>
            <p class="text-blue-200 text-xs sm:text-sm mt-1 leading-relaxed">
                Kelola akun pengajar dan atur penugasan rombel (kelas yang diampu). Guru hanya dapat mengabsen, menilai, dan membuat ujian pada kelas yang ditugaskan.
            </p>
        </div>
    </div>

    {{-- Filter & Action Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
            {{-- Search Bar --}}
            <form action="{{ route('admin.guru.index') }}" method="GET" class="flex-1 flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? request('search') }}" 
                           placeholder="Cari nama guru atau username..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.guru.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center justify-center" title="Reset Pencarian">
                        ✕
                    </a>
                @endif
            </form>

            {{-- Tombol Tambah Guru --}}
            <div class="flex-shrink-0">
                <button type="button" 
                        @click="addModal = true" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Guru</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Data Guru Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 sm:p-5 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center text-sm shadow-sm">
                    👨‍🏫
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        Daftar Guru Pengajar & Kelas yang Diampu
                        @if($search)
                            <span class="text-xs font-normal text-slate-500">(Hasil pencarian: "{{ $search }}")</span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Klik tombol <strong>Atur Kelas</strong> untuk menentukan rombel yang boleh diakses guru tersebut.
                    </p>
                </div>
            </div>
            <div>
                <span class="px-3 py-1 bg-blue-50 text-blue-800 rounded-full font-extrabold text-xs border border-blue-200">
                    Total: {{ $gurus->total() }} Guru
                </span>
            </div>
        </div>

        @if($gurus->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-700">Tidak ada data guru</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search)
                        Tidak ditemukan guru dengan kata kunci "{{ $search }}". Silakan coba kata kunci lain.
                    @else
                        Belum ada data guru terdaftar. Klik tombol <strong>Tambah Guru</strong> untuk menambahkan data baru.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200 tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Guru</th>
                            <th class="py-3.5 px-4 w-40">Username</th>
                            <th class="py-3.5 px-4">Kelas yang Diampu</th>
                            <th class="py-3.5 px-4 w-36">Tgl Dibuat</th>
                            <th class="py-3.5 px-4 text-center w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($gurus as $index => $guru)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-500 font-bold">
                                    {{ $gurus->firstItem() ? $gurus->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($guru->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 block">{{ $guru->name }}</span>
                                            <span class="inline-block px-2 py-0.5 mt-0.5 text-[10px] font-semibold text-blue-700 bg-blue-50 rounded-full border border-blue-100">
                                                Guru Pengajar
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg border border-slate-200">
                                        {{ $guru->username }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($guru->kelasMengajar && $guru->kelasMengajar->count() > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($guru->kelasMengajar as $km)
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 text-[11px] font-extrabold rounded-md border border-emerald-200">
                                                    {{ $km->nama_kelas }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-amber-600 italic bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            Belum ada kelas
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-xs">
                                    {{ $guru->created_at ? $guru->created_at->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Tombol Atur Kelas Pengampu --}}
                                        <button type="button" 
                                                @click="assignGuru = { id: '{{ $guru->id }}', name: {{ json_encode($guru->name) }}, kelas_ids: {{ json_encode($guru->kelasMengajar->pluck('id')->toArray()) }} }; assignModal = true" 
                                                class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg border border-indigo-200 text-xs transition-colors flex items-center gap-1" 
                                                title="Atur Kelas yang Diampu">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            <span>Atur Kelas</span>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button type="button" 
                                                @click="editGuru = { id: '{{ $guru->id }}', name: {{ json_encode($guru->name) }}, username: {{ json_encode($guru->username) }} }; editModal = true" 
                                                class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                                title="Edit Data Guru">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>

                                        {{-- Reset Password Form --}}
                                        <form action="{{ url('/app/admin/data-guru/' . $guru->id . '/reset-password') }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin mereset password guru {{ addslashes($guru->name) }} ke default (guru123)?');" 
                                              class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors" 
                                                    title="Reset Password Guru (Default: guru123)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Hapus Form --}}
                                        <form action="{{ url('/app/admin/data-guru/' . $guru->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun guru {{ addslashes($guru->name) }} secara permanen?');" 
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" 
                                                    title="Hapus Akun Guru">
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
                    Menampilkan <strong class="font-bold text-slate-700">{{ $gurus->firstItem() ?? 0 }}</strong> sampai <strong class="font-bold text-slate-700">{{ $gurus->lastItem() ?? 0 }}</strong> dari total <strong class="font-bold text-slate-700">{{ $gurus->total() }}</strong> data guru
                </div>
                <div>
                    {{ $gurus->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================================
         MODAL ATUR KELAS PENGAMPU (TEACHER-CLASS ASSIGNMENT)
         ============================================================ --}}
    <div x-cloak 
         x-show="assignModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="assignModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-lg border border-slate-100" 
             @click.outside="assignModal = false">
            
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                        🏫
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Atur Kelas yang Diampu</h3>
                        <p class="text-xs text-slate-500">Guru: <strong class="text-indigo-700" x-text="assignGuru.name"></strong></p>
                    </div>
                </div>
                <button type="button" @click="assignModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form :action="`{{ url('/app/admin/guru') }}/${assignGuru.id}/pengampu`" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                        Pilih Rombel / Kelas yang Diampu:
                    </label>
                    <p class="text-[11px] text-slate-400 mb-3">Centang satu atau beberapa kelas yang menjadi tanggung jawab mengajar guru ini.</p>
                    
                    @if($allKelas->isEmpty())
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-xs text-slate-500">
                            Belum ada data kelas terdaftar. Tambahkan kelas terlebih dahulu di menu <strong>Data Kelas</strong>.
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-2xl">
                            @foreach($allKelas as $k)
                                <label class="flex items-center gap-2.5 p-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/40 transition-all text-xs font-bold text-slate-700">
                                    <input type="checkbox" 
                                           name="kelas_ids[]" 
                                           value="{{ $k->id }}" 
                                           :checked="assignGuru.kelas_ids.includes({{ $k->id }})"
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                    <span>{{ $k->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-blue-800 text-xs leading-relaxed">
                    💡 <strong>Catatan:</strong> Guru hanya dapat melihat presensi, menginput nilai, dan membuat ujian CBT pada kelas-kelas yang telah dicentang di atas.
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" 
                            @click="assignModal = false" 
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                        Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL TAMBAH GURU
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
                    <h3 class="font-bold text-slate-800 text-base">Tambah Akun Guru Baru</h3>
                </div>
                <button type="button" @click="addModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form action="{{ route('admin.guru.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Lengkap Guru <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="Contoh: Ahmad Fauzi, S.Kom." 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="username" 
                           required 
                           placeholder="Contoh: ahmad_fauzi" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Digunakan guru untuk masuk (login) ke dalam sistem.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Kata Sandi / Password <span class="text-red-500">*</span></label>
                    <input type="password" 
                           name="password" 
                           required 
                           placeholder="Minimal 6 karakter" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-blue-800 text-xs">
                    💡 <strong>Info:</strong> Akun yang dibuat akan memiliki hak akses sebagai <strong>Guru</strong>. Penugasan kelas dapat diatur setelah akun dibuat.
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" 
                            @click="addModal = false" 
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition-all">
                        Simpan Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         MODAL EDIT GURU
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
                    <h3 class="font-bold text-slate-800 text-base">Ubah Data Guru</h3>
                </div>
                <button type="button" @click="editModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form :action="`{{ url('/app/admin/data-guru') }}/${editGuru.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Lengkap Guru <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           x-model="editGuru.name" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="username" 
                           x-model="editGuru.username" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono">
                </div>

                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-amber-800 text-xs">
                    ℹ️ <strong>Catatan:</strong> Untuk mengubah kata sandi guru, gunakan tombol <strong>Reset Password</strong> pada tabel.
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
