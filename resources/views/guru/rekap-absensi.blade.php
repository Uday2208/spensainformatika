@extends('layouts.app')
@section('title', 'Rekap Kehadiran')
@section('page_title', 'Rekap Kehadiran Siswa')
@section('content')

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div>
<!-- Filter & Export Controls -->
<form action="{{ url('/app/rekap-absensi') }}" method="GET" class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <!-- Filter Per Kelas -->
    <div class="bg-white rounded border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
        <h3 class="font-bold text-slate-700 text-sm mb-3 border-b border-slate-100 pb-2">Filter Berdasarkan Kelas</h3>
        <div class="flex flex-col sm:flex-row items-end gap-3 w-full">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Kelas</label>
                <select name="kelas_id" class="input-compact bg-slate-50 w-full cursor-pointer" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Per Siswa -->
    <div class="bg-white rounded border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
        <h3 class="font-bold text-slate-700 text-sm mb-3 border-b border-slate-100 pb-2">Filter Berdasarkan Nama Siswa</h3>
        <div class="flex flex-col sm:flex-row items-end gap-3 w-full">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Cari Nama</label>
                <input type="text" name="nama_siswa" value="{{ request('nama_siswa') }}" placeholder="Ketik nama siswa..." class="input-compact bg-slate-50 w-full focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm w-full sm:w-auto h-[30px] px-4">Cari</button>
            @if(request('nama_siswa') || request('kelas_id'))
                <a href="{{ url('/app/rekap-absensi') }}" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 shadow-sm h-[30px] px-3 flex items-center justify-center">✕</a>
            @endif
        </div>
    </div>
</form>

<div class="mb-4">
    <form action="{{ url('/app/rekap-absensi/export') }}" method="GET">
        @if($kelas_id) <input type="hidden" name="kelas_id" value="{{ $kelas_id }}"> @endif
        @if($nama_siswa) <input type="hidden" name="nama_siswa" value="{{ $nama_siswa }}"> @endif
        <button type="submit" class="btn-compact bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export CSV Hasil Filter Ini
        </button>
    </form>
</div>

<div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden">
    @if($siswas->isEmpty())
        <div class="p-10 text-center text-slate-500 italic border border-dashed border-slate-200 m-4 rounded">
            Tidak ada data siswa yang ditemukan.
        </div>
    @else
        <div class="overflow-x-auto p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-700 text-sm">Data Rekapitulasi</h3>
                @if($kelas_id)
                    <a href="{{ url('/app/rekap-absensi') }}" class="text-xs text-blue-600 hover:underline">Tampilkan Semua Data</a>
                @endif
            </div>
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th>Nomer Induk</th>
                        <th>Nama Siswa</th>
                        <th class="text-center w-20">Hadir</th>
                        <th class="text-center w-20">Sakit</th>
                        <th class="text-center w-20">Izin</th>
                        <th class="text-center w-20">Dispen</th>
                        <th class="text-center w-20">Alpha</th>
                        <th class="text-center w-24 bg-emerald-50">% Hadir</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $index => $siswa)
                        @php
                            $totalHari = ($siswa->hadir_count ?? 0) + ($siswa->sakit_count ?? 0) + ($siswa->izin_count ?? 0) + ($siswa->dispen_count ?? 0) + ($siswa->alpha_count ?? 0);
                            $persen = $totalHari > 0 ? round((($siswa->hadir_count ?? 0) / $totalHari) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="text-center text-slate-500">{{ $siswas->firstItem() + $index }}</td>
                            <td class="font-mono text-slate-600">{{ $siswa->nis }}</td>
                            <td class="font-medium text-slate-800">{{ $siswa->user->name ?? '-' }}</td>
                            <td class="text-center font-bold text-green-600 bg-green-50/50">{{ $siswa->hadir_count ?? 0 }}</td>
                            <td class="text-center font-bold text-blue-600 bg-blue-50/50">{{ $siswa->sakit_count ?? 0 }}</td>
                            <td class="text-center font-bold text-yellow-600 bg-yellow-50/50">{{ $siswa->izin_count ?? 0 }}</td>
                            <td class="text-center font-bold text-purple-600 bg-purple-50/50">{{ $siswa->dispen_count ?? 0 }}</td>
                            <td class="text-center font-bold text-red-600 bg-red-50/50">{{ $siswa->alpha_count ?? 0 }}</td>
                            <td class="text-center font-black text-sm
                                {{ $persen >= 80 ? 'text-emerald-700 bg-emerald-50' : ($persen >= 60 ? 'text-amber-700 bg-amber-50' : 'text-red-700 bg-red-50') }}">
                                {{ $totalHari > 0 ? $persen . '%' : '-' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ url('/app/rekap-absensi/siswa/' . $siswa->id) }}" class="btn-compact bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white transition-colors text-xs inline-flex items-center justify-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $siswas->links() }}
        </div>
    @endif
</div>

</div>

@endsection
