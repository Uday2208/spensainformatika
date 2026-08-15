@extends('layouts.app')
@section('title', 'Data Siswa')
@section('page_title', 'Manajemen Data Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <strong class="block mb-1">Terjadi Kesalahan:</strong>
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4" x-data="{ editModalOpen: false, editData: { id: '', nis: '', name: '', kelas_id: '' } }">
    <!-- Edit Modal -->
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded p-6 shadow-xl w-full max-w-md mx-4" @click.away="editModalOpen = false">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Ubah Data Siswa</h3>
            <form :action="`{{ url('/app/data-siswa') }}/${editData.id}`" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomer Induk</label>
                    <input type="text" name="nis" x-model="editData.nis" required class="input-compact w-full bg-slate-50">
                </div>
                
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editData.name" required class="input-compact w-full bg-slate-50">
                </div>
                
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                    <select name="kelas_id" x-model="editData.kelas_id" required class="input-compact w-full bg-slate-50 cursor-pointer">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700">Batal</button>
                    <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Tambah Siswa -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4 sticky top-4">
            <h3 class="font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100 text-sm">Tambah Siswa Baru</h3>
            
            <form action="{{ url('/app/data-siswa') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomer Induk</label>
                    <input type="text" name="nis" required placeholder="Contoh: 004123" class="input-compact w-full bg-slate-50">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama lengkap siswa" class="input-compact w-full bg-slate-50">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                    <select name="kelas_id" required class="input-compact w-full bg-slate-50 cursor-pointer">
                        <option value="">-- Pilih --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="btn-compact w-full bg-blue-600 hover:bg-blue-700 text-white border border-blue-700 shadow-sm">Simpan</button>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 leading-tight">Sandi default: Nomer Induk</p>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Siswa -->
    <div class="lg:col-span-3 space-y-4">
        
        <!-- Data Tabel -->
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 gap-3 border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-700 text-sm">Daftar Siswa ({{ $siswas->count() }})</h3>
                <form method="GET" action="{{ url('/app/data-siswa') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS/nama..." class="input-compact w-full sm:w-48 bg-slate-50">
                    <select name="kelas_id" class="input-compact w-full sm:w-32 bg-slate-50 cursor-pointer" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm px-3">Cari</button>
                    @if(request('search') || request('kelas_id'))
                        <a href="{{ url('/app/data-siswa') }}" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 flex items-center justify-center" title="Reset Filter">✕</a>
                    @endif
                </form>
            </div>

            <!-- Import / Export CSV (Toolbar Ringkas) -->
            <div class="mb-4 flex flex-col md:flex-row gap-2 items-center justify-between bg-slate-50 p-2 rounded border border-slate-200 text-xs">
                <span class="font-bold text-slate-600">CSV Tools:</span>
                <div class="flex flex-wrap gap-2 items-center justify-end w-full md:w-auto">
                    <form action="{{ url('/app/import-siswa') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-1.5">
                        @csrf
                        <select name="kelas_id" required class="input-compact text-[11px] h-7 w-20 py-0 px-1.5">
                            <option value="">Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                        <input type="file" name="file" accept=".csv" required class="text-[10px] w-40 text-slate-500 file:py-0.5 file:px-1.5 file:rounded file:border-0 file:text-[10px] file:bg-blue-50 file:text-blue-700 cursor-pointer">
                        <button type="submit" class="btn-compact text-[11px] h-7 bg-slate-200 hover:bg-slate-300 text-slate-700 px-2.5">Import</button>
                    </form>
                    <span class="text-slate-300 hidden md:inline">|</span>
                    <form action="{{ url('/app/export-siswa') }}" method="GET" class="flex items-center space-x-1.5">
                        <select name="kelas_id" required class="input-compact text-[11px] h-7 w-20 py-0 px-1.5">
                            <option value="">Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-compact text-[11px] h-7 bg-emerald-600 hover:bg-emerald-700 text-white px-2.5">Export</button>
                    </form>
                </div>
            </div>

            @if($siswas->isEmpty())
            <div class="border border-dashed border-slate-300 p-8 rounded text-center text-slate-500 text-sm">
                Belum ada siswa terdaftar.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-compact">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th>Nomer Induk</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th class="w-24 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $index => $siswa)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="text-center text-slate-500">{{ $siswas->firstItem() + $index }}</td>
                            <td class="font-mono text-slate-600">{{ $siswa->nis }}</td>
                            <td class="font-medium text-slate-800">{{ $siswa->user->name ?? '-' }}</td>
                            <td class="text-slate-600">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <form action="{{ url('/app/data-siswa/'.$siswa->id.'/reset-password') }}" method="POST" onsubmit="return confirm('Reset username dan password siswa ini ke NIS ({{ $siswa->nis }})?');">
                                        @csrf
                                        <button type="submit" class="text-orange-500 hover:text-orange-700 hover:bg-orange-50 p-1 rounded" title="Reset Sandi ke NIS">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                    <button type="button" 
                                        @click="editData = { id: '{{ $siswa->id }}', nis: '{{ $siswa->nis }}', name: {{ json_encode($siswa->user->name ?? '') }}, kelas_id: '{{ $siswa->kelas_id }}' }; editModalOpen = true" 
                                        class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 p-1 rounded" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ url('/app/data-siswa', $siswa->id) }}" method="POST" onsubmit="return confirm('Hapus data siswa ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded" title="Hapus">
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
            <div class="mt-4">
                {{ $siswas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
