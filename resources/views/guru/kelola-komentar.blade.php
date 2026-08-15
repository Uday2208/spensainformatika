@extends('layouts.app')
@section('title', 'Kelola Komentar')
@section('page_title', 'Kelola Komentar / Testimoni')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Setting Limit -->
    <div class="lg:col-span-1 bg-white p-6 border border-slate-200 rounded shadow-sm">
        <h3 class="font-bold text-slate-700 text-sm mb-4 border-b border-slate-100 pb-2">Pengaturan Tampilan</h3>
        <form action="{{ url('/app/setting-komentar') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Komentar di Homepage</label>
                <input type="number" name="limit" value="{{ $limit }}" min="1" max="500" class="input-compact w-full bg-slate-50" required>
                <p class="text-[10px] text-slate-500 mt-1">Berapa banyak komentar terbaru yang akan ditampilkan pada animasi _slider_ di halaman utama.</p>
            </div>
            <div class="pt-2">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm w-full">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <!-- Informasi -->
    <div class="lg:col-span-2 bg-slate-50 p-6 border border-slate-200 rounded shadow-sm flex flex-col justify-center">
        <h4 class="font-bold text-slate-700 mb-2">Informasi Fitur Komentar</h4>
        <ul class="text-sm text-slate-600 space-y-2 list-disc ml-5">
            <li>Siswa hanya dapat mengirimkan satu komentar / testimoni setiap 7 hari sekali untuk menghindari spam.</li>
            <li>Semua komentar yang dikirim otomatis ditampilkan di halaman utama, kecuali Anda menghapusnya di sini.</li>
            <li>Anda dapat membatasi jumlah komentar yang berputar di halaman utama menggunakan pengaturan di samping.</li>
        </ul>
    </div>
</div>

<div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h3 class="font-bold text-slate-700">Daftar Komentar Siswa</h3>
        <span class="text-xs bg-slate-200 text-slate-700 px-2 py-1 rounded-full font-bold">{{ $komentars->total() }} Total</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-white border-b border-slate-200">
                    <th class="p-3 font-semibold text-slate-600 w-12 text-center">No</th>
                    <th class="p-3 font-semibold text-slate-600">Siswa</th>
                    <th class="p-3 font-semibold text-slate-600">Isi Komentar</th>
                    <th class="p-3 font-semibold text-slate-600 w-32">Tanggal</th>
                    <th class="p-3 font-semibold text-slate-600 w-24 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($komentars as $index => $komentar)
                <tr class="hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                    <td class="p-3 text-center text-slate-500">{{ $komentars->firstItem() + $index }}</td>
                    <td class="p-3">
                        <div class="font-bold text-slate-800">{{ $komentar->siswa->user->name ?? 'User Terhapus' }}</div>
                        <div class="text-xs text-slate-500">Kelas: {{ $komentar->siswa->kelas->nama_kelas ?? '-' }}</div>
                    </td>
                    <td class="p-3 text-slate-700">
                        "{{ $komentar->isi_komentar }}"
                    </td>
                    <td class="p-3 text-slate-500 text-xs">
                        {{ \Carbon\Carbon::parse($komentar->created_at)->format('d M Y, H:i') }}
                    </td>
                    <td class="p-3 text-center">
                        <form action="{{ url('/app/komentar/' . $komentar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1 bg-red-50 hover:bg-red-100 rounded transition" title="Hapus Komentar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500 italic">Belum ada komentar dari siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-slate-100">
        {{ $komentars->links() }}
    </div>
</div>

@endsection
