@extends('layouts.app')
@section('title', 'Kelola Data Kelas')
@section('page_title', 'Kelola Data Kelas')
@section('content')

<div x-data="{ addModal: false }">

    {{-- FLASH SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm">
        <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
            ✓
        </div>
        <p class="font-semibold text-sm">{{ session('success') }}</p>
    </div>
    @endif

    {{-- FLASH ERROR MESSAGE --}}
    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-sm">
        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm mt-0.5">
            ✕
        </div>
        <div class="flex-1">
            <strong class="block text-sm font-bold">Terjadi Kesalahan:</strong>
            <ul class="list-disc ml-5 text-xs text-red-700 mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- BANNER HEADER UTAMA --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-950 rounded-2xl p-5 mb-6 shadow-xl relative overflow-hidden text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-24 translate-x-24"></div>
            <div class="absolute bottom-0 left-0 w-36 h-36 bg-blue-400 rounded-full translate-y-16 -translate-x-16"></div>
        </div>
        <div class="relative flex items-center gap-3.5">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg text-white font-bold text-2xl">
                🏫
            </div>
            <div>
                <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Manajemen Master Data Kelas</h2>
                <p class="text-blue-200 text-xs mt-0.5">Kelola daftar rombongan belajar (rombel), tingkat kelas, dan pantau jumlah siswa.</p>
            </div>
        </div>
        <div class="relative flex items-center gap-3">
            <button type="button" @click="addModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-400 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-lg transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kelas
            </button>
        </div>
    </div>

    {{-- TABEL DATA KELAS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 sm:p-5 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                    📚
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Daftar Kelas ({{ $kelas->count() }})</h3>
                    <p class="text-xs text-slate-500">Seluruh rombel yang terdaftar dalam sistem akademik</p>
                </div>
            </div>
            <div>
                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-bold text-xs border border-blue-200">
                    Total: {{ $kelas->count() }} Rombel
                </span>
            </div>
        </div>

        @if($kelas->isEmpty())
        <div class="p-12 text-center text-slate-400">
            <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <p class="text-base font-bold text-slate-700">Belum ada kelas terdaftar</p>
            <p class="text-xs text-slate-400 mt-1 mb-4">Silakan tambahkan data kelas baru untuk memulai.</p>
            <button type="button" @click="addModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kelas
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4 w-14 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Kelas</th>
                        <th class="py-3.5 px-4 w-32">Tingkat</th>
                        <th class="py-3.5 px-4 w-40">Jumlah Siswa</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kelas as $index => $k)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-500 font-bold font-mono">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800 text-sm">{{ $k->nama_kelas }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-md font-extrabold text-[11px] border border-blue-200">
                                {{ $k->tingkat ? 'Tingkat ' . $k->tingkat : '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-xs border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                {{ $k->siswas_count }} Siswa
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center">
                                <form action="{{ url('/app/admin/data-kelas', $k->id) }}" method="POST" onsubmit="return confirm('Menghapus kelas {{ addslashes($k->nama_kelas) }} akan menghapus seluruh data siswa di dalamnya beserta absensi dan nilainya! Yakin ingin menghapus?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Kelas">
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
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
            <span>Menampilkan total <strong>{{ $kelas->count() }}</strong> rombongan belajar.</span>
        </div>
        @endif
    </div>

    {{-- MODAL TAMBAH KELAS --}}
    <div x-cloak x-show="addModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md border border-slate-100" 
             @click.outside="addModal = false"
             x-show="addModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                        ➕
                    </div>
                    <h3 class="font-bold text-slate-800 text-base">Tambah Kelas Baru</h3>
                </div>
                <button type="button" @click="addModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-700 flex items-center justify-center text-xs transition-colors">✕</button>
            </div>
            
            <form action="{{ route('admin.kelas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kelas" required placeholder="Contoh: X IPA 1, VII A" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">-- Pilih Tingkat Kelas --</option>
                        <option value="X">X (Sepuluh)</option>
                        <option value="XI">XI (Sebelas)</option>
                        <option value="XII">XII (Dua Belas)</option>
                    </select>
                </div>
                
                <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl text-blue-800 text-xs flex items-start gap-2.5">
                    <span class="text-base flex-shrink-0">💡</span>
                    <p class="leading-relaxed">Pastikan nama kelas belum terdaftar sebelumnya agar tidak terjadi duplikasi data rombel.</p>
                </div>

                <div class="pt-2 flex justify-end gap-2.5">
                    <button type="button" @click="addModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md shadow-blue-500/20 transition-all active:scale-95">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
