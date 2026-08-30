@extends('layouts.app')
@section('title', 'Data Kelas')
@section('page_title', 'Manajemen Data Kelas')
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

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
    <!-- Form Tambah Kelas -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4 sticky top-4">
            <h3 class="font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100 text-sm">Tambah Kelas Baru</h3>
            
            <form action="{{ url('/app/admin/data-kelas') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kelas</label>
                    <input type="text" name="nama_kelas" required placeholder="Contoh: VII A" class="input-compact w-full bg-slate-50">
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="btn-compact w-full bg-blue-600 hover:bg-blue-700 text-white border border-blue-700 shadow-sm">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Kelas -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-700 text-sm">Daftar Kelas ({{ $kelas->count() }})</h3>
            </div>

            @if($kelas->isEmpty())
            <div class="border border-dashed border-slate-300 p-8 rounded text-center text-slate-500 text-sm">
                Belum ada kelas terdaftar.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-compact">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">ID</th>
                            <th>Nama Kelas</th>
                            <th class="w-24 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelas as $k)
                        <tr class="hover:bg-slate-50">
                            <td class="text-center text-slate-500 font-mono">{{ $k->id }}</td>
                            <td class="font-bold text-slate-800">{{ $k->nama_kelas }}</td>
                            <td class="text-center">
                                <form action="{{ url('/app/admin/data-kelas', $k->id) }}" method="POST" onsubmit="return confirm('Menghapus kelas akan menghapus seluruh data siswa di dalamnya beserta nilai dan absensi mereka! Yakin?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded" title="Hapus Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
