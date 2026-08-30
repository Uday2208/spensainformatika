@extends('layouts.app')
@section('title', 'Kelola Komentar')
@section('page_title', 'Kelola Komentar / Testimoni Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">
        ✓
    </div>
    <span class="font-semibold text-sm">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs mt-0.5">
        ✕
    </div>
    <div>
        <strong class="font-bold block mb-1 text-sm">Terjadi Kesalahan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Banner Card --}}
<div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-800 rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden text-white">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-24 translate-x-24"></div>
    </div>
    <div class="w-12 h-12 bg-blue-500/80 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg text-white font-bold text-2xl">
        💬
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Moderasi & Kelola Komentar Siswa</h2>
        <p class="text-blue-200 text-xs sm:text-sm mt-1 leading-relaxed">
            Pantau dan moderasi pesan kesan maupun testimoni yang dikirimkan oleh siswa untuk ditampilkan pada halaman utama (homepage) website.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Setting Limit -->
    <div class="lg:col-span-1 bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b border-slate-100 pb-2 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Pengaturan Tampilan Slider
        </h3>
        <form action="{{ route('admin.setting-komentar') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Maks. Komentar di Homepage</label>
                <input type="number" name="komentar_homepage_limit" value="{{ $limit }}" min="1" max="500" class="input-compact w-full bg-slate-50 min-h-[40px]" required>
                <p class="text-[11px] text-slate-400 mt-1">Berapa banyak komentar terbaru yang ditampilkan pada slider halaman depan.</p>
            </div>
            <div class="pt-1">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm w-full font-bold text-xs min-h-[40px] rounded-xl transition-all">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <!-- Informasi -->
    <div class="lg:col-span-2 bg-slate-50 p-6 border border-slate-200 rounded-2xl shadow-sm flex flex-col justify-center">
        <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
            <span>📌</span>
            <span>Panduan & Aturan Komentar Siswa</span>
        </h4>
        <ul class="text-xs text-slate-600 space-y-2.5 list-disc ml-5 leading-relaxed">
            <li>Siswa hanya dapat mengirimkan satu komentar/testimoni setiap 7 hari sekali untuk mencegah spam.</li>
            <li>Semua komentar yang dikirimkan siswa akan otomatis terbit di halaman depan, kecuali jika Admin menghapusnya di sini.</li>
            <li>Jika terdapat kata-kata yang tidak pantas, klik tombol ikon tempat sampah merah pada baris komentar untuk menghapusnya secara permanen.</li>
        </ul>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">Daftar Komentar Siswa</h3>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh pesan kesan yang masuk dari siswa.</p>
        </div>
        <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full font-bold">{{ $komentars->total() }} Total</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] border-b border-slate-200 tracking-wider">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4 w-52">Siswa</th>
                    <th class="py-3 px-4">Isi Komentar / Pesan</th>
                    <th class="py-3 px-4 w-36">Tanggal</th>
                    <th class="py-3 px-4 w-24 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($komentars as $index => $komentar)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-4 text-center text-slate-500 font-bold">{{ $komentars->firstItem() + $index }}</td>
                    <td class="py-3 px-4">
                        <div class="font-bold text-slate-800">{{ $komentar->siswa->user->name ?? 'User Terhapus' }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                            Kelas: <span class="font-bold text-blue-700">{{ $komentar->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-slate-700 italic">
                        "{{ $komentar->isi_komentar }}"
                    </td>
                    <td class="py-3 px-4 text-slate-500 text-xs">
                        {{ \Carbon\Carbon::parse($komentar->created_at)->translatedFormat('d M Y, H:i') }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        <form action="{{ url('/app/admin/komentar/' . $komentar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini secara permanen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 bg-red-50 hover:bg-red-100 rounded-lg transition-colors inline-flex items-center justify-center" title="Hapus Komentar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-12 text-center text-slate-400 italic">Belum ada komentar dari siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50">
        {{ $komentars->links() }}
    </div>
</div>

@endsection
